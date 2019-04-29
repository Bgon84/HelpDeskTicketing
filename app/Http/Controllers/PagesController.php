<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Authentication;
use App\Ticket;

class PagesController extends Controller
{
	public function index(Request $request)
    {
        if(Auth::user())
        {
            $userid = Auth::user()->userid;
            $userperms = getUserPerms($userid);       

            if(in_array('View_Admin_Dashboard', $userperms)) 
            {
                return redirect('admindash');
            } 
            elseif(in_array('View_Tech_Dashboard', $userperms)) 
            {
                return redirect('/techdash'); 
            }
            elseif(in_array('View_Auditor_Dashboard', $userperms)) 
            {
                return redirect('/auditordash'); 
            }
            elseif(in_array('View_User_Dashboard', $userperms))
            {
                return redirect('/userdash');
            }
            else
            {
                Auth::logout();
                return view('auth.login')->with('errorMessage', 'You do not have permission to view any dashboard, please contact your system administrator');
            }
        }
        else 
        {
            return view('auth.login');
        }
    }

    public function admin(Request $request)
    {
        $user = Auth::user();  
        $userperms = getUserPerms($user->userid);

        // Get ldap values if they exist.
        $activeauth = Authentication::where(['name' => 'LDAP'])->orderBy('active', 'desc')->first();

        if(in_array('View_Admin_Dashboard', $userperms))
        {
            return view('admindash', compact('user', 'activeauth'));
        }
        else
        {
            return redirect('/');
        }
	    
    }

    public function tech(Request $request)
    {
        $user = Auth::user();
        $userperms = getUserPerms($user->userid);

        if(in_array('View_Tech_Dashboard', $userperms)) 
        {
            return view('techdash', compact('user'));
        }
        else
        {
            return redirect('/');
        }
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        $userperms = getUserPerms($user->userid);

        if(in_array('View_User_Dashboard', $userperms)) 
        {
    	   return view('userdash', compact('user')); 
        }
        else
        {
            return redirect('/');
        }   	
    }

    public function auditor(Request $request)
    {
        $user = Auth::user();
        $userperms = getUserPerms($user->userid);

        if(in_array('View_Auditor_Dashboard', $userperms)) 
        {
           return view('auditordash', compact('user'));
        }
        else
        {
            return redirect('/');
        }  
    }

    public function userdir(Request $request)
    {   
        $user = Auth::user();
        $userperms = getUserPerms($user->userid);

        if(in_array('View_User_Directory', $userperms)) 
        {
           return view('userdirectory');
        }
        else
        {
            return redirect('/');
        }          
    }

    public function edituser($id)
    {
        $user = Auth::user();
        $userperms = getUserPerms($user->userid);
        
        if(in_array('User_Edit', $userperms)) 
        {
            return redirect('admindash#users')->with('usertoedit', $id);
        }
        else
        {
            return redirect('/');
        }  
    }

    public function settechdashrefresh(Request $request)
    {
        $value = $request->value;

        try
        {
            DB::table('settings')
                    ->where('setting', 'TECH_DASH_REFRESH_RATE')
                    ->update(['value' => $value]);
        }
        catch(\Exception $ex)
        {            
            return $ex->getMessage();
        }

        return 'success';
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

}
