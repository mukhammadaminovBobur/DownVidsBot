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
        $key = env('RAPID_KEY');
        $host = "social-download-all-in-one.p.rapidapi.com";
        $endpoint = "https://social-download-all-in-one.p.rapidapi.com/v1/social/autolink";
        return json_decode(Http::withHeaders([
            "X-RapidAPI-Key" => $key,
            "X-RapidAPI-Host" => $host,
            'content-type' => 'application/json',
        ])->post($endpoint, ["url" => $url,]));
    }
    public function checkTiktok($url)
    {
        return json_decode(Http::get("https://tiktok.com/oembed?url=".$url));
    }

    public function getVideoSize($url)
    {
        // Get the headers for the URL
        $headers = get_headers($url, true);

        // Check if the 'Content-Length' header is present
        if (isset($headers['Content-Length'])) {
            // Get the size of the video in bytes
            $sizeInBytes = $headers['Content-Length'];

            // Convert the size to a more human-readable format (e.g., kilobytes or megabytes)
            $sizeInKb = round($sizeInBytes / 1024, 2); // Size in kilobytes
            $sizeInMb = round($sizeInBytes / (1024 * 1024), 2); // Size in megabytes

            return $sizeInMb;
        }

        // If 'Content-Length' is not present in the headers, return null or handle it accordingly
        return null;
    }
}
