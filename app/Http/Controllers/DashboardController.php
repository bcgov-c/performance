<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SharedProfile;
use App\Models\DashboardMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index() {
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
                                    ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB']);
                        })
                        ->orWhere('dashboard_notifications.notification_type', '')
                        ;    
                })
            ->orderby('status', 'asc')->orderby('created_at', 'desc')
            ->paginate(8);
        $notifications_unread = DashboardNotification::where('user_id', Auth::id())->where('status', null)
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
                                                ->whereIn('dashboard_notifications.notification_type', ['GC', 'GR', 'GB']);
                                    });    
                                });
        $supervisorTooltip = 'If your current supervisor in the Performance Development Platform is incorrect, please have your supervisor submit a service request through AskMyHR and choose the category: <span class="text-primary">My Team or Organization > HR Software Systems Support > Position / Reporting Updates</span>';        
        $sharedList = SharedProfile::where('shared_id', Auth::id())->with('sharedWithUser')->get();
        $profilesharedTooltip = 'If this information is incorrect, please discuss with your supervisor first and escalate to your organization\'s Strategic Human Resources shop if you are unable to resolve.';
        
        $message= '';
        $messages = $this->getDashboardMessage();
        
        if (count($messages) > 0) {
            foreach ($messages as $message) {}
        }

        return view('dashboard.index', compact('greetings', 'tab', 'supervisorTooltip', 'sharedList', 'profilesharedTooltip', 'notifications', 'notifications_unread', 'message'));
    }

    public function show(Request $request, $id) {

        $notification = DashboardNotification::where('id', $id)->first();

        // TODO: update
        if ($notification) {
            $notification->status = 'R';
            $notification->save();

            return redirect( $notification->url );
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
        $ids = $request->ids;
        DashboardNotification::wherein('id',explode(",",$ids))->delete();
        return redirect()->back();
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
        $ids = $request->ids;
        // var check = confirm($ids);
        // dd("RESULTS: " . $ids);
        DashboardNotification::wherein('id',explode(",",$ids))->update(['status' => 'R']);
        // return route::get('dashboard.notifications');
        return redirect()->back();
        // $notification = DashboardNotification::where('id', 4)->update(['status' => 'S']);
        // window.location.reload();
        // return redirect()->to('/route');
        // header("Refresh:0");
        // return back();
        // return response()->json(['success'=>"Notification(s) updated successfully."]);
    }

    public function resetstatus(request $request)
    {
        $ids = $request->ids;
        DashboardNotification::wherein('id',explode(",",$ids))->update(['status' => null]);
        // DashboardNotification::where('id',5)->update(['status' => 'D']);
        // header("Refresh:0");
        // return back();
        // window.location.reload();
        // return redirect()->to('/route');
        return redirect()->back();
        // return response()->json(['success'=>"Notification(s) updated successfully."]);
    }
    
    
    public function revertIdentity(Request $request) {
         $oldUserId = $request->session()->get('existing_user_id');
         Auth::loginUsingId($oldUserId);
         $request->session()->forget('existing_user_id');
         $request->session()->forget('user_is_switched');
         //return redirect()->back();
         return redirect()->to('/');

    }

}
