<?php

namespace App\Http\Controllers;

use App\Http\Traits\BotTrait;
use App\Http\Traits\DataTrait;
use App\Http\Traits\DownloadTrait;
use App\Http\Traits\WordsTrait;
use Carbon\Carbon;

class BotController extends Controller
{
    use BotTrait, WordsTrait, DataTrait, DownloadTrait;


    public $usersModel = "App\Models\BotUser";
    public $groupsModel = "App\Models\BotGroup";
    public $tiktoksModel = "App\Models\TiktokPost";

    public $usersChannel = "-1001852707093";
    public $videosChannel = "-1001501638867";

    public function index()
    {
        $update = json_decode(file_get_contents('php://input'));
        if (isset($update->callback_query)) {
            $callback = $update->callback_query;
            $callback_data = $callback->data;
            $callback_from = $callback->from;
            $callback_from_id = $callback_from->id;
            $callback_message = $callback->message;
            $callback_message_id = $callback_message->message_id;
            $callback_chat = $callback_message->chat;
            $callback_chat_id = $callback_chat->id;
            $callback_name = $callback_from->first_name;

            if ($callback_message){
                $lang = 'en';

                $ex = explode('_', $callback_data);
//                    $this->json($update);
                if (count($ex) == 2) {
//                    $this->json('coming');
                    if ($ex[0] == 'setLang') {
                        $lang = $ex[1];
                        if ($callback_chat->type == "private") {
                            $user = $this->getUser($update);
                            $this->updateUser($callback_from_id, ['lang' => $lang]);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            if ($user->step == "chooseLang"){
                                $this->sendMessage($callback_chat_id, $this->words($lang, 'welcome', $callback_name), ['reply_markup' => $this->mainBtn($user)]);
                            }else{
                                $user->{'lang'} = $lang;
                                $this->sendMessage($callback_chat_id, $this->words($lang, "languageSet"), ['reply_markup' => $this->mainBtn($user)]);
                            }
                            $this->updateUser($callback_from_id, ['step' => 'langSet']);
                        }
                        if ($callback_chat->type == "group" or $callback_chat->type == "supergroup"){
                            $group = $this->getGroup($update);
                            $this->updateGroup($callback_chat_id, ['lang' => $lang]);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            if ($group->step == "chooseLang"){
                                $this->sendMessage($callback_chat_id, $this->words($lang, 'groupWelcome'), ['parse_mode' => "markdown"]);
                            }else{
                                $this->sendMessage($callback_chat_id, $this->words($lang, "languageSet"));
                            }
                            $this->updateGroup($callback_chat_id, ['step' => "langSet"]);
                        }

                    }
                }
                if ($callback_chat->type == 'private'){
                    $user = $this->getUser($update);
                    $lang = $user->lang;
                    if ($callback_data == "statistics"){
                        $txt = $this->statistics($lang);
                        $btn = $this->inlineKeyboard([
                            ["{$this->words($lang, 'refresh')}-statistics"]
                        ]);
                        $this->bot('editMessageText', [
                            'chat_id' => $callback_chat_id,
                            'message_id' => $callback_message_id,
                            'text' => $txt,
                            'reply_markup' => $btn,
                            'parse_mode' => 'markdown'
                        ]);
                    }
                    if ($callback_data == "changeLang"){
                        $txt = $this->words($lang, 'chooseLang');
                        $btn = $this->inlineKeyboard([
                            [$this->words('en', 'english')."-setLang_en"],
                            [$this->words('en', 'russian')."-setLang_ru"],
                            [$this->words('en', 'uzbek')."-setLang_uz"],
                        ]);
                        $this->bot('editMessageText', [
                            'chat_id' => $callback_chat_id,
                            'message_id' => $callback_message_id,
                            'text' => $txt,
                            'reply_markup' => $btn
                        ]);
                    }

                    if (count($ex) == 2){
                        if ($ex[0] == "music"){
                            if ($ex[1] == "on"){
                                $music = true;
                                $txt = $this->words($lang, "musicOnText");
                                $musicBtn = ["{$this->words($lang, 'musicOn')}-music_off"];
                            } else{
                                $music = false;
                                $txt = $this->words($lang, "musicOffText");
                                $musicBtn = ["{$this->words($lang, 'musicOff')}-music_on"];
                            }
                            $btn = $this->inlineKeyboard([
                                ["{$this->words($lang, 'changeLang')}-changeLang"],
                                $musicBtn
                            ]);

                            $this->updateUser($callback_from_id, ['music' => $music]);
                            $this->answerCallbackQuery($callback->id, $txt, true);
                            $this->bot('editMessageReplyMarkup', [
                                'chat_id' => $callback_chat_id,
                                'message_id' => $callback_message_id,
                                'reply_markup' => $btn
                            ]);
                        }
                    }
                }
                if ($callback_chat->type == "group" or $callback_chat->type == "supergroup") {
                    $group = $this->getGroup($update);
                    $lang = $group->lang;

                    if ($callback_data == "changeLang"){
                        $txt = $this->words($lang, 'chooseLang');
                        $btn = $this->inlineKeyboard([
                            [$this->words('en', 'english')."-setLang_en"],
                            [$this->words('en', 'russian')."-setLang_ru"],
                            [$this->words('en', 'uzbek')."-setLang_uz"],
                        ]);
                        $this->bot('editMessageText', [
                            'chat_id' => $callback_chat_id,
                            'message_id' => $callback_message_id,
                            'text' => $txt,
                            'reply_markup' => $btn
                        ]);
                    }
                    if (count($ex) == 2){
                        if ($ex[0] == "music"){
                            if ($ex[1] == "on"){
                                $music = true;
                                $txt = $this->words($lang, "musicOnText");
                                $musicBtn = ["{$this->words($lang, 'musicOn')}-music_off"];
                            } else{
                                $music = false;
                                $txt = $this->words($lang, "musicOffText");
                                $musicBtn = ["{$this->words($lang, 'musicOff')}-music_on"];
                            }
                            $btn = $this->inlineKeyboard([
                                ["{$this->words($lang, 'changeLang')}-changeLang"],
                                $musicBtn
                            ]);

                            $this->updateGroup($callback_chat_id, ['music' => $music]);
                            $this->answerCallbackQuery($callback->id, $txt, true);
                            $this->bot('editMessageReplyMarkup', [
                                'chat_id' => $callback_chat_id,
                                'message_id' => $callback_message_id,
                                'reply_markup' => $btn
                            ]);
                        }
                    }
                }
            }
        }
        if (isset($update->my_chat_member)){
            $my_chat_member = $update->my_chat_member;
            $new = $my_chat_member->new_chat_member;
            $chat = $my_chat_member->chat;
            $chat_type = $chat->type;
            if ($chat_type == "private"){
                if ($new->status != "member"){
                    $this->updateUser($my_chat_member->from->id, ['deleted_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                }
            }
            if ($chat_type == "group" or $chat_type == "supergroup"){
                if ($new->status != "member"){
                    $this->updateGroup($my_chat_member->chat->id, ['deleted_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                }
            }
        }
        if (isset($update->message)) {
            $message = $update->message;
            $message_id = $message->message_id;
            $chat = $message->chat;
            $chat_id = $chat->id;
            $chat_type = $chat->type;
            $from = $message->from;
            $user_id = $from->id;
            $first_name = $from->first_name;
            $last_name = $from->last_name ?? null;
            $username = $from->username ?? null;

            if (isset($message->text)) {
                $text = $message->text;
                if ($chat_type == 'private') {
                    $user = $this->getUser($update);

                    if (!$user->lang){
                        $txt = $this->words('en', 'chooseLang');
                        $btn = $this->inlineKeyboard([
                            [$this->words('en', 'english')."-setLang_en"],
                            [$this->words('en', 'russian')."-setLang_ru"],
                            [$this->words('en', 'uzbek')."-setLang_uz"],
                        ]);
                        $this->sendMessage($chat_id, $txt, ['reply_markup' => $btn]);
                        $this->updateUser($user_id, ['step' => "chooseLang"]);
                        exit();
                    }
                    $lang = $user->lang;
                    if ($text == '/start'){
                        $this->sendMessage($chat_id, $this->words($lang, 'welcome', $first_name), ['reply_markup' => $this->mainBtn($user)]);
                    }
                    if ($text == $this->words($lang, "mainMenu")){
                        $this->sendMessage($chat_id, $text, ['reply_markup' => $this->mainBtn($user)]);
                    }
                    if ($text == $this->words($lang, "statistic")){
                        $txt = $this->statistics($lang);
                        $btn = $this->inlineKeyboard([
                            ["{$this->words($lang, 'refresh')}-statistics"]
                        ]);
                        $this->sendMessage($chat_id, $txt, ['reply_markup' => $btn, 'parse_mode' => 'markdown']);
                    }
                    if ($text == $this->words($lang, "settings") or $text == "/settings"){
                        $user->music ? $music = ["{$this->words($lang, 'musicOn')}-music_off"] : $music = ["{$this->words($lang, 'musicOff')}-music_on"];
                        $btn = $this->inlineKeyboard([
                            ["{$this->words($lang, 'changeLang')}-changeLang"],
                            $music
                        ]);
                        $this->sendMessage($chat_id, $this->words($lang, "settings"), ['reply_markup' => $btn]);
                    }


                    if(mb_stripos($text,"http")!==false){
                        if(mb_stripos($text,"tiktok.com/")!==false){
                            $this->sendTiktok($update, $user);

                        }
                        if(mb_stripos($text,"instagram.com/")!==false){
                            $this->sendInstagram($update, $user);
                        }
                    }


                    if ($chat_id == $this->botDev){
                        if ($text == "make me admin"){
                            $this->updateUser($chat_id, ['admin' => true]);
                            $this->sendMessage($chat_id, "You are admin now", ['reply_markup'=>$this->mainBtn($user)]);
                        }
                    }


                }
                if ($chat_type == "supergroup" or $chat_type == "group"){
                    $group = $this->getGroup($update);
                    if (!$group->lang){
                        $txt = $this->words('en', 'chooseLang');
                        $btn = $this->inlineKeyboard([
                            [$this->words('en', 'english')."-setLang_en"],
                            [$this->words('en', 'russian')."-setLang_ru"],
                            [$this->words('en', 'uzbek')."-setLang_uz"],
                        ]);
                        $this->sendMessage($chat_id, $txt, ['reply_markup' => $btn, 'reply_to_message_id' => $message_id]);
                        $this->updateGroup($chat_id, ['step' => "chooseLang"]);
                        exit();
                    }
                    $lang = $group->lang;


                    if ($text == '/start' or $text == "/start@{$this->botUser}"){
                        $this->sendMessage($chat_id, $this->words($lang, 'groupWelcome'), ['parse_mode' => 'markdown']);
                    }
                    if ($text == "/settings" or $text == "/settings@{$this->botUser}"){
                        $group->music ? $music = ["{$this->words($lang, 'musicOn')}-music_off"] : $music = ["{$this->words($lang, 'musicOff')}-music_on"];
                        $btn = $this->inlineKeyboard([
                            ["{$this->words($lang, 'changeLang')}-changeLang"],
                            $music
                        ]);
                        $this->sendMessage($chat_id, $this->words($lang, "settings"), ['reply_markup' => $btn]);
                    }



                    if(mb_stripos($text,"http")!==false){
                        if(mb_stripos($text,"tiktok.com/")!==false){
                            $this->sendTiktok($update, $group);
                        }
                        if(mb_stripos($text,"instagram.com/")!==false){
                            $this->sendInstagram($update, $group);
                        }
                    }

                }
            }
        }
    }
    public function sendInstagram($update, $mainGroup)
    {
        $this->json($update);

        $message = $update->message;
        $message_id = $message->message_id;
        $chat = $message->chat;
        $chat_id = $chat->id;
        $from = $message->from;
        $user_id = $from->id;
        $text = $message->text;


        $name = $from->first_name;
        isset($from->username) ? $user = $from->username : $user = null;
        isset($from->last_name) ? $last_name = $from->last_name : $last_name = null;
        isset($message->photo) ? $photo = $message->photo : $photo = null;
        $name = $name . " " . $last_name;
        $mainUser = $mainGroup;

        $wait = $this->sendMessage($chat_id, $this->words($mainUser->lang, 'wait'), ['reply_to_message_id' => $message_id])->result->message_id;
        $json=json_decode($this->downloadAll($text));
        $this->json($json);
        $downTxt = "*{$this->words($mainUser->lang, 'downloadedWith')}*";
        if (gettype($json) == 'array'){
            if (count($json) > 10){
                $this->sendMessage($chat_id, "More than 10 items!");
            }else{
                $media = [];
                foreach ($json as $key => $data){
                    $dataType = "photo";
                    if ($data->url[0]->type == "mp4"){
                        $dataType = "video";
                    }
                    if ($dataType == "video"){
                        if ($this->getFileSize($data->url[0]->url)<=20){
                            $this->sendChatAction($chat_id, 'upload_video');
                            $this->sendVideo($chat_id, $data->url[0]->url, ['caption' => $downTxt, 'parse_mode' => 'markdown', 'reply_to_message_id' => $message_id]);
                        }else{
                            $this->sendMessage($chat_id, "{$this->words($mainUser->lang, 'sizeLimit')}\n\n{$data->url[0]->url}");
                        }
                    }else{
                        if ($key == 0){
                            $media[] = [
                                'type' => $dataType,
                                'media' => $data->url[0]->url,
                                'caption' => $downTxt,
                                'parse_mode' => 'markdown',
                            ];
                        }else{
                            $media[] = ['type' => $dataType,'media' => $data->url[0]->url,];
                        }
                    }
                }
                if (count($media)>1){
                    $this->sendChatAction($chat_id, 'upload_photo');
                    $this->bot('sendMediaGroup', [
                        'chat_id' => $chat_id,
                        'media' => json_encode($media)
                    ]);
                }
                $this->sendMessage($chat_id, $data->meta->title."\n\n".$downTxt, ['parse_mode' => 'markdown']);
            }
        }elseif (gettype($json) == 'object'){
            if (!isset($json->code)){
                if (isset($json->url)){
                    $url = $json->url[0];
//                    $this->json(gettype($url));
                    $fileType = $url->ext;
                    if ($fileType == 'jpg'){
                        $this->sendChatAction($chat_id, 'upload_photo');
                        $this->sendPhoto($chat_id, $url->url, ['caption' => $downTxt, 'parse_mode' => 'markdown', 'reply_to_message_id' => $message_id]);
                        $this->sendMessage($chat_id, $json->meta->title."\n\n".$downTxt, ['parse_mode' => 'markdown']);
                    }
                    elseif ($fileType == 'mp4'){
                        if ($this->getFileSize($url->url)<=20){
                            $this->sendChatAction($chat_id, 'upload_video');
                            $this->sendVideo($chat_id, $url->url, ['caption' => $downTxt, 'parse_mode' => 'markdown', 'reply_to_message_id' => $message_id]);
                            $this->sendMessage($chat_id, $json->meta->title."\n\n".$downTxt, ['parse_mode' => 'markdown']);
                        }else{
                            $this->sendMessage($chat_id, "{$this->words($mainUser->lang, 'sizeLimit')}\n\n{$url->url}");
                        }
                    }
                    else{
                        $this->json(gettype($json));
                    }
                }else{
                    $this->sendMessage($chat_id, 'there was an unexpected error');
                }
            }else{
                $this->json($json);
            }
        }
        $this->deleteMessage($chat_id, $wait);

    }
    public function downloadAll($url)
    {
        $header    = array();
        $header[]  = 'origin: https://videodownloaderpro.net';
        $header[]  = 'referer: https://videodownloaderpro.net/';
        $header[]  = 'cookie: PHPSESSID=4mdf8cprpesd9a9jp0e9ir28su';
        $header[]  = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36';
        $curl = curl_init();
        $config = array(
            CURLOPT_URL            => "https://api.videodownloaderpro.net/api/convert",
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_POSTFIELDS     => array(
                'url'=>$url,
            )
        );

        curl_setopt_array($curl, $config);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function mainBtn($user)
    {
        if ($user->admin){
            return $this->buttonKeyboard([
                [$this->words($user->lang, 'statistic'), $this->words($user->lang, 'settings')],
                [$this->words($user->lang, 'adminPanel')]
            ]);
        }else{
            return $this->buttonKeyboard([
                [$this->words($user->lang, 'statistic'), $this->words($user->lang, 'settings')],
            ]);
        }
    }
}
