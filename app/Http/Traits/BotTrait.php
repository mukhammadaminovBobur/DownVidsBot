<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait BotTrait{

    public $botToken;
    public $botUser;
    public $botDev;

    public function __construct(){
        $this->botToken = config('bot.token');
        $this->botUser = config('bot.user');
        $this->botDev = config('bot.dev');
    }

    public function getRequest($url, $data)
    {
        $response = Http::post($url, $data);
        if ($response->ok()) {
            return $response->json();
        } else {
            $status = $response->status();
            $message = $response->body();
            $this->json(json_decode($message));
//            $this->sendMessage($this->botDev, $message);
        }
    }
    public function bot($method, $data = []){
        $url = "https://api.telegram.org/bot".$this->botToken."/" . $method;
        return $this->getRequest($url, $data);
    }
    public function sendMessage($chat_id, $text, $extra = [])
    {
        return $this->bot('sendMessage', array_merge(
            [
                'chat_id' => $chat_id,
                'text' => $text,
            ], $extra
        ));
    }
    public function deleteMessage($chat_id, $message_ids = [])
    {
        if (gettype($message_ids) == "integer"){
            $this->bot('deleteMessage', [
                'chat_id' => $chat_id,
                'message_id' => $message_ids,
            ]);
        }else{
            foreach ($message_ids as $message_id){
                $this->bot('deleteMessage', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                ]);
            }
        }
    }

    public function inlineKeyboard($array = [])
    {
        $keys = [];
        foreach ($array as $arr){
            $inKeys = [];
            foreach ($arr as $ar){
                $ex = explode('-', $ar);
                $inKeys[] = ['text' => $ex[0], 'callback_data' => $ex[1]];
            }
            $keys[] = $inKeys;
        }
        return json_encode([
            "inline_keyboard" => $keys
        ]);
    }
    public function buttonKeyboard($array = [])
    {
        $keys = [];
        foreach ($array as $arr){
            $inKeys = [];
            foreach ($arr as $ar){
                $inKeys[] = ['text' => $ar];
            }
            $keys[] = $inKeys;
        }
        return json_encode([
            "resize_keyboard" => true,
            "keyboard" => $keys
        ]);
    }

    public function json($update)
    {
        $this->sendMessage($this->botDev, json_encode($update, JSON_PRETTY_PRINT));
    }



}
