<?php
namespace App\Http\Traits;

use Carbon\Carbon;
use http\Url;
use Illuminate\Support\Facades\Http;

trait DownloadTrait
{
    use BotTrait;

    public function getUrlData($url)
    {
        $key = "bf587b0308msh5d356bcff04fa3ep1e3622jsn8677dce9292c";
        $host = "social-download-all-in-one.p.rapidapi.com";
        $endpoint = "https://social-download-all-in-one.p.rapidapi.com/v1/social/autolink";
        return json_decode(Http::withHeaders([
            "X-RapidAPI-Key" => $key,
            "X-RapidAPI-Host" => $host,
            'content-type' => 'application/json',
        ])->post($endpoint, ["url" => $url,]));
    }
}
