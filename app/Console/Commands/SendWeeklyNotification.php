<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use App\Models\Conversation;
use Illuminate\Console\Command;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use App\Models\User;

class SendWeeklyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Weekly email notification';
    
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


        $this->notifyHRAdminOnOverDueConversation();

    }


    protected function notifyHRAdminOnOverDueConversation() {

        $users = User::whereIn('guid', function($q) {
            $q->select('guid')->from('employee_demo')
                ->whereIn('organization',['BC Public Service Agency'])
                ->whereIn('employee_status',['A','L','S','P']);
        })
        ->orderBy('name')->get();

        // array of the overdue users
        $output_arr = [];

        foreach ($users as $key => $user) {

            $text = '';
            $nextDueDate = $user->joining_date ? $user->joining_date->addMonths(4) : '';
            $diff = 0;
        
            if ($user->conversations->whereNotNull('sign_off_time')->count() == 0 ) {

                $nextDueDate = $user->joining_date ? $user->joining_date->addMonths(4) : '';
                $text = "You must complete your first performance conversation by " . $nextDueDate->format('d-M-y');
                $diff = Carbon::now()->diffInDays($nextDueDate, false);

            } else {
                foreach ($user->conversations as $conversation) {
                    $lastConv = conversation::getLastConv([], $user);

                    if ($lastConv) {
                        if ($lastConv->sign_off_time->addMonths(4)->lt(Carbon::now())) {
                            $text = "You are required to complete a performance conversation every 4 months at minimum. You are overdue. Please complete a conversation as soon as possible.";
                        } else {
                            $nextDueDate = $lastConv->sign_off_time->addMonths(4);
                            $diff = Carbon::now()->diffInMonths($lastConv->sign_off_time->addMonths(4), false);
                            $text = "Your next performance conversation is due by ". $lastConv->sign_off_time->addMonths(4)->format('d-M-y');
                        }
                    } else {
                        $nextDueDate = $user->joining_date ? $user->joining_date->addMonths(4) : '';
                        $text = "You must complete your first performance conversation by " . $nextDueDate->format('d-M-y');
                        $diff = Carbon::now()->diffInDays($nextDueDate, false);
                    }
                
                }

            }

            if ($diff <= -4) {

                array_push($output_arr, [ 
                        'user' =>  $user->name,
                        'email' => $user->email,
                        'overdue' => $nextDueDate->diffForHumans( Carbon::now() ), 
                        'Text' => $text,
                ]);
                
                $this->info( $key+1 . ' - ' . $user->email . ' - ' . $diff . ' - ' . $text   );        
            }

        }

        // create a html table from a array 
        $listing = $this->array2Html($output_arr);

        // sender 
        $to = User::where('email', 'employee11@extest.gov.bc.ca')->first(); 

        // Method 1: Real-Time
        $sendMail = new \App\MicrosoftGraph\SendMail();
        $sendMail->toRecipients = [ $to->id ];
        $sendMail->alertFormat = 'E';
        $sendMail->useQueue = false;
        $sendMail->template = 'WEEKLY_OVERDUE_SUMMARY';
        array_push($sendMail->bindvariables, $listing);
        // $success = $sendMail->sendMailWithGenericTemplate();
        if ($success) {
            $this->info( 'Email was successfully sent.');
        }

    }

    function array2Html($array, $table = true)
    {
        $out = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!isset($tableHeader)) {
                    $tableHeader =
                        '<th>' .
                        implode('</th><th>', array_keys($value)) .
                        '</th>';
                }
                array_keys($value);
                $out .= '<tr style="border: 1px solid black; border-collapse: collapse;">';
                $out .= $this->array2Html($value, false);
                $out .= '</tr>';
            } else {
                $out .= "<td>".htmlspecialchars($value)."</td>";
            }
        }

        if ($table) {
            return '<table style="border: 1px solid black; border-collapse: collapse;">' . $tableHeader . $out . '</table>';
        } else {
            return $out;
        }
    }


}
