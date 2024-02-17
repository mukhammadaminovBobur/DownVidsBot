<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait BotTrait
{

    public $botToken = "5079517440:AAGIsVvzLIjDGuY52U_nvQ8pZ9XdVWAI9bQ";
    public $botUser = "DownVidsBot";
    public $botDev = "716294792";

    public function bot($method, $data = []){
        $url = "https://api.telegram.org/bot".$this->botToken."/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            $this->json(curl_error($ch));
            return true;
        } else {
            http_response_code(200);
            return json_decode($res);
        }
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

}
