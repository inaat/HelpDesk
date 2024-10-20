<?php

namespace App;

use App\Models\Setting;
use App\Models\Ticket;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

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
    $datePrefix = now()->format('dmy'); // YYMMDD 'ymd'

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
public function getDatePrefix()
{
    // Get the current date in the required format
    $datePrefix = now()->format('dmy'); // YYMMDD 'ymd'


    return $datePrefix;
}

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true); // Create a new PHPMailer instance

    try {
           
        $mail->isSMTP();
       // dd(config('mail.mailers.smtp.host'));
       //$mail->Host       = config('mail.mailers.smtp.host');
        $mail->Host       = config('mail.mailers.smtp.host');        // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;
        $mail->Username   = config('mail.mailers.smtp.username');
        $mail->Password   = config('mail.mailers.smtp.password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = config('mail.mailers.smtp.port');

        // Set the sender and recipient
        $mail->setFrom(config('mail.mailers.smtp.username'), 'Injazat Support');
        $mail->addAddress($to);
        // Content
        $mail->isHTML(true);                              // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        // Set encoding to UTF-8
        $mail->CharSet = 'UTF-8'; // Set character set to UTF-8
        // Send the email
        $mail->send();
      
        return 'Message has been sent';
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

}
