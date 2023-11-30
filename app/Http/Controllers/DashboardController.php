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
        Log::info('user id:' . Auth::id());
        
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
                                    ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GK', 'GS']);
                        })
                        ->orWhereExists(function ($query) {
                            return $query->select(DB::raw(1))
                                    ->from('goals')
                                    ->whereColumn('dashboard_notifications.related_id', 'goals.id')
                                    ->whereNull('goals.deleted_at')
                                    ->where('dashboard_notifications.notification_type', 'GB')
                                    ->whereRaw("(
                                        EXISTS (SELECT 1 FROM goals_shared_with gsw WHERE gsw.goal_id = dashboard_notifications.related_id AND gsw.user_id = dashboard_notifications.user_id)
                                        OR EXISTS (SELECT 1 FROM goal_bank_orgs gbo, employee_demo ed, users u WHERE gbo.goal_id = dashboard_notifications.related_id AND gbo.version = 2 AND gbo.inherited = 0 AND gbo.orgid = ed.orgid AND ed.employee_id = u.employee_id AND u.id = dashboard_notifications.user_id)
                                        OR EXISTS (SELECT 1 FROM goal_bank_orgs gbo, employee_demo_tree edt WHERE gbo.goal_id = dashboard_notifications.related_id AND gbo.version = 2 AND gbo.inherited = 1 AND gbo.orgid = edt.id 
                                            AND (EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud0 WHERE ud0.user_id = dashboard_notifications.user_id AND edt.level = 0 AND ud0.organization_key = edt.organization_key)
                                                OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud1 WHERE ud1.user_id = dashboard_notifications.user_id AND edt.level = 1 AND ud1.organization_key = edt.organization_key AND ud1.level1_key = edt.level1_key)
                                                OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud2 WHERE ud2.user_id = dashboard_notifications.user_id AND edt.level = 2 AND ud2.organization_key = edt.organization_key AND ud2.level1_key = edt.level1_key AND ud2.level2_key = edt.level2_key)
                                                OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud3 WHERE ud3.user_id = dashboard_notifications.user_id AND edt.level = 3 AND ud3.organization_key = edt.organization_key AND ud3.level1_key = edt.level1_key AND ud3.level2_key = edt.level2_key AND ud3.level3_key = edt.level3_key)
                                                OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud4 WHERE ud4.user_id = dashboard_notifications.user_id AND edt.level = 4 AND ud4.organization_key = edt.organization_key AND ud4.level1_key = edt.level1_key AND ud4.level2_key = edt.level2_key AND ud4.level3_key = edt.level3_key AND ud4.level4_key = edt.level4_key)
                                            )
                                        )
                                    )");
                        })
                        ->orWhereExists(function ($query) {
                            return $query->select(DB::raw(1))
                                    ->from('shared_profiles')
                                    ->whereColumn('dashboard_notifications.related_id', 'shared_profiles.id')
                                    ->whereIn('dashboard_notifications.notification_type', ['SP']);
                        })
                        ->orWhere('dashboard_notifications.notification_type', '');    
                })
                ->orderby('created_at', 'desc');

        if($request->ajax()) {

            return Datatables::of($notifications)
                ->addColumn('item_detail', function ($notification) {

                    $text = '<span ';
                    $text .= ($notification->status == 'R' ? '' : 'class="font-weight-bold"');                    
                    $text .= '>'.$notification->comment.'</span>';
                    $text .= $notification->status == 'R' ? '' : '<span class="badge badge-pill badge-primary ml-2">New</span>' ;
                    $text .= '<br/>';

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

                        if  ( !(in_array($notification->notification_type, ['CA', 'CS'])) ) {
                            $text .= '<button onclick="'. $link . '"' .
                                    'data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Now hover out." '.
                                    'class="notification-modal btn btn-sm btn-primary mt-2" value="'. $notification->id .'">View</button>';
                        }
                    }
                    $text .= '<button class="btn btn-danger btn-sm ml-2 delete-dn mt-2"  data-id="'. $notification->id .
                                '" data-comment="'. $notification->comment . '"><i class="fas fa-trash-alt fa-lg" ></i></button>';

                    return $text;
                })
                ->rawColumns(['item_detail', 'action'])
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

        $tab = (Route::current()->getName() == 'dashboard.notifications') ? 'notifications' : 'notifications';
        $supervisorTooltip = 'If you have a dropdown menu below it means that there are multiple people sharing your supervisor\'s position #. Please select the correct supervisor from the list. This may take up to 5 seconds to process.
            <br><br>
            If your current supervisor is not visible below, please have your actual supervisor submit a service request through AskMyHR and choose the category: <span class="text-primary">My Team or Organization > HR Software Systems Support > Position / Reporting Updates</span>.
        ';        
        
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
        $jobTooltip = 'This option only appears for employees that have more than one active position with the BC Public Service. Please select a position to connect with the appropriate supervisor.
            <br><br>
            This may take up to 5 seconds to process.
        ';        

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
        return response()->noContent();
    }

    public function updatestatus(Request $request)
    {

        $ids = $request->ids ? json_decode($request->ids) : [];

        DashboardNotification::wherein('id', $ids)->update(['status' => 'R']);
        return response()->json(['success'=>"Notification(s) updated successfully."]);
    }

    public function resetstatus(Request $request)
    {

        $ids = $request->ids ? json_decode($request->ids) : [];
        DashboardNotification::wherein('id',$ids)->update(['status' => null]);
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
                                        ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GK', 'GS']);
                            })
                            ->orWhereExists(function ($query) {
                                return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->whereColumn('dashboard_notifications.related_id', 'goals.id')
                                        ->whereNull('goals.deleted_at')
                                        ->where('dashboard_notifications.notification_type', 'GB')
                                        ->whereRaw("(
                                            EXISTS (SELECT 1 FROM goals_shared_with gsw WHERE gsw.goal_id = dashboard_notifications.related_id AND gsw.user_id = dashboard_notifications.user_id)
                                            OR EXISTS (SELECT 1 FROM goal_bank_orgs gbo, employee_demo ed, users u WHERE gbo.goal_id = dashboard_notifications.related_id AND gbo.version = 2 AND gbo.inherited = 0 AND gbo.orgid = ed.orgid AND ed.employee_id = u.employee_id AND u.id = dashboard_notifications.user_id)
                                            OR EXISTS (SELECT 1 FROM goal_bank_orgs gbo, employee_demo_tree edt WHERE gbo.goal_id = dashboard_notifications.related_id AND gbo.version = 2 AND gbo.inherited = 1 AND gbo.orgid = edt.id 
                                                AND (EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud0 WHERE ud0.user_id = dashboard_notifications.user_id AND edt.level = 0 AND ud0.organization_key = edt.organization_key)
                                                    OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud1 WHERE ud1.user_id = dashboard_notifications.user_id AND edt.level = 1 AND ud1.organization_key = edt.organization_key AND ud1.level1_key = edt.level1_key)
                                                    OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud2 WHERE ud2.user_id = dashboard_notifications.user_id AND edt.level = 2 AND ud2.organization_key = edt.organization_key AND ud2.level1_key = edt.level1_key AND ud2.level2_key = edt.level2_key)
                                                    OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud3 WHERE ud3.user_id = dashboard_notifications.user_id AND edt.level = 3 AND ud3.organization_key = edt.organization_key AND ud3.level1_key = edt.level1_key AND ud3.level2_key = edt.level2_key AND ud3.level3_key = edt.level3_key)
                                                    OR EXISTS (SELECT DISTINCT 1 FROM user_demo_jr_view ud4 WHERE ud4.user_id = dashboard_notifications.user_id AND edt.level = 4 AND ud4.organization_key = edt.organization_key AND ud4.level1_key = edt.level1_key AND ud4.level2_key = edt.level2_key AND ud4.level3_key = edt.level3_key AND ud4.level4_key = edt.level4_key)
                                                )
                                            )
                                        )");
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
         return redirect()->to('/dashboard');

    }

    public function updateSupervisor(Request $request) {
        PreferredSupervisor::updateOrCreate([
            'employee_id' => Auth::user()->employee_id,
            'position_nbr' => Auth::user()->employee_demo->position_number
        ], [
            'supv_empl_id' => $request->id
        ]);
        $this->updateSupervisorDetails(Auth::user()->id);
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
        $this->updateSupervisorDetails(Auth::user()->id);
        return redirect()->back();
    }
    
    public function checkExpiration(Request $request) {
        $user = Auth::user();
        $sessionExpired = true;
        if($user){
            $sessionExpired = false;
        }

        return response()->json(['sessionExpired' => $sessionExpired]);
    }

    public function updateSupervisorDetails($userid) {
        
        \DB::statement("
            UPDATE users_annex AS ua
            SET 
                ua.reporting_to_employee_id = NULL,
                ua.reporting_to_name = NULL,
                ua.reporting_to_name2 = NULL,
                ua.reporting_to_email = NULL,
                ua.reporting_to_position_number = NULL,
                ua.reporting_to_userid = NULL
            WHERE ua.user_id = {$userid}
        ");

        \DB::statement("
                UPDATE users_annex AS target,
                (SELECT ua.id, u.employee_id, ed.employee_name, u.name, u.email, ed.position_number, es.supervisor_id, 
                    (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                FROM users_annex AS ua, employee_supervisor AS es, users AS u, employee_demo AS ed
                WHERE ua.user_id = es.user_id
                    AND es.supervisor_id = u.id
                    AND es.deleted_at IS NULL
                    AND u.employee_id = ed.employee_id
                    AND ed.date_deleted IS NULL) AS source
            SET 
                target.reporting_to_employee_id = source.employee_id,
                target.reporting_to_name = source.employee_name,
                target.reporting_to_name2 = source.name,
                target.reporting_to_email = source.email,
                target.reporting_to_position_number = source.position_number,
                target.reporting_to_userid = source.supervisor_id
            WHERE target.id = source.id
                AND source.manager_updated IS NULL
                AND target.user_id = {$userid}
        ");

        \DB::statement("
            UPDATE users_annex AS target,
                (SELECT ua.id, em.supervisor_emplid, em.supervisor_name, em.supervisor_name2, em.supervisor_email, em.supervisor_position_number, em.supervisor_userid,
                    (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                FROM users_annex AS ua, employee_demo AS ed, employee_managers AS em, preferred_supervisor AS ps
                WHERE ua.employee_id = em.employee_id
                    AND ua.employee_id = ed.employee_id
                    AND ua.empl_record = ed.empl_record
                    AND em.employee_id = ps.employee_id
                    AND ed.position_number = ps.position_nbr
                    AND ed.date_deleted IS NULL
                    AND em.supervisor_emplid = ps.supv_empl_id) AS source
            SET 
                target.reporting_to_employee_id = source.supervisor_emplid,
                target.reporting_to_name = source.supervisor_name,
                target.reporting_to_name2 = source.supervisor_name2,
                target.reporting_to_email = source.supervisor_email,
                target.reporting_to_position_number = source.supervisor_position_number,
                target.reporting_to_userid = source.supervisor_userid
            WHERE target.id = source.id
                AND source.manager_updated IS NULL
                AND target.user_id = {$userid}
        ");
        
        \DB::statement("
            UPDATE users_annex AS target,
                (SELECT ua.id, em.supervisor_emplid, em.supervisor_name, em.supervisor_name2, em.supervisor_email, em.supervisor_position_number, em.supervisor_userid,
                    (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                FROM users_annex AS ua, employee_demo AS ed, employee_managers AS em
                WHERE ua.employee_id = ed.employee_id
                    AND ua.empl_record = ed.empl_record
                    AND ua.employee_id = em.employee_id
                    AND ed.position_number = em.position_number) AS source
            SET 
                target.reporting_to_employee_id = source.supervisor_emplid,
                target.reporting_to_name = source.supervisor_name,
                target.reporting_to_name2 = source.supervisor_name2,
                target.reporting_to_email = source.supervisor_email,
                target.reporting_to_position_number = source.supervisor_position_number,
                target.reporting_to_userid = source.supervisor_userid
            WHERE target.id = source.id
                AND source.manager_updated IS NULL
                AND target.user_id = {$userid}
        ");

    }

}
