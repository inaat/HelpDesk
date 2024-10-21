<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\WhatsappApiService;
use Log;

class WebhookController extends Controller
{
    private $whatsappApiService;

    public function __construct(WhatsappApiService $whatsappApiService)
    {
        $this->whatsappApiService = $whatsappApiService;
    }

    // public function handleWebhook(Request $request)
    // {
    //     $webhookData = $request->all();
    //     Log::info('Received Webhook:', $webhookData);
    //     $details = '';
    //     $ticket_create=false;
    //     // Handle messages
    //     if (isset($webhookData['body']['message'])) {
    //         $this->handleMessages($webhookData, $details , $ticket_create);
    //     } else {
    //         Log::info('Received non-message type:', [$webhookData['type'] ?? 'unknown']);
    //     }

    //     return response()->json(['message' => 'Webhook received'], 200);
    // }
    public function handleWebhook(Request $request)
    {
        try {
            $webhookData = $request->all();
            Log::info('Received Webhook:', $webhookData);
            $details = '';
            $ticket_create = false;
    
            // Handle messages
            if (isset($webhookData['body']['message'])) {
                $this->handleMessages($webhookData, $details, $ticket_create);
            } else {
                Log::info('Received non-message type:', [$webhookData['type'] ?? 'unknown']);
            }
    
            return response()->json(['message' => 'Webhook received'], 200);
        } catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the webhook'], 500);
        }
    }
    private function handleMessages($webhookData, &$details ,$ticket_create)
    {
        $messageId = $webhookData['body']['key']['id'] ?? null;
        $messageData = $webhookData['body']['message'];

        // Handle document messages
        if (isset($messageData['documentMessage']) || isset($messageData['documentWithCaptionMessage']['message']['documentMessage'])) {
            $documentMessage = isset($messageData['documentMessage'])
                ? $messageData['documentMessage']
                : $messageData['documentWithCaptionMessage']['message']['documentMessage'];
            $ticket_create=true;  
            $this->processDocument($documentMessage, $webhookData, $messageId, $details);
        }

        // Handle audio messages
        if (isset($messageData['audioMessage'])) {
            $ticket_create=true;  
            $this->processAudio($webhookData, $messageId, $details);
        }

        // Handle video messages
        if (isset($messageData['videoMessage'])) {
            $ticket_create=true;  
           
            $this->processVideo($webhookData, $messageId, $details);
        }
        Log::info('Received message data:', [$messageData]);

            // Handle text messages
            $msgText = $messageData['conversation'] ?? ($messageData['extendedTextMessage']['text'] ?? null);
            if ($msgText) {
                if (strlen($msgText) > 13) {
                    $ticket_create=true;
                
                }
            }

        // Handle text and image messages
        $this->processTextAndImage($webhookData, $messageData, $details ,$ticket_create);
    }

    private function processDocument($documentMessage, $webhookData, $messageId, &$details )
    {
        $fileName = $documentMessage['fileName'];
        $caption = $documentMessage['caption'] ?? '';
        $msgContent = $webhookData['body']['msgContent'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $jpegThumbnail = $documentMessage['jpegThumbnail'] ?? null;

        // Save the document and thumbnail
        $this->saveDocument($msgContent, $messageId, $fileName, $fileExtension);
        $fileUrl = asset("files/pdf/{$messageId}.$fileExtension");

        $thumbnailHtml = $jpegThumbnail
            ? "<img src='" . asset("files/thumbnails/{$messageId}.jpg") . "' alt='Thumbnail' />"
            : "<img src='$fileUrl' alt='Thumbnail' />";

        $details .= "<p class='chat-pdf' data-src='$fileUrl'>$thumbnailHtml <br> $fileName<p><em>$caption</em></p></p>";

        // Save the thumbnail if it exists
        if ($jpegThumbnail) {
            $this->saveThumbnail($jpegThumbnail, $messageId);
        }
    }

    private function processAudio($webhookData, $messageId, &$details )
    {
        $msgContent = $webhookData['body']['msgContent'];
        $this->saveAudioFromBase64($msgContent, $messageId);
        $details .= $this->generateAudioHtml($messageId);
    }

    private function processVideo($webhookData, $messageId, &$details )
    {
        $msgContent = $webhookData['body']['msgContent'];
        $caption = $webhookData['body']['message']['videoMessage']['caption'] ?? null;

        $this->saveVideoFromBase64($msgContent, $messageId);
        $videoHtml = $this->generateVideoHtml($messageId);
        $details .= "<p>$videoHtml $caption</p>";
    }

    private function processTextAndImage($webhookData, $messageData, &$details  ,$ticket_create)
    {   
       
        $remoteJid = $webhookData['body']['key']['remoteJid'] ?? null;
        $mobile_no = null;

        // Handle text messages
        $msgText = $messageData['conversation'] ?? ($messageData['extendedTextMessage']['text'] ?? null);
        if ($msgText) {
            $details .= "<p>$msgText</p>";
            
        }

        // Handle image messages
        if (isset($messageData['imageMessage'])) {
            $ticket_create=true;
            $this->processImage($messageData['imageMessage'], $webhookData, $details);
        }

        // Handle mobile number extraction
        if ($remoteJid) {
            $mobile_no = explode('@', $remoteJid)[0];
            Log::info('remoteJid:', [$remoteJid]);
        }

        // Handle user and ticket creation logic
        $this->handleUserAndTicket($mobile_no, $details,$msgText,$ticket_create);
    }

    private function processImage($imageMessage, $webhookData, &$details)
    {
        $caption = $imageMessage['caption'] ?? null;
        $msgContent = $webhookData['body']['msgContent'];
        $imageData = base64_decode($msgContent);
        $fileName = 'image_' . time() . '_' . uniqid() . '.jpg';
        $filePath = public_path('files/kb/' . $fileName);

        if (!file_exists(public_path('images'))) {
            mkdir(public_path('images'), 0755, true);
        }

        file_put_contents($filePath, $imageData);
        $customUrl = url("/files/kb/{$fileName}");
        $details .= "<p>{$caption}<img class='chat-img' src=\"/files/kb/{$fileName}\"></p><a href=\"{$customUrl}\">open</a>";
        Log::info('Image saved to:', [$filePath]);
    }

    private function handleUserAndTicket($mobile_no, $details,$msgText,$ticket_create)
    {
        $assigned_to = null;
        $assigned_phone = null;
        // Fetch phone numbers of users with role_id = 5
        $phone_array = User::where('role_id', 5)->pluck('phone')->toArray();
        // Check if the mobile number exists in the phone array
        if (in_array($mobile_no, $phone_array)) {
            $assigned_to = User::where('phone', $mobile_no)->first();
        }
        $assigned_to = User::where('phone', $mobile_no)->first();
if (!$assigned_to) {
    Log::warning('Assigned user not found for mobile number: ' . $mobile_no);
    // Handle the case where the user is not found
}
        $lastTicket = Ticket::where('assigned_to', $assigned_to->id?? null)->where('review_id', 1)->first();
        preg_match('/#(\d+)/', $details, $checkForwardMatches);
        if (!empty($checkForwardMatches)) {
            $details=  str_replace('#'.$checkForwardMatches[1], '', $details);
            $ticketUid = app('App\HelpDesk')->getDatePrefix() . $checkForwardMatches[1];
            // Get the last ticket UID created today
            $lastTicket = Ticket::where('uid', $ticketUid)->first();
            if (!empty($lastTicket)) {
                $lastTicket->review_id = 1;
                $lastTicket->save();
            }
        }
        if (!empty($lastTicket)) {
           
            if (str_contains($details, '*')) {
                if (!empty($lastTicket)) {
                    $lastTicket->review_id = 0;
                    $lastTicket->save();
                }
                return response()->json(['message' => 'Webhook received'], 200);
            } else {
                Comment::create([
                    'details' => $details,
                    'ticket_id' => $lastTicket->id,
                    'user_id' => $assigned_to->id,
                ]);
            }
            return response()->json(['message' => 'Webhook received'], 200);

        }





        $ticket_open = null;
        $customer = null;

        // Check for customer number in details
        preg_match('/#C(\d+)/', $details, $matchesCustomer);
        if (!empty($matchesCustomer)) {
            $customer_no = str_replace('#C', '', $matchesCustomer[0]);
            $organization = Organization::where('customer_no', $customer_no)->first();
            if ($organization) {
                $customer = User::where('organization_id', $organization->id)->first();
            }
            $details=  str_replace($matchesCustomer[0], '', $details);

        }

        // Handle user creation if customer is not found
        if (empty($customer)) {
            $user = User::where('phone', $mobile_no)->first();
           
            
            $customer = $user;
        } else {
            $assigned_phone = $mobile_no;
        }

        // Check for existing ticket using regex for ticket number
        preg_match('/#(\d+)/', $details, $matches);
        if (!empty($matches)) {
            $ticket_open = Ticket::where('uid', str_replace('#', '', $matches[0]))->first();
            $details=  str_replace($matches[0], '', $details);

        }

        // If no ticket found, check for an open ticket for the user
        if (empty($ticket_open) && ($user->id ?? null) != ($assigned_to->id ?? null)) {
            $ticket_open = Ticket::whereDate('open', Carbon::now())
                ->whereNull('close')
                ->where('user_id', $user->id)
                ->first();
        }

        // If an open ticket exists, add a comment
        if ($ticket_open) {
            Comment::create([
                'details' => $details,
                'ticket_id' => $ticket_open->id,
                'user_id' => $user->id,
            ]);
        } else {
            $subject='No Subject';
            if ($msgText) {
                $subject = $msgText;
                
            }
            // Create a new ticket
           if($ticket_create && !empty($customer)){
            $request_data = [
                'user_id' => $customer ? $customer->id : null,
                'department_id' => $assigned_to ? $assigned_to->department_id : 2,
                'status_id' => 2,
                'priority_id' => 3,
                'type_id' => 4,
                'assigned_to' => $assigned_to ? $assigned_to->id : 1,
                'subject' => $subject, // Default subject if empty
                'details' => $details,
                'contact_id'=> $assigned_to ? $assigned_to->id : null,
                'review_id'=>1
            ];

            // Create the ticket and generate a unique UID
            $ticket = Ticket::create($request_data);
            $ticket->uid = app('App\HelpDesk')->getUniqueUid($ticket->id);
            $ticket->save();
            $message = "تم فتح تذكرة رقم #{$ticket->uid} مع المندوب {$ticket->assignedTo->first_name}";

            if($ticket->assignedTo->id!=1){

            // Send notification messages
            $this->whatsappApiService->sendTestMsg('888', $customer->phone, $message);
            }else{
                $this->whatsappApiService->sendTestMsg('888', $customer->phone, 'أهلا وسهلا, سوف يتم فتح تذكرة وإبلاغك بها');
 
            }
            if (!empty($assigned_phone)) {
                $this->whatsappApiService->sendTestMsg('888', $assigned_phone, $message);
            }
            
        }
        }
        return response()->json(['message' => 'Webhook received'], 200);

    }

    protected function saveDocument($msgContent, $messageId, $fileName,$fileExtension) {
        // Create the storage path
        $storagePath = public_path('files/pdf/');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // Define the file path
        $filePath = "{$storagePath}{$messageId}.$fileExtension"; // Save as .pdf or adjust as needed

        // Save the msgContent directly as the document
        file_put_contents($filePath, base64_decode($msgContent)); // Decode the content before saving
        \Log::info("Document saved to: $filePath");
    }

    // Function to save the JPEG thumbnail
    protected function saveThumbnail($jpegThumbnail, $messageId) {
        // Decode the base64 thumbnail
        $thumbnailData = base64_decode($jpegThumbnail);
        if ($thumbnailData === false) {
            \Log::error("Failed to decode JPEG thumbnail for message ID: $messageId");
            return;
        }

        // Create the storage path for thumbnails
        $thumbnailPath = public_path('files/thumbnails/');
        if (!is_dir($thumbnailPath)) {
            mkdir($thumbnailPath, 0777, true);
        }

        // Define the file path for the thumbnail
        $thumbnailFilePath = "{$thumbnailPath}{$messageId}.jpg"; // Save as .jpg

        // Save the thumbnail
        file_put_contents($thumbnailFilePath, $thumbnailData);
        \Log::info("Thumbnail saved to: $thumbnailFilePath");
    }

    // Function to generate HTML for the document link
      
    protected function saveVideoFromBase64($base64Content, $messageId) {
        $videoData = base64_decode($base64Content);
        $filePath = public_path("files/videos/{$messageId}.mp4");
    
        // Ensure the directory exists
        if (!file_exists(public_path('files/videos'))) {
            mkdir(public_path('files/videos'), 0755, true);
        }
    
        // Save video to file
        file_put_contents($filePath, $videoData);
    }
    protected function generateVideoHtml($messageId) {
        $videoUrl  = asset(path: "files/videos/{$messageId}.mp4");

        // Generate HTML for the video player
        return "<video controls>
                    <source src='{$videoUrl}' type='video/mp4'>
                    Your browser does not support the video tag.
                </video>";
    }
private function saveAudioFromBase64($base64Content, $messageId)
{
    // Decode the base64-encoded content
    $audioContent = base64_decode($base64Content);
    
    // Define the file path within the public directory
    $directoryPath = public_path('files/audios');
    
    // Ensure the directory exists
    if (!file_exists($directoryPath)) {
        mkdir($directoryPath, 0755, true);
    }
    
    // Define the full file path
    $filePath = "{$directoryPath}/{$messageId}.ogg";

    // Save the decoded content to a file
    file_put_contents($filePath, $audioContent);
    \Log::info("Audio saved to: {$filePath}");
}

private function generateAudioHtml($messageId)
{
    // Generate the URL for accessing the file in the public directory
    $audioPath = asset(path: "files/audios/{$messageId}.ogg");
    
    // Return HTML code with an audio player
    return "<audio controls>
                <source src='{$audioPath}' type='audio/ogg'>
                Your browser does not support the audio element.
            </audio>";
}
}
