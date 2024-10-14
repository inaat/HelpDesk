<?php

namespace App;

use App\Models\Setting;
use App\Models\Ticket;

class HelpDesk{
    /**
     * The HelpDesk version.
     *
     * @var string
     */
    const VERSION = '1';

    /**
     * The envato item ID.
     *
     * @var string
     */
    const ITEM_ID = '';

    public function getSettingsEmailNotifications(): array
    {
        $settingQuery = Setting::where('slug','email_notifications')->first();
       
        $settings = \json_decode($settingQuery->value, true);
        $notifications = [];
        foreach ($settings as $setting){
            $notifications[$setting['slug']] = $setting['value'];
        }
        return $notifications;
    }

    // public function getUniqueUid($id)
    // {
    //     do {
    //         $uid = random_int(100000, 909999)+(int) $id;
    //         $ticketByUid = Ticket::where('uid', $uid)->first();
    //     } while (!empty($ticketByUid));
    //     return $uid;
    // }
//     public function getUniqueUid()
// {
//     // Get the last UID from the Ticket table and increment by one
//     $lastTicket = Ticket::orderBy('uid', 'desc')->first();

//     // Check if there is any ticket
//     if ($lastTicket) {
//         // Get the last UID and increment it
//         $lastUid = (int)$lastTicket->uid;
//         $nextUid = $lastUid + 1;
//     } else {
//         // If no tickets exist, start from 1
//         $nextUid = 1;
//     }

//     // Format the next UID to be 4 digits, padding with zeros
//     $formattedUid = str_pad($nextUid, 4, '0', STR_PAD_LEFT);

//     return $formattedUid;
// }


public function getUniqueUid()
{
    // Get the current date in the required format
    $datePrefix = now()->format('mdy'); // YYMMDD 'ymd'

    // Get the last ticket UID created today
    $lastTicket = Ticket::where('uid', 'like', $datePrefix . '%')
        ->orderBy('uid', 'desc')
        ->first();

    // If there is a last ticket, extract the serial number and increment it
    if ($lastTicket) {
        $lastUid = (int)substr($lastTicket->uid, -3); // Get the last 3 digits
        $nextSerial = $lastUid + 1; // Increment the serial
    } else {
        // If no tickets exist for today, start with 001
        $nextSerial = 1;
    }

    // Format the next serial number to be 3 digits, padding with zeros
    $formattedSerial = str_pad($nextSerial, 3, '0', STR_PAD_LEFT);

    // Combine date prefix with the formatted serial number
    $uid = $datePrefix . $formattedSerial;

    return $uid;
}
function sendHtmlEmail($to, $subject, $message) {
    $smtp_server = '';  // SMTP server
    $smtp_port = 587;                            // SMTP port
    $username = '';  // SMTP username
    $password = '';                    // SMTP password

    // Create a socket connection
    $connection = fsockopen($smtp_server, $smtp_port);

    if (!$connection) {
        die("Failed to connect to SMTP server.");
    }

    // Read server response
    $response = fgets($connection, 512);
    if (strpos($response, '220') === false) {
        die("Error: " . $response);
    }

    // Send HELO command
    fputs($connection, "HELO $smtp_server\r\n");
    $response = fgets($connection, 512);

    // Authenticate
    fputs($connection, "AUTH LOGIN\r\n");
    $response = fgets($connection, 512);

    fputs($connection, base64_encode($username) . "\r\n");
    $response = fgets($connection, 512);

    fputs($connection, base64_encode($password) . "\r\n");
    $response = fgets($connection, 512);

    // Set the sender
    fputs($connection, "MAIL FROM: <$username>\r\n");
    $response = fgets($connection, 512);

    // Set the recipient
    fputs($connection, "RCPT TO: <$to>\r\n");
    $response = fgets($connection, 512);

    // Send data
    fputs($connection, "DATA\r\n");
    $response = fgets($connection, 512);

    // Set headers for HTML email
    fputs($connection, "Subject: $subject\r\n");
    fputs($connection, "From: $username\r\n");
    fputs($connection, "To: $to\r\n");
    fputs($connection, "MIME-Version: 1.0\r\n");
    fputs($connection, "Content-Type: text/html; charset=UTF-8\r\n");
    fputs($connection, "Content-Transfer-Encoding: 8bit\r\n");  // Ensure correct encoding
    fputs($connection, "\r\n");  // This empty line separates headers from the body

    // Send the HTML message body
    $message=view('mail.ticket_updated')->render();
    fputs($connection, "$message\r\n.\r\n");
    $response = fgets($connection, 512);

    // Close connection
    fputs($connection, "QUIT\r\n");
    fclose($connection);

    return "Email sent successfully!";
}
}
