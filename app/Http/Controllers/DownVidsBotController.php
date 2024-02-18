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

        if (isset($update->callback_query)) {
            $callback = $update->callback_query;
            $callback_data = $callback->data;
            $callback_from = $callback->from;
            $callback_from_id = $callback_from->id;
            $callback_message = $callback->message;
            $callback_message_text = $callback_message->text;
            $callback_message_id = $callback_message->message_id;
            $callback_chat = $callback_message->chat;
            $callback_chat_id = $callback_chat->id;
            $callback_name = $callback_from->first_name;

            if ($callback_message){
                $lang = 'en';

                $ex = explode('_', $callback_data);
                if (count($ex) == 2) {
                    if ($ex[0] == 'setLang') {
                        $lang = $ex[1];
                        if ($callback_chat->type == "private") {
                            $user = $this->getUser($update);
                            $this->updateUser($callback_from_id, ['lang' => $lang]);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            if ($user->step == "chooseLang"){
                                $user->lang = $lang;
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
                    if ($user->step == "sendGroupConfirm"){
                        if ($callback_data == $resetText){
                            $this->updateUser($callback_chat_id, ['step' => 'sendGroup']);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $btn =  $this->buttonKeyboard([[$cancelText]]);
                            $this->sendMessage($callback_chat_id, "ur text message to groups", ['reply_markup' => $btn]);
                        }
                        if ($callback_data == $cancelText){
                            $this->updateUser($callback_chat_id, ['step' => 'mainMenu']);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $this->sendMessage($callback_chat_id, $callback_data, ['reply_markup'=>$this->mainBtn($user)]);
                        }
                        if ($callback_data == $user->step){
                            $groups = $this->getGroupIds();
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $this->sendMessage($callback_chat_id, "Sending to ".count($groups)." groups");
                            foreach ($groups as $group){
                                $this->sendMessage($group, $callback_message_text);
                            }
                            $this->sendMessage($callback_chat_id, "Done");
                        }
                    }
                    if ($user->step == "sendUserConfirm"){
                        if ($callback_data == $resetText){
                            $this->updateUser($callback_chat_id, ['step' => 'sendUser']);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $btn =  $this->buttonKeyboard([[$cancelText]]);
                            $this->sendMessage($callback_chat_id, "ur text message to users", ['reply_markup' => $btn]);
                        }
                        if ($callback_data == $cancelText){
                            $this->updateUser($callback_chat_id, ['step' => 'mainMenu']);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $this->sendMessage($callback_chat_id, $callback_data, ['reply_markup'=>$this->mainBtn($user)]);
                        }
                        if ($callback_data == $user->step){
                            $users = $this->getUserIds();
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            $this->sendMessage($callback_chat_id, "Sending to ".count($users)." users");
                            foreach ($users as $userid){
                                $this->sendMessage($userid, $callback_message_text);
                            }
                            $this->sendMessage($callback_chat_id, "Done");
                        }
                    }
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
                    if ($user_id == $this->botDev){
                        if ($text == 'bich'){
                            $lastMonth = Carbon::now()->subMonth(); // Get the date for the first day of the previous month
                            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth(); // Get the date for the last day of the previous month

                            $records = $this->tiktoksModel::whereDate('created_at', '>=', $lastMonth)
                                ->whereDate('created_at', '<=', $endOfLastMonth)
                                ->get()->sum('downloads');
                            $this->sendMessage($chat_id, "yesss?", ['reply_to_message_id' => $message_id]);
//                            $this->sendMessage($chat_id, $records." downloads last month", ['reply_to_message_id' => $message_id]);
                        }
                    }



                    if(mb_stripos($text,"http")!==false){
                        if(mb_stripos($text,"tiktok.com/")!==false){

                        }
                        /*
                        if(mb_stripos($text,"instagram.com/")!==false){
                            $this->sendInstagram($update, $group);
                        }
                        if(mb_stripos($text,"youtube.com/")!==false){
                            $this->sendYoutube($update, $group);
                        }
                        */
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
