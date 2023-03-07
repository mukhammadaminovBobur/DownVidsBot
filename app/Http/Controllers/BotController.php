<?php

namespace App\Http\Controllers;

use App\Http\Traits\BotTrait;
use App\Http\Traits\DataTrait;
use App\Http\Traits\WordsTrait;
use Carbon\Carbon;

class BotController extends Controller
{
    use BotTrait, WordsTrait, DataTrait;


    public $usersModel = "App\Models\BotUser";
    public $groupsModel = "App\Models\BotGroup";

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
                $ex = explode('_', $callback_data);
                if (count($ex) == 2) {
                    if ($ex[0] == 'setLang') {
                        $lang = $ex[1];
                        if ($callback_chat->type == "private") {
                            $user = $this->getUser($update);
                            $this->updateUser($callback_from_id, ['lang' => $lang]);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            if ($user->step == "chooseLang"){
                                $this->sendMessage($callback_chat_id, $this->words($lang, 'welcome', $callback_name), ['reply_markup' => $this->mainBtn($user)]);
                            }else{
                                $this->sendMessage($callback_chat_id, $this->words($lang, "languageSet"), ['reply_markup' => $this->mainBtn($user)]);
                            }
                            $this->updateUser($callback_from_id, ['step' => 'langSet']);
                        }
                        if ($callback_chat->type == "group" or $callback_chat->type == "supergroup"){
                            $group = $this->getGroup($update);
                            $this->updateGroup($callback_chat_id, ['lang' => $lang]);
                            $this->deleteMessage($callback_chat_id, $callback_message_id);
                            if ($group->step == "chooseLang"){
                                $this->sendMessage($callback_chat_id, $this->words($lang, 'groupWelcome'));
                            }else{
                                $this->sendMessage($callback_chat_id, $this->words($lang, "languageSet"));
                            }
                            $this->updateGroup($callback_chat_id, ['step' => "langSet"]);
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
            $last_name = isset($from->last_name) ?? $from->last_name;
            $username = isset($from->username) ?? $from->username;

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
                    if ($text == 'time'){
                        $this->sendMessage($chat_id, Carbon::now()->format('Y-m-d H:i:s'));
                    }


                    if ($text == 'c'){
                        $this->usersModel::truncate();
                        $this->groupsModel::truncate();
                        $this->sendMessage($chat_id, "done");
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
