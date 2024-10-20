<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\WhatsappApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;

class PippingEmail extends Command
{
    protected $signature = 'command:piping_email';
    protected $description = 'Process emails from inbox to create tickets, including CC information';

 

    private $whatsappApiService;

    public function __construct(WhatsappApiService $whatsappApiService)
    {
        parent::__construct();

        $this->whatsappApiService = $whatsappApiService;
    }
    public function handle()
    {
        $client = Client::account('default');

        if (!$client->connect()) {
            Log::error("Failed to connect to the email server.");
            return 1;
        }

        while (true) {
            try {
                $inbox = $client->getFolder('INBOX');
                $messages = $inbox->messages()->unseen()->get();

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
                    $plainBody = $message->getTextBody(); // Get plain text body
                    $messageId = $message->getMessageId()[0] ?? null;
                    $from = $fromData->mail;
                    if (!empty($messageId)) {
                        $cc = $message->getCc();
                        $assigned_to = null;

                        if (!empty($cc) && isset($cc[0]->mail)) {
                            $assignedUser = User::where('email', $cc[0]->mail)->first();
                            $assigned_to = $assignedUser;
                        }
                        if (!empty($user)) {
                            $ticket = $this->createTicket($user, $subject, $body, $plainBody, $assigned_to, $from);
                            $this->processAttachments($message, $ticket, $user);
                            $message->setFlag('SEEN');
                            if (!empty($ticket->user)) {
                                $message = 'أهلا وسهلا, سوف يتم فتح تذكرة وإبلاغك بها';
                                if (!empty($ticket->user->phone)) {
                                    $response = $this->whatsappApiService->sendTestMsg(
                                        '888',
                                        $ticket->user->phone,
                                        $message

                                    );
                                    if (!empty($ticket->user->email)) {
                                        app(abstract: 'App\HelpDesk')->sendEmail($ticket->user->email, "Reply: $ticket->subject",     view('mail.test', compact('message'))->render() );
                                    }
                                }
                            }
                        }
                    }
                }


            } catch (\Exception $e) {
                Log::error("An error occurred: " . $e->getMessage());
                sleep(10);
            }
        }

        return 0;
    }

    private function getOrCreateUser($fromData)
    {
        $user = User::where('email', $fromData->mail)->first();



        return $user;
    }

    private function createTicket($user, $subject, $body, $plainBody, $assigned_to, $from)
    {
        $combinedBody = $body . "\n\n" . strip_tags($plainBody); // Use strip_tags to remove HTML from plain body
        $customer = null;

        // Extract customer number from subject if it exists
        preg_match('/#(\d+)/', $subject, $matchesCustomer);
        if (!empty($matchesCustomer)) {
            $customer_no = str_replace('#', '', $matchesCustomer[0]);
            $subject = str_replace('#' . $customer_no, '', $subject);
            $og = Organization::where('customer_no', $customer_no)->first();

            if (!empty($og)) {
                $user = User::where('organization_id', $og->id)->first();
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

        // Check if a similar ticket already exists to avoid duplicates
        $existingTicket = Ticket::where('subject', $subject)
            ->where('user_id', $customer->id)
            ->where('status_id', 2) // Assuming 2 represents "open" status, adjust this based on your system
            ->first();

        if ($existingTicket) {
            Log::info("A ticket with subject '$subject' already exists for user {$customer->id}. No new ticket created.");
            return $existingTicket; // Return the existing ticket to avoid duplicate creation
        }

        // Create new ticket if no duplicate is found
        $ticket = Ticket::factory()->create([
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

    private function processAttachments($message, $ticket, $user)
    {
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
            } else {
                Log::error("Failed to save attachment for ticket ID " . $ticket->id);
            }
        });
    }

    private function split_name($name)
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . preg_quote($last_name, '#') . '#', '', $name));
        return [$first_name, $last_name];
    }

}