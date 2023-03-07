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
//            $this->json(gettype(json_decode($message)));
            $this->json(json_decode($message));
            $this->sendMessage($this->botDev, $message);
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
    public function json($update)
    {
        $this->sendMessage($this->botDev, json_encode($update, JSON_PRETTY_PRINT));
    }

}
