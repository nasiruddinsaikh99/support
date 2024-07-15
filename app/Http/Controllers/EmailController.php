<?php

namespace App\Http\Controllers;

use App\Mail\SupportEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Search\Flag\Unseen;
use Ddeboer\Imap\Search\Flag\Recent;
use Ddeboer\Imap\Search\Date\Since;
use Ddeboer\Imap\Search\Email\From;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Subject;
use Ddeboer\Imap\SearchExpression;
use DateTime;
use App\Models\Customer;
use App\Models\Ticket;

class EmailController extends Controller
{
    Public function sendEmail(){
        $toEmail = "developervikashkr@gmail.com";
        $message = "We are thrilled to have you on board. Our team is here to assist you with any questions or support you may need.";
        $subject = "Welcome to SupportCRM!";

        $request = Mail::to($toEmail)->send(new SupportEmail($message, $subject));

        dd($request);
    }

    public function retrieveEmail_old()
    {
        // IMAP server connection parameters
        $server = new Server('mail.privateemail.com', 993, 'ssl');//new Server('imap.gmail.com', 993, 'ssl');
        $username = 'hello@supportcrm.io'; //'vikash.businesslabs@gmail.com';
        $password = 'Vikash@20r' ;//'gkkrzwkbqlhlmbhu';

        // Establish IMAP connection
        $connection = $server->authenticate($username, $password);

        // Get the mailbox
        $mailbox = $connection->getMailbox('INBOX');

        // Define the current date
        $currentDate = new DateTime();
        $currentDate->setTime(0, 0); // Set to the start of the day

        // Construct search expression with conditions
        $search = new SearchExpression();
        //$search->addCondition(new Since($currentDate));
        $search->addCondition(new From('vikkuvikashssm2000@gmail.com')); // support@businesslabs.org
        //$search->addCondition(new To('email.to@address.com'));     // Replace with actual email address
        //$search->addCondition(new Subject('Remind'));  
         //$search->addCondition(new Unseen());  
        // $search->addCondition(new Recent());  
        // Fetch emails based on search criteria
        $emails = $mailbox->getMessages();//$search
        $emailData = [];
        // Check if any emails are found
        if (count($emails) == 0) {
            echo "No emails found for today.<br>";
        } else {
            // Process each email
            foreach ($emails as $email) {
                // Extract email details
                $subject = $email->getSubject();

                // Extract sender details
                $from = $email->getFrom();
                $fromAddress = $from->getAddress();
                $fromName = $from->getName();

                // Extract recipient details
                $to = $email->getTo();
                $toAddresses = [];
                foreach ($to as $recipient) {
                    $toAddresses[] = $recipient->getAddress();
                }

                // Extract other email details
                $date = $email->getDate();
                $isAnswered = $email->isAnswered();
                $isDeleted = $email->isDeleted();
                $isDraft = $email->isDraft();
                $isSeen = $email->isSeen();
                $body = $email->getBodyHtml();

                // Process attachments if needed
                $attachments = [];
                if ($email->hasAttachments()) {
                    foreach ($email->getAttachments() as $attachment) {
                        $attachments[] = [
                            'filename' => $attachment->getFilename(),
                            'size' => $attachment->getSize(),
                            'content' => $attachment->getDecodedContent(),
                        ];
                    }
                }

                // Output email details
                // echo "Subject: " . $subject . "<br>";
                // echo "From: " . ($fromName ? $fromName . " <" . $fromAddress . ">" : $fromAddress) . "<br>";
                // echo "To: " . implode(', ', $toAddresses) . "<br>";
                // echo "Date: " . $date->format('Y-m-d H:i:s') . "<br>";
                // echo "Answered: " . ($isAnswered ? 'Yes' : 'No') . "<br>";
                // echo "Deleted: " . ($isDeleted ? 'Yes' : 'No') . "<br>";
                // echo "Draft: " . ($isDraft ? 'Yes' : 'No') . "<br>";
                // echo "Seen: " . ($isSeen ? 'Yes' : 'No') . "<br>";
                // echo "Body: " . $body . "<br>";

                // // Output attachments
                // foreach ($attachments as $attachment) {
                //     echo "Attachment: " . $attachment['filename'] . " (size: " . $attachment['size'] . " bytes)<br>";
                // }

                // echo "====================<br>";
                $emailDetails = [
                    'subject' => $email->getSubject(),
                    'from' => [
                        'address' => $email->getFrom()->getAddress(),
                        'name' => $email->getFrom()->getName()
                    ],
                    'to' => array_map(function ($recipient) {
                        return $recipient->getAddress();
                    }, iterator_to_array($email->getTo())),
                    'date' => $email->getDate()->format('Y-m-d H:i:s'),
                    'isAnswered' => $email->isAnswered(),
                    'isDeleted' => $email->isDeleted(),
                    'isDraft' => $email->isDraft(),
                    'isSeen' => $email->isSeen(),
                    'body' => $email->getBodyHtml(),
                    'attachments' => array_map(function ($attachment) {
                        return [
                            'filename' => $attachment->getFilename(),
                            'size' => $attachment->getSize()
                        ];
                    }, iterator_to_array($email->getAttachments()))
                ];
    
                $emailData[] = $emailDetails;
            }
        }

        // Disconnect from the mailbox
        $connection->expunge();
        return response()->json($emailData);
    }

    public function retrieveEmail()
    {
        // IMAP server connection parameters
        $server = new Server('mail.privateemail.com', 993, 'ssl');//new Server('imap.gmail.com', 993, 'ssl');
        $username = 'hello@supportcrm.io'; //'vikash.businesslabs@gmail.com';
        $password = 'Vikash@20r' ;//'gkkrzwkbqlhlmbhu';

        // Establish IMAP connection
        $connection = $server->authenticate($username, $password);

        // Get the mailbox
        $mailbox = $connection->getMailbox('INBOX');
        $emails = $mailbox->getMessages();

        foreach ($emails as $email) {
            // Extract email details
            $subject = $email->getSubject();
            $description = $email->getBodyHtml();
            $fromAddress = $email->getFrom()->getAddress();
            $fromName = $email->getFrom()->getName();
            $tags = ['Support','meeting']; // Adjust this based on your tagging logic
            $ticket_id = random_int(1000, 9999);
    
            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['email' => $fromAddress],
                ['name' => $fromName]
            );
    
            // Create ticket
            $ticket = new Ticket();
            $ticket->ticket_id = $ticket_id; // Generate a unique ID
            $ticket->subject = $subject;
            $ticket->description = $description;
            $ticket->status = 'Open'; // Default status
            $ticket->priority = 'High'; // Default priority
            $ticket->created_at = $email->getDate();
            $ticket->updated_at = now();
            $ticket->customer()->associate($customer);
            $ticket->tags = json_encode($tags);
            $ticket->save();
        }
    
        //return response()->json(['message' => 'Emails retrieved and tickets created.']);

        // Fetch tickets from the database
        $tickets = Ticket::with('customer')->orderBy('created_at', 'desc')->get();

        // Format the response data
        $ticketData = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->ticket_id,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
                'customer' => [
                    'name' => $ticket->customer->name,
                    'email' => $ticket->customer->email,
                ],
                'tags' => json_decode($ticket->tags),
                // Add attachments if needed
            ];
        });


        // Disconnect from the mailbox
        $connection->expunge();

        // Return JSON response with ticket data
        return response()->json(['tickets' => $ticketData]);
    }
}
