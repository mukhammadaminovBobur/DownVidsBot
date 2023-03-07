<?php

namespace App\Http\Controllers;

use App\Http\Traits\BotTrait;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    use BotTrait;


    public $usersModel = "App\Models\BotUser";

    public function index()
    {
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
            $last_name = isset($from->last_name) ?? $from->last_name;
            $username = isset($from->username) ?? $from->username;

            if (isset($message->text)) {
                $text = $message->text;
                if ($chat_type == 'private') {
                    $user = $this->getUser($update);

                    if ($text == 'c'){
                        $this->usersModel::truncate();
                        $this->sendMessage($chat_id, "done");
                    }
                }
            }
        }
    }
}
