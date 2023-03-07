<?php
namespace App\Http\Traits;

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
        $this->json($response);
        if ($response->code == 0){
            $data = $response->data;
            if ($data->size > 20000000){
                $txt = "{$this->words($lang, 'sizeLimit')}\n\n{$data->play}";
                $this->sendMessage($chat_id, $txt, ['reply_to_message_id' => $message_id]);
            }else {
                $res = $this->sendVideo($chat_id, $data->play, [
                    'caption' => "*{$this->words($lang, 'downloadedWith')}*",
                    'parse_mode' => 'markdown',
                    'reply_to_message_id' => $message_id,
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [["text" => "{$this->words($lang, 'shareFriends')}", "switch_inline_query" => "not ready"],],
                        ]
                    ]),
                ])->result;
                $video = $res->video->file_id;
                $this->bot('editMessageReplyMarkup', [
                    "chat_id" => $chat_id,
                    "message_id" => $res->message_id,
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [["text" => "{$this->words($lang, 'shareFriends')}", "switch_inline_query" => $video],],
                        ]
                    ]),
                ]);
                if ($user->music) {
                    if (isset($data->music)){
                        $this->sendAudio($chat_id, $data->music, [
                            'caption' => "*{$this->words($lang, 'downloadedWith')}*",
                            'parse_mode' => 'markdown',
                            'reply_to_message_id' => $res->message_id,

                        ]);
                    }
                }
                $text = "{$chat->type}\n<b>Link: {$text}\n<a href='tg://user?id={$user_id}'>Go to user profile</a> \nName: {$name} \nUsername: @{$username} \nID: {$user_id}</b>";
                $ms = $this->sendVideo($this->videosChannel, $video, [
                    'caption' => $text,
                    'parse_mode' => 'html',
                ]);
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
