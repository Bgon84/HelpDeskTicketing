<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use App\User;
use App\Authentication;
use App\ActivityLog;
use Auth;
use DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers
    {
        login as protected traitlogin;
    }

    public function login(Request $request)
    {
        $request->merge([$this->username() => strtolower($request->input($this->username()))]);

        return $this->traitlogin($request);
    }


    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected function authenticated(Request $request, $user)
    {
        $ldap = Authentication::where('name', '=', 'LDAP')->first();
        $userid = $user->userid;
        $perms = getUserPerms($userid);
        $now = date('Y-m-d H:i:s');

        if($user->active == 0)
        {
            Auth::logout();

            $activity = $user->name . ' attempted to login while marked inactive';
            logactivity($userid, 32, $activity, $now); // activitytypeid 32 - Unsuccessful Login Attempt

            return view('auth.login')->with('errorMessage', 'You have been marked as inactive in the application and may not log in at this time.');
        }

        if($user->logintype == 'LDAP')
        {            
            // We have to take the string from AD for user's manager and convert it to their userid in SlickTix
            setManagerIds();

            if($ldap->active == 0)
            {
                Auth::logout();

                $activity = $user->name . ' attempted to login with LDAP credentials while LDAP was disabled';
                logactivity($userid, 32, $activity, $now); // activitytypeid 32 - Unsuccessful Login Attempt

                return view('auth.login')->with('errorMessage', 'LDAP login has been disabled, you cannot log in with LDAP credentials at this time.');
            }

            // AD useraccountcontrol values that signify the user is disabled
            $notallowed = array('16', '514', '546', '66050', '66082', '262658', '262690', '328194', '328226');

            if(in_array($user->useraccountcontrol, $notallowed))
            {
                Auth::logout();

                $activity = $user->name . ' attempted to login with disabled LDAP credentials';
                logactivity($userid, 32, $activity, $now); // activitytypeid 32 - Unsuccessful Login Attempt

                return view('auth.login')->with('errorMessage', 'Your Active Directory acccount has been disabled. Please contact your system administrator.');
            }
        }

        if($user->preferreddash !== null)
        {
            if($user->preferreddash == 'admin' && in_array('View_Admin_Dashboard', $perms))
            {
                $activity = $user->name . ' logged into the application';
                logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In                    
                return redirect('admindash');
            }

            if($user->preferreddash == 'tech' && in_array('View_Tech_Dashboard', $perms))
            {
                $activity = $user->name . ' logged into the application';
                logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
                return redirect('/techdash');
            }

            if($user->preferreddash == 'auditor' && in_array('View_Auditor_Dashboard', $perms))
            {
                $activity = $user->name . ' logged into the application';
                logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
                return redirect('/auditordash');
            }

            if($user->preferreddash == 'user' && in_array('View_User_Dashboard', $perms))
            {
               $activity = $user->name . ' logged into the application';
                logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
                return redirect('/userdash'); 
            }            
        }

        if(in_array('View_Admin_Dashboard', $perms)) 
        {
            $activity = $user->name . ' logged into the application';
            logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
            return redirect('admindash');
        } 
        elseif(in_array('View_Tech_Dashboard', $perms)) 
        {
            $activity = $user->name . ' logged into the application';
            logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
            return redirect('/techdash'); 
        }
        elseif(in_array('View_Auditor_Dashboard', $perms)) 
        {
            $activity = $user->name . ' logged into the application';
            logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
            return redirect('/auditordash');
        }        
        elseif(in_array('View_User_Dashboard', $perms))
        {
            $activity = $user->name . ' logged into the application';
            logactivity($userid, 1, $activity, $now); // activitytypeid 1 - Logged In
            return redirect('/userdash');            
        }
        else
        {
            Auth::logout();

            $activity = $user->name . ' attempted to login with no dashboard permissions';
            logactivity($userid, 32, $activity, $now); // activitytypeid 32 - Unsuccessful Login Attempt

            return view('auth.login')->with('errorMessage', 'You do not have permission to view any dashboard, please contact your system administrator');
        }
    }

    //protected $redirectTo = 'admindash';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), 4, 10
        );
    }

    public function logout(Request $request)
    {
        $userid = intval($request->input('userid'));
        $user = User::find($userid);
        $now = date('Y-m-d H:i:s');

        if($user->optedin == 1)
        {
            $user->optedin = 0;
            $user->save();

            logoptout($userid);
            
            $activity = $user->name . ' opted out by logging out';
            logactivity($user->userid, 34, $activity, $now); // activitytypeid 34 - Opted Out           
        }       

        Auth::logout();

        $activity = $user->name . ' logged out of the application';
        logactivity($userid, 2, $activity, $now); // activitytypeid 2 - Logged Out

        return redirect('/');
    }
}
