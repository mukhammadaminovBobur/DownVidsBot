<?php

namespace App\Http\Controllers;

use App\Http\Traits\BotTrait;
use App\Http\Traits\DataTrait;
use App\Http\Traits\WordsTrait;
use Illuminate\Http\Request;

class DownVidsBotController extends Controller
{

    use BotTrait, WordsTrait, DataTrait;

    public $usersModel = "App\Models\DVBUser";
    public $groupsModel = "App\Models\DVBGroup";
    public $tiktoksModel = "App\Models\DVBTiktok";

    public function index()
    {
        $cancelText = "Cancel";
        $resetText = "Reset";
        $update = json_decode(file_get_contents('php://input'));

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
                            $this->sendchataction($chat_id, 'typing');
                        }


                        if(mb_stripos($text,"instagram.com/")!==false){
//                            if ($chat_id == $this->botDev)
//                            $this->json(DownloadsController::instagram($text));
//                            $this->sendInstagram($update, $user);
                        }
                        if(mb_stripos($text,"youtube.com/")!==false){
//                            $this->sendYoutube($update, $user);
                        }
                    }


                    if ($chat_id == $this->botDev){
                        if ($text == "make me admin"){
                            $this->updateUser($chat_id, ['admin' => true]);
                            $this->sendMessage($chat_id, "You are admin now", ['reply_markup'=>$this->mainBtn($user)]);
                        }
                        if ($text == '/sendGroup'){
                            $this->updateUser($chat_id, ['step' => 'sendGroup']);
                            $btn =  $this->buttonKeyboard([[$cancelText]]);
                            $this->sendMessage($chat_id, "ur text message to groups", ['reply_to_message_id' => $message_id, 'reply_markup' => $btn]);
                        }
                        if ($user->step == "sendGroup"){
                            if ($text == $cancelText){
                                $this->updateUser($chat_id, ['step' => 'mainMenu']);
                                $this->sendMessage($chat_id, $text, ['reply_markup'=>$this->mainBtn($user)]);
                            }else{
                                $this->updateUser($chat_id, ['step' => 'sendGroupConfirm']);
                                $btn = $this->inlineKeyboard([
                                    ["Send now-sendGroupConfirm"],
                                    ["{$cancelText}-{$cancelText}", "{$resetText}-{$resetText}"],
                                ]);
                                $this->sendMessage($chat_id, $text, ['reply_markup' => $btn]);
                            }
                        }
                        if ($text == '/sendUser'){
                            $this->updateUser($chat_id, ['step' => 'sendUser']);
                            $btn =  $this->buttonKeyboard([[$cancelText]]);
                            $this->sendMessage($chat_id, "ur text message to users", ['reply_to_message_id' => $message_id, 'reply_markup' => $btn]);
                        }
                        if ($user->step == "sendUser"){
                            if ($text == $cancelText){
                                $this->updateUser($chat_id, ['step' => 'mainMenu']);
                                $this->sendMessage($chat_id, $text, ['reply_markup'=>$this->mainBtn($user)]);
                            }else{
                                $this->updateUser($chat_id, ['step' => 'sendUserConfirm']);
                                $btn = $this->inlineKeyboard([
                                    ["Send now-sendUserConfirm"],
                                    ["{$cancelText}-{$cancelText}", "{$resetText}-{$resetText}"],
                                ]);
                                $this->sendMessage($chat_id, $text, ['reply_markup' => $btn]);
                            }
                        }
                    }


                }

            }

        }


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
