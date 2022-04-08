<?php

namespace App\MicrosoftGraph;

use DateTime;
use DateInterval;
use DateTimeZone;
use App\Models\User;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use App\Models\GenericTemplate;
use App\Models\NotificationLog;
use App\MicrosoftGraph\TokenCache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SendMail
{

    //public $toAddresses;
    public $toRecipients;       /* array of user id (Model: User) */
    public $ccRecipients;       /* array of user id (Model: User) */
    public $bccRecipients;      /* array of user id */
    public $sender_id;          /* user id (Model: User) */

    public $subject;            /* String */
    public $body;               /* String */
    public $bodyContentType;    /* text or html, default is 'html' */

    // Generic Template 
    public $template;           /* String - name of the template */
    public $bindvariables;      

    // Option 
    public $importance;         /* low, normal, and high. */
    public $saveToSentItems;    /* Boolean -- true or false */

    // Audit Log related
    public $saveToLog;          /* Boolean -- true or false */
    public $alertType;
    public $alertFormat;
    
    // Default email for Testing purpose (sent any email to this email)
    public $SendToTestAccount;  /* jpoon@extest.gov.bc.ca */

    // Private property
    private $generic_template;  
    private $default_email_prod_region;  // Azure - principle name 
    private $default_email_test_region;  // Azure - principle name 

    public function __construct() 
    {
        //$this->toAddresses = [];
        $this->toRecipients = [];
        $this->ccRecipients =[];
        $this->bccRecipients = [];
        $this->bindvariables = [];
        $this->bodyContentType = 'html';
        $this->saveToSentItems = true;
        $this->saveToLog = true;

        $this->SendToTestEmail = "travis.clark@gov.bc.ca";
        

        $this->alertType = 'N';  /* Notification */
        $this->alertFormat = 'E';   /* E = E-mail, A = In App */

        // Default Principle Name 
        $this->default_email_prod_region = "travis.clark@gov.bc.ca";
        $this->default_email_test_region = "HRadministror1@extest.gov.bc.ca";

    }

    public function sendMailWithoutGenericTemplate() 
    {

        if (!($this->sender_id) ) {
            $this->sender_id = Auth::id();
        }
        
        return $this->sendMailUsingApplicationToken();
    }


    public function sendMailWithGenericTemplate() 
    {

        $this->generic_template = GenericTemplate::where('template',$this->template)->first(); 

        // Bind variable
        $keys = $this->generic_template->binds->pluck('bind')->toArray();

        $this->subject = str_replace( $keys, $this->bindvariables, $this->generic_template->subject);
        $this->body = str_replace( $keys, $this->bindvariables, $this->generic_template->body);

        if ($this->generic_template->sender == 2) {
            // Override the sender based on the generic template definition
            $user = User::find($this->generic_template->sender_id);
            $this->sender_id = $user->id;
        } else {
            $this->sender_id = Auth::id();
        }
        return $this->sendMailUsingApplicationToken();
       
    }

    public function sendMailUsingApplicationToken() 
    {
        $accessToken = $this->getAccessToken();

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $a_toRecipients = User::whereIn('id', $this->toRecipients)->pluck('email');
        $a_ccRecipients = User::whereIn('id', $this->ccRecipients)->pluck('email');
        $a_bccRecipients = User::whereIn('id', $this->bccRecipients)->pluck('email');
        $g_toRecipients = [];
        $g_ccRecipients = [];
        $g_bccRecipients = [];
        
        if (App::environment(['production'])) {
            // TO
            foreach ($a_toRecipients as $emailAddress) {
                array_push($g_toRecipients, [
                    // Add the email address in the emailAddress property
                    'emailAddress' => [
                        'address' => $emailAddress,
                    ],
                ]);
            }
            // CC
            foreach ($a_ccRecipients as $emailAddress) {
                array_push($g_ccRecipients, [
                    // Add the email address in the emailAddress property
                    'emailAddress' => [
                        'address' => $emailAddress,
                    ],
                ]);
            }
            // Bcc
            foreach ($a_bccRecipients as $emailAddress) {
                array_push($g_bccRecipients, [
                    // Add the email address in the emailAddress property
                    'emailAddress' => [
                        'address' => $emailAddress,
                    ],
                ]);
            }
        } else {

            if (App::environment(['local'])) {
                $this->SendToTestEmail = "jpoon@extest.gov.bc.ca";
            }
            
            $sender = User::where('id', $this->sender_id)->first();

            /* Override sender and recipients */
            $this->body = "<h4>Note: The following message is the content was sent out from Performance application (Region: ". App::environment() .")</h4>".      
                          "<hr>".
                          "<p><b>From: </b>". $sender->email . "</p>".
                          "<p><b>To: </b>". implode('; ', $a_toRecipients->toArray() ). "</p>".
                          "<p><b>CC: </b>". implode('; ', $a_ccRecipients->toArray() ). "</p>".
                          "<p><b>Bcc: </b>". implode('; ', $a_bccRecipients->toArray() ). "</p>".
                          "<p><b>Subject: </b>" . $this->subject . "</p>".
                          "<p><b>Body : </b>" . $this->body . "</p>".
                          "<hr>";
            $this->subject = "Performance Application -- message sent out from (Region: ". App::environment() .") ";
            $g_toRecipients = [ 
                [
                    'emailAddress' => [
                    'address' => $this->SendToTestEmail,    /* default account for testing purpose */
                   ],
                ]
            ];
        }

        // Build Graph Message
        $newMessage = [
            "message" => [
                "subject" => $this->subject,
                "body" => [
                    "contentType" => $this->bodyContentType,
                    "content" => $this->body,
                ],
                'toRecipients' => $g_toRecipients,
                'ccRecipients' => $g_ccRecipients,
                'bccRecipients' => $g_bccRecipients
            ],
            "saveToSentItems" => $this->saveToSentItems ? "true" : "false",
        ];

        $sender = User::where('id', $this->sender_id)->first();
        
        // Local user without Sender ID
        if ($sender->azure_id) {
            $graph_azure_id = $sender->azure_id;
        } else {
            if (App::environment(['production'])) {
                $graph_azure_id = $this->default_email_prod_region;  // should be the generic one
            } else if (App::environment(['local'])) {
                $graph_azure_id = $this->default_email_test_region;
            } else {
                $graph_azure_id = $this->default_email_test_region;
            }
        }

        $sendMailUrl = '/users/' . $graph_azure_id . '/sendMail';
        //  User - API https://graph.microsoft.com/v1.0/users/{id}/sendMail
        $response = $graph->createRequest('POST', $sendMailUrl)
            ->addHeaders(['Prefer' => 'outlook.timezone="Pacific Standard Time"'])
            ->attachBody($newMessage)
            ->execute();

        if ($this->saveToLog) {

            // Insert Notification log
            $notification_log = NotificationLog::Create([  
                'recipients' => (App::environment(['local'])) ? $this->SendToTestEmail : null,
                'sender_id' => $this->sender_id,
                'subject' => $this->subject,
                'description' => $this->body,
                'alert_type' => $this->alertType,
                'alert_format' => $this->alertFormat,
                'template_id' => $this->generic_template ? $this->generic_template->id : null,
                'status' => $response->getStatus(), 
                'date_sent' => now(),
            ]);

            // Update Recipients
            foreach ($this->toRecipients as $recipient_id) {
                $notification_log->recipients()->updateOrCreate([
                    'recipient_id' => $recipient_id,
                ],['recipient_type' => 1]);
            }

            foreach ($this->ccRecipients as $recipient_id) {
                $notification_log->recipients()->updateOrCreate([
                    'recipient_id' => $recipient_id,
                ],['recipient_type' => 2]);
            }

            foreach ($this->bccRecipients as $recipient_id) {
                $notification_log->recipients()->updateOrCreate([
                    'recipient_id' => $recipient_id,
                ],['recipient_type' => 3]);
            }

       }   
        
        return $response;

    }

    protected function getAccessToken()
    {

        $client = new client;
        $endpoint = env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT');

        try {

            $response = $client->request('POST', $endpoint, [
                'form_params' => [
                    'client_id' => env('OAUTH_APP_ID'),
                    'client_secret' => env('OAUTH_APP_PASSWORD'),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ] 
            ]);

            $contents = $response->getBody()->getContents();
            $token_array = json_decode($contents, true);
            
            return $token_array['access_token'];

        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            echo $respose;
            echo $responseBodyAsString;
            // To Do -- notify administrator about the process failure
            exit(1);

        }

    }

}
