<?php
namespace App\Console\Commands;

use App\Models\Attachment;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;

class MarsEmailPiping extends Command {
    protected $signature = 'command:mars_piping_email';
    protected $description = 'Process emails from INBOX, INBOX.Junk, and INBOX.spam to create tickets, including CC information and log email details';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $client = Client::account('mars_email');

        if (!$client->connect()) {
            Log::error("Failed to connect to the email server.");
            return 1;
        }

        $folders = ['INBOX', 'INBOX.Junk', 'INBOX.spam']; // Specify the folders to process

        while (true) {
            foreach ($folders as $folderName) {
                try {
                    // Fetch the specific folder (INBOX, INBOX.Junk, INBOX.spam)
                    $folder = $client->getFolder($folderName);
                    
                    // Fetch all unseen messages in the folder
                    $messages = $folder->messages()->unseen()->get();

                    foreach ($messages as $message) {
                        $from = $message->getFrom();
                        if (empty($from)) {
                            Log::warning("No sender found for message ID: " . $message->getMessageId());
                            continue;
                        }

                        $fromData = $from[0];
                        if (!$fromData || !isset($fromData->mail)) {
                            Log::warning("From data is not valid for message ID: " . $message->getMessageId());
                            continue;
                        }

                        $user = $this->getOrCreateUser($fromData);

                        $subject = $message->getSubject();
                        $body = $message->getHTMLBody();
                        $plainBody = $message->getTextBody();
                        $messageId = $message->getMessageId()[0] ?? null;
                        $fromEmail = $fromData->mail;

                        // Log the email subject and sender details
                        Log::info("Processing email from: $fromEmail, Subject: $subject, Message ID: $messageId, Folder: $folderName");

                        if (!empty($messageId)) {
                            $cc = $message->getCc();
                            $assigned_to = null;

                            if (!empty($cc) && isset($cc[0]->mail)) {
                                $assignedUser = User::where('email', $cc[0]->mail)->first();
                                $assigned_to = $assignedUser;
                            }

                            // Create ticket and log its creation
                            $ticket = $this->createTicket($user, $subject, $body, $plainBody, $assigned_to, $fromEmail);
                            Log::info("Ticket created with ID: " . $ticket->id . " for email: $fromEmail in folder: $folderName");

                            // Process and log attachments
                            $this->processAttachments($message, $ticket, $user);

                            // Mark the email as seen after successful processing
                            $message->setFlag('SEEN');
                        }
                    }

                } catch (\Exception $e) {
                    Log::error("An error occurred while processing folder $folderName: " . $e->getMessage());
                    sleep(10); // Delay before retrying
                }
            }
        }

        return 0;
    }

    private function getOrCreateUser($fromData) {
        $user = User::where('email', $fromData->mail)->first();

        if (empty($user)) {
            $role = Role::where('slug', 'customer')->first();
            $name = $this->split_name($fromData->personal);
            $user = User::create([
                'email' => $fromData->mail,
                'password' => bcrypt('secret'),
                'role_id' => $role->id ?? 5,
                'first_name' => $name[0],
                'last_name' => $name[1]
            ]);

            // Log user creation
            Log::info("New user created: " . $user->email . " (" . $user->first_name . " " . $user->last_name . ")");
        }

        return $user;
    }

    private function createTicket($user, $subject, $body, $plainBody, $assigned_to, $from) {
        $combinedBody = $body . "\n\n" . strip_tags($plainBody);
        $customer = null;

        preg_match('/#(\d+)/', $subject, $matchesCustomer);
        if (!empty($matchesCustomer)) {
            $customer_no = str_replace('#', '', $matchesCustomer[0]);
            $subject = str_replace('#' . $customer_no, '', $subject);
            $organization = Organization::where('customer_no', $customer_no)->first();

            if (!empty($organization)) {
                $user = User::where('organization_id', $organization->id)->first();
                $customer = $user ? $user : null;
            }
        }

        if (empty($customer)) {
            $customer = $user;
        }

        $contact_id = null;
        if (!empty($from)) {
            $email_array = User::where('role_id', 5)->pluck('email')->toArray();
            if (in_array($from, $email_array)) {
                $contact_id = User::where('email', $from)->first()->id;
            }
        }

        $ticket = Ticket::create([
            'subject' => $subject,
            'details' => $combinedBody,
            'user_id' => $customer->id,
            'open' => now(),
            'response' => null,
            'due' => null,
            'assigned_to' => $assigned_to ? $assigned_to->id : 1,
            'contact_id' => $contact_id,
            'department_id' => $assigned_to ? $assigned_to->department_id : 2,
            'status_id' => 2,
            'priority_id' => 3,
            'type_id' => 6,
        ]);

        $ticket->uid = app('App\HelpDesk')->getUniqueUid($ticket->id);
        $ticket->save();

        return $ticket;
    }

    private function processAttachments($message, $ticket, $user) {
        $message->getAttachments()->each(function ($attachment) use ($message, $ticket, $user) {
            $origin_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $message->getMessageId() . '_' . $attachment->name);
            $directory = public_path('files/tickets/');

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $public_path = $directory . $origin_name;

            if (file_put_contents($public_path, $attachment->content) !== false) {
                $file_path = 'tickets/' . $origin_name;
                Attachment::create([
                    'ticket_id' => $ticket->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'path' => $file_path,
                    'user_id' => $user->id
                ]);

                // Log attachment processing
                Log::info("Attachment saved for ticket ID " . $ticket->id . ": " . $attachment->name);
            } else {
                Log::error("Failed to save attachment for ticket ID " . $ticket->id);
            }
        });
    }

    private function split_name($name) {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . preg_quote($last_name, '#') . '#', '', $name));
        return [$first_name, $last_name];
    }
}
