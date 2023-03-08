<?php
namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

trait DownloadTrait{
    use BotTrait;

    public function getTiktok($url)
    {
        return json_decode(Http::withHeaders([
            "X-RapidAPI-Key" => config('api.tiktok_key'),
            "X-RapidAPI-Host" => config('api.tiktok_host'),
        ])->get(config('api.tiktok_url'), ["url" => $url,]));
    }
    public function sendTiktok($update, $user)
    {
        $message = $update->message;
        $message_id = $message->message_id;
        $from = $message->from;
        $name = $from->first_name;
        $user_id = $from->id;
        $username = $from->username ?? null;
        $chat = $message->chat;
        $chat_id = $chat->id;
        $text = $message->text;
        $lang = $user->lang;

        $this->sendChatAction($chat_id, "upload_video");
        $response = $this->getTiktok($text);
        if ($response->code == 0){
            $data = $response->data;
            if ($data->size > 20000000){
                $txt = "{$this->words($lang, 'sizeLimit')}\n\n{$data->play}";
                $this->sendMessage($chat_id, $txt, ['reply_to_message_id' => $message_id]);
            }else {
                $shareText = $this->words($lang, 'shareFriends');
                $downloadText = "*".$this->words($lang, 'downloadedWith')."*";
                $text = "{$chat->type}\n<b>Link: {$text}\n<a href='tg://user?id={$user_id}'>Go to user profile</a> \nName: {$name} \nUsername: @{$username} \nID: {$user_id}</b>";

                $tiktok = $this->tiktoksModel::where('tiktok_id', $data->id)->first();

                if (!$tiktok){
                    $video_id = $data->play;
                    $audio_id = $data->music;
                    $downloadsCount = 1;
                    $updated_at = $created_at = Carbon::now()->format('Y-m-d H:i:s');
                }else{
                    $video_id = $tiktok->video ?? $data->play;
                    $audio_id = $tiktok->audio ?? $data->music;
                    $downloadsCount = $tiktok->downloads+1;
                    $created_at = $tiktok->created_at;
                    $updated_at = Carbon::now()->format('Y-m-d H:i:s');
                }
                $reply = $this->sendVideo($chat_id, $video_id, [
                    'caption' => $downloadText,
                    'parse_mode' => 'markdown',
                    'reply_to_message_id' => $message_id,
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [["text" => $shareText, "switch_inline_query" => "not ready"],],
                        ]
                    ]),
                ])->result;
                $video_id = $reply->video->file_id;
                $this->bot('editMessageReplyMarkup', [
                    "chat_id" => $chat_id,
                    "message_id" => $reply->message_id,
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [["text" => $shareText, "switch_inline_query" => $video_id],],
                        ]
                    ]),
                ]);
                if ($user->music) {
                    if ($audio_id){
                        $audio = $this->sendAudio($chat_id, $audio_id, [
                            'caption' => $downloadText,
                            'parse_mode' => 'markdown',
                            'reply_to_message_id' => $reply->message_id,
                        ])->result->audio->file_id;
                        $audio_id = $audio;
                    }
                }
                $ms = $this->sendVideo($this->videosChannel, $video_id, [
                    'caption' => $text,
                    'parse_mode' => 'html',
                ])->result->message_id;
                if ($audio){
                    $this->sendAudio($this->videosChannel, $audio_id, [
                        'caption' => "Audio file",
                        'parse_mode' => 'html',
                        'reply_to_message_id' => $ms,
                    ]);
                }
                $this->tiktoksModel::updateOrInsert(
                    ['tiktok_id' => $data->id],
                    [
                        'audio' => $audio_id,
                        'video' => $video_id,
                        'downloads' => $downloadsCount,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at
                    ]
                );

            }
        }else{
            $this->sendMessage($chat_id, $this->words($lang, 'checkLink'), ['reply_to_message_id' => $message_id]);
            if ($response->msg != "Url parsing is failed! Please check url."){
                $this->json($update);
                $this->json($response);
            }
        }

    }
}
