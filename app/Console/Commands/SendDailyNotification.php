<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DateTime;
use DateTimeZone;
use Microsoft\Graph\Graph;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use GuzzleHttp\Client;


class SendDailyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily email notification';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accessToken = $this->getAccessToken();

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $attendeeAddresses = ['james.poon@gov.bc.ca', 'james.poon@telus.com', 'myphd2@gmail.com', 'employee11@extest.gov.bc.ca', 'employee12@extest.gov.bc.ca'];
        $subject = 'ePerformance Application - schedule daily notification testing';
        $body = 'Test message -- daily notification send out from server for testing purpose, please ignore.';

        $attendees = [];
        foreach ($attendeeAddresses as $attendeeAddress) {
            array_push($attendees, [
                // Add the email address in the emailAddress property
                'emailAddress' => [
                    'address' => $attendeeAddress,
                ],
            ]);
        }

        // Build message
        $newMessage = [
            "message" => [
                "subject" => $subject,
                "body" => [
                    "contentType" => "Text",
                    "content" => $body,
                ],
                'toRecipients' => $attendees
            ],
            "saveToSentItems" => "true",
        ];

        //  User - API https://graph.microsoft.com/v1.0/me/sendMail
        $sendMailUrl = '/users/HRadministror1@extest.gov.bc.ca/sendMail';
        $response = $graph->createRequest('POST', $sendMailUrl)
            ->addHeaders(['Prefer' => 'outlook.timezone="Pacific Standard Time"'])
            ->attachBody($newMessage)
            ->execute();

        //return $response;
        $this->info('Successfully sent daily notification to eligible people.');


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
