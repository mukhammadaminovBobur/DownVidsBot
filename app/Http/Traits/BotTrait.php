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
    public function sendPhoto($chat_id, $photo, $extra = [])
    {
        return $this->bot('sendPhoto', array_merge(
            [
                'chat_id' => $chat_id,
                'photo' => $photo,
            ], $extra
        ));
    }
    public function sendVideo($chat_id, $video, $extra = [])
    {
        return $this->bot('sendVideo', array_merge(
            [
                'chat_id' => $chat_id,
                'video' => $video,
            ], $extra
        ));
    }
    public function sendAudio($chat_id, $audio, $extra = [])
    {
        return $this->bot('sendAudio', array_merge(
            [
                'chat_id' => $chat_id,
                'audio' => $audio,
            ], $extra
        ));
    }
    public function sendChatAction($chat_id, $action)
    {
        return $this->bot('sendChatAction', [
            'chat_id' => $chat_id,
            'action' => $action,
        ]);
    }
    public function answerCallbackQuery($callback_query_id, $text, $alert = false)
    {
        $this->bot('answerCallbackQuery', [
            "callback_query_id" => $callback_query_id,
            "text" => $text,
            "show_alert" => $alert,
        ]);
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
        $longText = json_encode($update, JSON_PRETTY_PRINT);
        if (strlen($longText) > 4096){
            $textChunks = str_split($longText, 4096);
            foreach ($textChunks as $textChunk){
                $this->sendMessage($this->botDev, $textChunk);
            }
        }else{
            $this->sendMessage($this->botDev, $longText);
        }
    }




}
