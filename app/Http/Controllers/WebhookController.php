<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\WhatsappApiService;

class WebhookController extends Controller
{

    private $whatsappApiService;

    public function __construct(WhatsappApiService $whatsappApiService)
    {
        // Apply middleware to the controller

        // Inject the WhatsappApiService dependency
        $this->whatsappApiService = $whatsappApiService;
    }
    //     public function handleWebhook(Request $request)
    //     {
    //         $webhookData = $request->all();

    //         // Log the received webhook data
    //         \Log::info('Received Webhook:', $webhookData);

    //         // Check if the type is 'message'
    //         if (isset($webhookData['type']) && $webhookData['type'] === 'message') {
    //             // Extract the message details
    //             $messageData = $webhookData['body']['message'] ?? null;
    //             $msgContent = $messageData['extendedTextMessage']['text'] ?? null; // Accessing the text from extendedTextMessage
    //             $messageId = $webhookData['body']['key']['id'] ?? null; // Extracting the message ID if needed
    //             $sender = $webhookData['body']['pushName'] ?? null; // Extracting the sender's name

    //             // Log the message content if available
    //             if ($msgContent) {
    //                 \Log::info('Message Content:', [$msgContent]);
    //             } else {
    //                 \Log::info('No message content available.');
    //             }

    //             // Log additional information if needed
    //             \Log::info('Sender:', [$sender]);
    //             \Log::info('Message ID:', [$messageId]);
    //         } else {
    //             \Log::info('Received non-message type:', [$webhookData['type'] ?? 'unknown']);
    //         }

    //         // Send a response back to acknowledge the webhook was received
    //         return response()->json(['message' => 'Webhook received'], 200);
    //     }
    // }

    public function handleWebhook(Request $request)
    {
        $webhookData = $request->all();
        $details = '';
        // Check if the type is 'message'
        if (isset($webhookData['type']) && $webhookData['type'] === 'message') {
            // Extract the message details
            $messageData = $webhookData['body']['message'] ?? null;
            $messageId = $webhookData['body']['key']['id'] ?? null; // Extracting the message ID if needed
            $sender = $webhookData['body']['pushName'] ?? null; // Extracting the sender's name
            $remoteJid = $webhookData['body']['key']['remoteJid'] ?? null; // Extracting the sender's name

            // Initialize msgContent and caption
            $msgContent = null;
            $caption = null;
            $mobile_no = null;
            $msgText = $messageData['conversation']
                ?? ($messageData['extendedTextMessage']['text'] ?? null);
            // Log the message content if available
            if ($msgText) {
                $details .= "<p>$msgText</p>";
                \Log::info('Message Content:', [$msgText]);
            } else {
                \Log::info('No message content available.');
            }
            if ($remoteJid) {
                $whatsappId = $remoteJid;
                $split = explode('@', $whatsappId);
                $mobile_no = $split[0];
                // The result will be an array
                print_r($split);
                \Log::info(' remoteJid :', [$remoteJid]);
            } else {
                \Log::info('No  remoteJid t available.');
            }
            // Handle image messages
            if (isset($messageData['imageMessage'])) {
                $imageMessage = $messageData['imageMessage'];
                $caption = $imageMessage['caption'] ?? null; // Get the caption of the image

                // Extract the Base64 content from the webhook data
                $msgContent = $webhookData['body']['msgContent'] ?? null; // Accessing msgContent directly

                // Log the image URL
                $imageUrl = $imageMessage['url'] ?? null; // Get the URL of the image
                if ($imageUrl) {
                    \Log::info('Image URL:', [$imageUrl]);
                } else {
                    \Log::info('No image URL available.');
                }

                // Log the Base64 encoded message content
                if ($msgContent) {
                    // Decode the Base64 content
                    $imageData = base64_decode($msgContent);

                    // Generate a unique filename for the image
                    $fileName = 'image_' . time() . '_' . uniqid() . '.jpg'; // You can modify the extension based on your needs
                    $filePath = public_path('files/kb/' . $fileName); // Adjust the path where you want to save the image

                    // Ensure the directory exists
                    if (!file_exists(public_path('images'))) {
                        mkdir(public_path('images'), 0755, true);
                    }
                    $customUrl = url("/files/kb/{$fileName}");

                    // Save the image
                    file_put_contents($filePath, $imageData);
                    $details .= "<p>{$caption}<img src=\"/files/kb/{$fileName}\"></p><a href=\"{$customUrl}\">open</a>";
                    // Log the success of saving the image
                    \Log::info('Image saved to:', [$filePath]);
                } else {
                    \Log::info('No Base64 message content available.');
                }
            }
            $assigned_to = null;
           
            if ($mobile_no == '966509290196' || $mobile_no == '923428927305') {
                $assigned_to = User::where('phone', $mobile_no)->first()->id;
            }
            // Handle empty fields gracefully
            $user = User::where('phone', $mobile_no)->first();
            if (empty($user)) {
                $userRequest = [
                    'first_name' => $mobile_no,
                    'last_name' => 'whatsapp',
                    'phone' => $mobile_no,
                    'email' => $mobile_no . '@gmail.com'
                ];

                // Fetch the customer role
                $customerRole = Role::where('slug', 'customer')->first();
                if (!empty($customerRole)) {
                    $userRequest['role_id'] = $customerRole->id;
                }

                // Create a new user and assign it to the $user variable
                $user = User::create($userRequest);
            }
            $ticket_open = null;
            preg_match('/#(\d+)/', $details, $matches);
            // Check if a match was found and output it
            if (!empty($matches)) {
                $result = str_replace('#', '', $matches[0]);


                \Log::info("The number after # is:$assigned_to " . $result . $details);
             
                $ticket_open = Ticket::where('uid', $result)->first();
            }else{
                 if(empty($ticket_open) && $user->id !=$assigned_to)
                $ticket_open = Ticket::whereDate('open', Carbon::now()) // Check if 'open' matches the current time
->whereNull('close')
->where('user_id' , $user->id)       // Check if 'cloase' is null
->first();
            }

            if (!empty($ticket_open)) {
                Comment::create([
                    'details' => $details,
                    'ticket_id' => $ticket_open->id,
                    'user_id' => $user->id
                ]);
            } else {
                $request_data = [
                    'user_id' => $user->id,
                    //'status_id' => 2,
                    'assigned_to' => $assigned_to,
                    'subject' => $caption ?: $msgText ?: "No Subject",
                    // Default subject if empty
                    'details' => $details,
                ];

                // Create a new ticket
                $ticket = Ticket::create($request_data);
                $ticket->uid = app(abstract: 'App\HelpDesk')->getUniqueUid($ticket->id);
                $ticket->save();
                $response = $this->whatsappApiService->sendTestMsg(
                    '888',
                    $user->phone,
                    "Thank you for contacting us, your ticket has been created with ticket #$ticket->uid.\nWe will get back to you soon."
                );
            }
            // Log additional information if needed

        } else {
            \Log::info('Received non-message type:', [$webhookData['type'] ?? 'unknown']);
        }

        // Send a response back to acknowledge the webhook was received
        return response()->json(['message' => 'Webhook received'], 200);
    }
}


