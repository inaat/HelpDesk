<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappApiService
{
   private $baseUrl = 'http://localhost:3333';
    //private $baseUrl = 'http://sender.injazatsoftware.net';

    private $token = 'YOUR_TOKEN'; 
    private $adminToken='da71b564a1ed7e998204ca0d7cae38e791ca2154';

    public function instanceInit($instance)
    {
        $apiURL = $this->baseUrl . '/instance/init?';

        $postInput = [
            "key" => $instance,
            "browser" => "Chrome (Linux)",
            "webhook" => false,
            "base64" => true,
            "webhookUrl" => "",
            "webhookEvents" => ["messages.upsert"],
            "ignoreGroups" => true,
            "messagesRead" => false
        ];

        return $this->makeApiCall($apiURL, 'post', $postInput);
    }

    public function getQrCodebase64($instance)
    {
        $apiURL = $this->baseUrl . '/instance/qrbase64?key=' . $instance;

        return $this->makeApiCall($apiURL, 'get');
    }

    public function sendTestMsg($instance, $number, $text)
    {
        $apiURL = $this->baseUrl . '/message/text?key=' . $instance;

        $postInput = [
            "id" => $number,
            "typeId" => "user",
            "message" => $text,
            "options" => [
                "delay" => 0,
                "replyFrom" => ""
            ],
            "groupOptions" => [
                "markUser" => "ghostMention"
            ]
        ];

        return $this->makeApiCall($apiURL, 'post', $postInput);
    }
 public function sendDocument($instance,$filePath,$number,$filename,$caption){
  

    $apiURL = $this->baseUrl . '/message/imageFile?key=' . $instance;

    $response = Http::attach(
        'file',
        file_get_contents($filePath), // Read the file contents from the full path
        $filename // Filename to send // Use basename() to get the file name
    )->withToken($this->token)
    ->post($apiURL, [
        'id'              => $number,
        'filename'        => $filename,
        'userType'        => 'user',
        'replyFrom'       => '',
        'caption'         => $caption
    ]);
    $responseData = $response->json();
    return $responseData;
 }
    private function makeApiCall($url, $method, $data = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Bearer ' . $this->token,
            'admintoken' =>$this->adminToken
        ];

        $http = Http::withoutVerifying()->withHeaders($headers);

        if ($method === 'post') {
            return $http->post($url, $data)->json();
        } elseif ($method === 'get') {
            return $http->get($url)->json();
        }

        // Handle other HTTP methods if needed
        return null;
    }
}
