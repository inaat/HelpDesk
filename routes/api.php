<?php

use App\Events\TicketCreated;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailFromHtml;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('webhook', action: [WebhookController::class, 'handleWebhook']);


 // Ensure you import your Mailable class

Route::get('/test-email', function () {
    // Ensure the template is fetched properly
    $text = "1 <p>#254924 fghhhh</p>";

// Use a regular expression to find the pattern
// Use a regular expression to find the pattern and remove the #
preg_match('/#(\d+)/', $text, $matches);
// Check if a match was found and output it
if (!empty($matches)) {
    $result = str_replace('#', '', $matches[0]);
    return $result; // Output: #254924
} else {
    return "No match found.";
}
    // Prepare the message data
    $messageData = [
        'html' => '<p>sadasasdasdsadasd</p>', // Fixed the quotes and removed the invalid syntax
        'subject' => 'Ticket#', // Corrected subject format
    ];
    
    // Send the email
    try {
        // Fixed the email address format (removed extra 'l' at the end)
        Mail::to('inayatullahkks@gmail.com')->send(new SendMailFromHtml($messageData)); 
        return response()->json(['status' => 'Email sent successfully.']);
    } catch (\Exception $e) {
        // Handle the exception
        return response()->json(['status' => 'Failed to send email.', 'error' => $e->getMessage()], 500);
    }
});



// public function handleWebhook(Request $request)
// {
//     $webhookData = $request->all();

//     // Log the received webhook data
//     \Log::info('Received Webhook:', $webhookData);
//     $details = '';
//     // Check if the type is 'message'
//     if (isset($webhookData['type']) && $webhookData['type'] === 'message') {
//         // Extract the message details
//         $messageData = $webhookData['body']['message'] ?? null;
//         $messageId = $webhookData['body']['key']['id'] ?? null; // Extracting the message ID if needed
//         $sender = $webhookData['body']['pushName'] ?? null; // Extracting the sender's name
//         $remoteJid = $webhookData['body']['key']['remoteJid'] ?? null; // Extracting the sender's name
        
//         // Initialize msgContent and caption
//         $msgContent = null;
//         $caption = null;
//         $mobile_no = null;
//         $msgText = $messageData['conversation'] 
//         ?? ($messageData['extendedTextMessage']['text'] ?? null);
//         // Log the message content if available
//         if ($msgText) {
//             $details .= "<p>$msgText</p>";
//             \Log::info('Message Content:', [$msgText]);
//         } else {
//             \Log::info('No message content available.');
//         }
//         if ($remoteJid) {
//             $whatsappId = '923428927305@s.whatsapp.net';
//             $split = explode('@', $whatsappId);
//             $mobile_no = $split[0];
//             // The result will be an array
//             print_r($split);
//             \Log::info(' remoteJid :', [$remoteJid]);
//         } else {
//             \Log::info('No  remoteJid t available.');
//         }
//         // Handle image messages
//         if (isset($messageData['imageMessage'])) {
//             $imageMessage = $messageData['imageMessage'];
//             $caption = $imageMessage['caption'] ?? null; // Get the caption of the image

//             // Extract the Base64 content from the webhook data
//             $msgContent = $webhookData['body']['msgContent'] ?? null; // Accessing msgContent directly

//             // Log the image URL
//             $imageUrl = $imageMessage['url'] ?? null; // Get the URL of the image
//             if ($imageUrl) {
//                 \Log::info('Image URL:', [$imageUrl]);
//             } else {
//                 \Log::info('No image URL available.');
//             }

//             // Log the Base64 encoded message content
//             if ($msgContent) {
//                 // Decode the Base64 content
//                 $imageData = base64_decode($msgContent);

//                 // Generate a unique filename for the image
//                 $fileName = 'image_' . time() . '_' . uniqid() . '.jpg'; // You can modify the extension based on your needs
//                 $filePath = public_path('files/kb/' . $fileName); // Adjust the path where you want to save the image

//                 // Ensure the directory exists
//                 if (!file_exists(public_path('images'))) {
//                     mkdir(public_path('images'), 0755, true);
//                 }
//                 $customUrl = url("/files/kb/{$fileName}");

//                 // Save the image
//                 file_put_contents($filePath, $imageData);
//                 $details .= "<p>{$caption}<img src=\"/files/kb/{$fileName}\"></p><a href=\"{$customUrl}\">open</a>";
//                 // Log the success of saving the image
//                 \Log::info('Image saved to:', [$filePath]);
//             } else {
//                 \Log::info('No Base64 message content available.');
//             }
//         }
//         // Handle empty fields gracefully
//         $user = User::where('phone', $mobile_no)->first();
//         if (empty($user)) {
//             $userRequest = [
//                 'first_name' => $mobile_no,
//                 'last_name' => 'whatsapp',
//                 'phone' => $mobile_no,
//                 'email' => $mobile_no . '@gmail.com'
//             ];

//             // Fetch the customer role
//             $customerRole = Role::where('slug', 'customer')->first();
//             if (!empty($customerRole)) {
//                 $userRequest['role_id'] = $customerRole->id;
//             }

//             // Create a new user and assign it to the $user variable
//             $user = User::create($userRequest);
//         }
//         $ticket_open = Ticket::whereDate('open', Carbon::now()) // Check if 'open' matches the current time
//             ->whereNull('close')
//             ->where('user_id' , $user->id)
//                        // Check if 'cloase' is null
//             ->first();
//         if (!empty($ticket_open)) {
//             Comment::create([
//                 'details' => $details,
//                 'ticket_id' =>  $ticket_open->id,
//                 'user_id' => $user->id
//             ]);
//         } else {
//             $request_data = [
//                 'user_id' => $user->id,
//                 'status_id' => 2,
//                 'subject' => $caption ?: $msgText ?: "No Subject",
//                 // Default subject if empty
//                 'details' => $details,
//             ];

//             // Create a new ticket
//             $ticket = Ticket::create($request_data);
//             $ticket->uid = app(abstract: 'App\HelpDesk')->getUniqueUid($ticket->id);
//             $ticket->save();
//         }
//         // Log additional information if needed
//         \Log::info('Sender:', [$sender]);
//         \Log::info('Message ID:', [$messageId]);
//     } else {
//         \Log::info('Received non-message type:', [$webhookData['type'] ?? 'unknown']);
//     }

//     // Send a response back to acknowledge the webhook was received
//     return response()->json(['message' => 'Webhook received'], 200);
// }