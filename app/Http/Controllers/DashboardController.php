<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserReportingTo;
use App\Models\SharedProfile;
use App\Models\DashboardMessage;
use App\Models\PreferredSupervisor;
use App\Models\PrimaryJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();
        
        if ($user->hasRole('Service Representative')) {
            session()->put('sr_user', true);
        } 

        $notifications = DashboardNotification::where('user_id', Auth::id())
                        ->where(function ($q)  {
                            $q->whereExists(function ($query) {
                                return $query->select(DB::raw(1))
                                        ->from('conversations')
                                        ->whereColumn('dashboard_notifications.related_id', 'conversations.id')
                                        ->whereNull('conversations.deleted_at')
                                        ->whereIn('dashboard_notifications.notification_type', ['CA', 'CS']);
                        })
                        ->orWhereExists(function ($query) {
                            return $query->select(DB::raw(1))
                                    ->from('goals')
                                    ->whereColumn('dashboard_notifications.related_id', 'goals.id')
                                    ->whereNull('goals.deleted_at')
                                    ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB', 'GK', 'GS']);
                        })
                        ->orWhereExists(function ($query) {
                            return $query->select(DB::raw(1))
                                    ->from('shared_profiles')
                                    ->whereColumn('dashboard_notifications.related_id', 'shared_profiles.id')
                                    ->whereIn('dashboard_notifications.notification_type', ['SP']);
                        })
                        ->orWhere('dashboard_notifications.notification_type', '');    
                })
                // ->orderby('status', 'asc')->orderby('created_at', 'desc');
                ->orderby('created_at', 'desc');

        if($request->ajax()) {

            return Datatables::of($notifications)
                ->addColumn('item_detail', function ($notification) {

                    $text = '<span ';
                    $text .= ($notification->status == 'R' ? '' : 'class="font-weight-bold"');                    
                    $text .= '>'.$notification->comment.'</span>';
                    $text .= $notification->status == 'R' ? '' : '<span class="badge badge-pill badge-primary ml-2">New</span>' ;
                    $text .= '<br/>';
                    // $text .= '<span>sjskaj |  sadasdad  |dkajsdkjkjadskjdkja</span>';

                    switch($notification->notification_type) {
                        case 'GC':
                        case 'GR':
                        case 'GS':
                        case 'GK':
                            $text .= 'Title: '.$notification->relatedGoal->title.' | Goal Type: '.$notification->relatedGoal->goalType->name.($notification->created_at?' | Date: '.$notification->created_at->format('M d, Y H:i A'):'');
                            break;
                        case 'GB':
                            $dt = new \DateTimeImmutable($notification->created_at, new \DateTimeZone('UTC'));
                            $text .= 'Title: '.$notification->relatedGoal->title. ' | Type: '.$notification->relatedGoal->mandatory_status_descr.($notification->created_at?' | Date: '.$dt->setTimezone((new \DateTime())->getTimezone())->format('M d, Y H:i A'):'');
                            break;
                        case 'CA':
                        case 'CS':
                                $text .= 'Title: '.($notification->conversation ? $notification->conversation->topic->name : '');
                                $text .= ($text?' | ':'').($notification->created_at?'Date: '.$notification->created_at->format('M d, Y H:i A'):'');
                            break;
                        case 'SP':
                                $text .= 'Elements: '.($notification->sharedProfile ? $notification->sharedProfile->shared_element_name : '');
                                $text .= ($text?' | ':'').($notification->created_at?'Date: '.$notification->created_at->format('M d, Y H:i A'):'');
                            break;
                        case '':
                                $text .= ($notification->created_at?'Date: '.$notification->created_at->format('M d, Y H:i A'):'');
                            break;
                    }

                    return '<table class="inner" style="border:none">'. 
                        '<tr>'.

                        // '<td class="'. ($notification->status == 'R' ? 'read' : 'new') .' pr-1">&nbsp;</td>'.
                        '<td class="pr-3" style="vertical-align:middle"><input type="checkbox" id="itemCheck'. 
                                $notification->id .'" name="itemCheck[]" value="'. 
                                $notification->id .'" class="dt-body-center"></td>'. 
                        '<td>'.$text.'</td>'.
                        '</tr>'.
                        '</table>';
                })
                ->addColumn('action', function ($notification) {

                    $text = "";
                    if ($notification->related_id) {
                        $link = 'location.href=\''. route("dashboardmessage.show", $notification->id) . '\'" ';
                        // if ($notification->notification_type == 'SP' ) {
                        //     $link = "open_shared_profile_modal()";  
                        // }

                        if  ( !(in_array($notification->notification_type, ['CA', 'CS'])) ) {
                            $text .= '<button onclick="'. $link . '"' .
                                    // 'data-toggle="tooltip" data-placement="bottom" title="Click to view the details." '.
                                    'data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Now hover out." '.
                                    'class="notification-modal btn btn-sm btn-primary mt-2" value="'. $notification->id .'">View</button>';
                        }
                    }
                    $text .= '<button class="btn btn-danger btn-sm ml-2 delete-dn mt-2"  data-id="'. $notification->id .
                                '" data-comment="'. $notification->comment . '"><i class="fas fa-trash-alt fa-lg" ></i></button>';

                    return $text;
                })
                ->rawColumns(['item_detail', 'action'])
                //->removeColumn('password')
                ->make(true);

        }

        $matched_dn_ids = $notifications->select(['id'])->pluck('id');
        $old_selected_dn_ids = isset($old['selected_dn_ids']) ? json_decode($old['selected_dn_ids']) : [];

        $greetings = "";

        /* This sets the $time variable to the current hour in the 24 hour clock format */
        $time = date("H");

        /* Set the $timezone variable to become the current timezone */
        $timezone = date("e");

        /* If the time is less than 1200 hours, show good morning */
        if ($time < "12") {
            $greetings = "Good Morning";
        } else

        /* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
        if ($time >= "12" && $time < "17") {
            $greetings = "Good Afternoon";
        } else

        /* Should the time be between or equal to 1700 and 1900 hours, show good evening */
        if ($time >= "17" && $time < "19") {
            $greetings = "Good Evening";
        } else

        if ($time >= "19") {
            $greetings = "Hello";
        }

        // $tab = (Route::current()->getName() == 'dashboard.notifications') ? 'notifications' : 'todo';
        $tab = (Route::current()->getName() == 'dashboard.notifications') ? 'notifications' : 'notifications';
        // $notifications = DashboardNotification::where('user_id', Auth::id())->get();
        // $notifications = DashboardNotification::where('user_id', Auth::id())
        //         ->where(function ($q)  {
        //                 $q->whereExists(function ($query) {
        //                     return $query->select(DB::raw(1))
        //                             ->from('conversations')
        //                             ->whereColumn('dashboard_notifications.related_id', 'conversations.id')
        //                             ->whereNull('conversations.deleted_at')
        //                             ->whereIn('dashboard_notifications.notification_type', ['CA', 'CS']);
        //                 })
        //                 ->orWhereExists(function ($query) {
        //                     return $query->select(DB::raw(1))
        //                             ->from('goals')
        //                             ->whereColumn('dashboard_notifications.related_id', 'goals.id')
        //                             ->whereNull('goals.deleted_at')
        //                             ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB']);
        //                 })
        //                 ->orWhereExists(function ($query) {
        //                     return $query->select(DB::raw(1))
        //                             ->from('shared_profiles')
        //                             ->whereColumn('dashboard_notifications.related_id', 'shared_profiles.id')
        //                             ->whereIn('dashboard_notifications.notification_type', ['SP']);
        //                 })
        //                 ->orWhere('dashboard_notifications.notification_type', '')
        //                 ;    
        //         })
        //     ->orderby('status', 'asc')->orderby('created_at', 'desc')
        //     ->paginate(8);
        // $notifications_unread = DashboardNotification::where('user_id', Auth::id())->where('status', null)
        //                         ->where(function ($q)  {
        //                             $q->whereExists(function ($query) {
        //                                 return $query->select(DB::raw(1))
        //                                         ->from('conversations')
        //                                         ->whereColumn('dashboard_notifications.related_id', 'conversations.id')
        //                                         ->whereNull('conversations.deleted_at')
        //                                         ->whereIn('dashboard_notifications.notification_type', ['CA', 'CS']);
        //                             })
        //                             ->orWhereExists(function ($query) {
        //                                 return $query->select(DB::raw(1))
        //                                         ->from('goals')
        //                                         ->whereColumn('dashboard_notifications.related_id', 'goals.id')
        //                                         ->whereNull('goals.deleted_at')
        //                                         ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB']);
        //                             })
        //                             ->orWhereExists(function ($query) {
        //                                 return $query->select(DB::raw(1))
        //                                         ->from('shared_profiles')
        //                                         ->whereColumn('dashboard_notifications.related_id', 'shared_profiles.id')
        //                                         ->whereIn('dashboard_notifications.notification_type', ['SP']);
        //                             })
        //                             ->orWhere('dashboard_notifications.notification_type', '');    
        //                         });
        $supervisorTooltip = 'If your current supervisor in the Performance Development Platform is incorrect, please have your supervisor submit a service request through AskMyHR and choose the category: <span class="text-primary">My Team or Organization > HR Software Systems Support > Position / Reporting Updates</span>';        
        
        $sharedList = SharedProfile::where('shared_id', Auth::id())
                    ->join('users','users.id','shared_profiles.shared_with')
                    ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
                    ->whereNull('employee_demo.date_deleted')
                    ->with('sharedWithUser')->get();
        
        
        $profilesharedTooltip = 'If this information is incorrect, please discuss with your supervisor first and escalate to your organization\'s Strategic Human Resources shop if you are unable to resolve.';
        
        $message= '';
        $messages = $this->getDashboardMessage();
        
        if (count($messages) > 0) {
            foreach ($messages as $message) {}
        }

        $open_modal = (session('open')) ? true : false;

        $supervisorList = Auth::user()->supervisorListPrimaryJob();
        $supervisorListCount = Auth::user()->supervisorListPrimaryJobCount();
        $preferredSupervisor = Auth::user()->preferredSupervisor();
        $primaryJob = Auth::user()->primaryJob();
        $jobList = Auth::user()->jobList();
        $jobTooltip = 'This option only appears for employees that have more than one active position with BC Public Service. Please select the position that you would like to link to your PDP profile.';        

        return view('dashboard.index', compact('greetings', 'tab', 'supervisorTooltip', 'sharedList', 'profilesharedTooltip', 
                    // 'notifications', 'notifications_unread', 
                    'message', 'matched_dn_ids','old_selected_dn_ids', 'open_modal', 'supervisorList', 'supervisorListCount', 'preferredSupervisor', 'primaryJob', 'jobList', 'jobTooltip'));
    }

    public function show(Request $request, $id) {

        $notification = DashboardNotification::where('id', $id)->first();

        // TODO: update
        if ($notification) {
            $notification->status = 'R';
            $notification->save();

            $url = $notification->url;

            if ($notification->notification_type == 'SP' ) {
                // $url .= '?open=1';
                return redirect( $url )->with('open', '1');
            }

            return redirect( $url )->with('open_modal_id', $notification->related_id );
        }

        return redirect()->back();

    }

    public function getDashboardMessage() {
        $dbm = DashboardMessage::select('message')->get();
        return $dbm;
    }

    public function destroy($id)
    {
        $notification = DashboardNotification::where('id',$id)->delete();
        return redirect()->back();
    }

    public function destroyall(Request $request)
    {
        
        $ids = $request->ids ? json_decode($request->ids) : [];

        DashboardNotification::wherein('id', $ids)->delete();
        // return redirect()->back();
        return response()->noContent();

        // return redirect()->to('/route');
        // header("Refresh:0");
        // return back();
        // window.location.reload();
        //return route::get('dashboard.notifications');
        //return view('dashboard.partials.notifications');
        // return Redirect()->route('dashboard.notifications');
        // return response()->json(['success'=>"Notification(s) deleted successfully."]);
    }

    public function updatestatus(Request $request)
    {

        $ids = $request->ids ? json_decode($request->ids) : [];

// return $ids;        
        // var check = confirm($ids);
        // dd("RESULTS: " . $ids);

        DashboardNotification::wherein('id', $ids)->update(['status' => 'R']);
        // return route::get('dashboard.notifications');
        // return redirect()->back();
        // $notification = DashboardNotification::where('id', 4)->update(['status' => 'S']);
        // window.location.reload();
        // return redirect()->to('/route');
        // header("Refresh:0");
        // return back();
        return response()->json(['success'=>"Notification(s) updated successfully."]);
    }

    public function resetstatus(Request $request)
    {

        $ids = $request->ids ? json_decode($request->ids) : [];
        DashboardNotification::wherein('id',$ids)->update(['status' => null]);
        // DashboardNotification::where('id',5)->update(['status' => 'D']);
        // header("Refresh:0");
        // return back();
        // window.location.reload();
        // return redirect()->to('/route');
        // return redirect()->back();
        return response()->json(['success'=>"Notification(s) updated successfully."]);
    }
    
    public function badgeCount(Request $request) {

        if($request->ajax()) {
            $badge_count = DashboardNotification::where('user_id', Auth::id())
                            ->where(function ($q)  {
                                $q->whereExists(function ($query) {
                                    return $query->select(DB::raw(1))
                                            ->from('conversations')
                                            ->whereColumn('dashboard_notifications.related_id', 'conversations.id')
                                            ->whereNull('conversations.deleted_at')
                                            ->whereIn('dashboard_notifications.notification_type', ['CA', 'CS']);
                            })
                            ->orWhereExists(function ($query) {
                                return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->whereColumn('dashboard_notifications.related_id', 'goals.id')
                                        ->whereNull('goals.deleted_at')
                                        ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB', 'GK', 'GS']);
                            })
                            ->orWhereExists(function ($query) {
                                return $query->select(DB::raw(1))
                                        ->from('shared_profiles')
                                        ->whereColumn('dashboard_notifications.related_id', 'shared_profiles.id')
                                        ->whereIn('dashboard_notifications.notification_type', ['SP']);
                            })
                            ->orWhere('dashboard_notifications.notification_type', '');
                        })
                        ->whereNull('status')
                        ->count();
                            
            return response()->json(['count'=> $badge_count]);
        }

    }

    
    public function revertIdentity(Request $request) {
         $oldUserId = $request->session()->get('existing_user_id');
         Auth::loginUsingId($oldUserId);
         $request->session()->forget('existing_user_id');
         $request->session()->forget('user_is_switched');
         $request->session()->forget('sr_user');
         $request->session()->forget('SR_ALLOWED');
         //return redirect()->back();
         return redirect()->to('/dashboard');

    }

    public function updateSupervisor(Request $request) {
        PreferredSupervisor::updateOrCreate([
            'employee_id' => Auth::user()->employee_id,
            'position_nbr' => Auth::user()->employee_demo->position_number
        ], [
            'supv_empl_id' => $request->id
        ]);
        $supvUser = User::join('employee_demo AS d', 'users.employee_id', 'd.employee_id')
        ->select('users.id')
        ->whereRaw("users.employee_id = '".$request->id."'")
        ->whereNull('d.date_deleted')
        ->orderBy('users.id')
        ->first();
        if($supvUser) {
            User::where('id', '=', Auth::user()->id)
            ->update([
                'reporting_to' => $supvUser->id
            ]);
            UserReportingTo::updateOrCreate([
                'user_id' => Auth::user()->id
            ], [ 
                'reporting_to_id' => $supvUser->id 
            ]);
        }
        return redirect()->back();
    }
    
    // public function updateJob(Request $request) {
    //     PrimaryJob::updateOrCreate([
    //         'employee_id' => Auth::user()->employee_id,
    //     ], [
    //         'empl_record' => $request->id,
    //         'updated_by' => $request->session()->get('existing_user_id') ? $request->session()->get('existing_user_id') : Auth::id(),
    //     ]);
    //     return redirect()->back();
    // }
    
    public function updateJob(Request $request) {
        User::updateOrCreate([
            'employee_id' => Auth::user()->employee_id,
        ], [
            'empl_record' => $request->id,
            'updated_by' => $request->session()->get('existing_user_id') ? $request->session()->get('existing_user_id') : Auth::id(),
        ]);
        return redirect()->back();
    }
    
    public function checkExpiration(Request $request) {
        $user = Auth::user();
        $sessionExpired = true;
        if($user){
            $sessionExpired = false;
        }

        //$sessionExpired = $request->session()->has('last_activity');
        return response()->json(['sessionExpired' => $sessionExpired]);
    }
}
