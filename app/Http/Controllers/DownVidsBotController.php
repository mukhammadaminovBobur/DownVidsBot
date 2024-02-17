<?php

namespace App\Http\Controllers;

use App\Http\Traits\BotTrait;
use Illuminate\Http\Request;

class DownVidsBotController extends Controller
{

    use BotTrait;

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
            $last_name = $from->last_name ?? null;
            $username = $from->username ?? null;

            if (isset($message->text)) {
                $text = $message->text;

                if ($text == "/start"){
                    $this->sendMessage($chat_id, "Hello $first_name");
                }

            }

        }


    }
}
