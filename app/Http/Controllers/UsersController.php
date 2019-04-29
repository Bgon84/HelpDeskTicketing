<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\UserPhone;
use App\UserEmail;
use App\UserPriorityOverride;
use App\Group;
use App\Role;
use DB;
use Input;
use Auth;

class UsersController extends Controller
{
    public function create(Request $request)
    {
        try{
        	$first = $request->input('createuserfirstname');
        	$last = $request->input('createuserlastname');    
            $name = $first . ' ' . $last;
        	$primaryphone = $request->input('createuserprimaryphone');
        	$primaryphoneext = $request->input('createuserprimaryphoneext');
        	$otherphone = $request->input('createuserotherphone');
        	$primaryemail = $request->input('createuserprimaryemail');
    		$groups = $request->input('createusergroupselect');   	
        	$roles = $request->input('createuserroleselect');
        	$pass = $request->input('createuserpass');
            $managerid = $request->input('createusermanagerselect');
        	$priorityor = $request->input('createuserpriorityorselect');
        	$active = intval($request->input('createuseractive'));
            $direxclude = intval($request->input('createuserdirexclude'));
            $logintype = 'Native';

        	$user = User::create(
                [
                    'email' => strtolower($primaryemail), 
                    'name' => $name, 
                    'password' => Hash::make($pass), 
                    'phoneNumber' => $primaryphone, 
                    'extension' => $primaryphoneext,
                    'mobilephone' => $otherphone,
                    'active' => $active, 
                    'logintype' => $logintype,
                    'manager' => $managerid,
                    'direxclude' => $direxclude,
                ]);

        	$userid = $user['userid'];


        	// Assign User to Groups 
        	if(!empty($groups))
        	{
        		foreach($groups as $gr)
        		{
                    $user->groups()->attach($gr);
        		}
        	}
            else
            {
                $defaultgroup = DB::table('settings')
                                ->select('value')
                                ->where('setting', 'DEFAULT_GROUP')
                                ->get();

                $user->groups()->attach($defaultgroup[0]->value);
            }

        	// Assign User to Roles 
        	if(!empty($roles))
        	{
        		foreach($roles as $rl)
        		{
                    $user->roles()->attach($rl);
        		}
        	}

        	// Create UserPriorityOverride
    		if(!empty($priorityor))
        	{
        		UserPriorityOverride::create(['userid' => $userid, 'level' => $priorityor]);
        	}
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
        
        $now = date('Y-m-d H:i:s');
        $activityuser = Auth::user();
        $activity = $activityuser->name . ' created user ' . $name;
        logactivity($activityuser->userid, 11, $activity, $now); // activitytypeid 11 - Created User

        $resp = array('success', $user);
        return $resp;
    }

    public function update(Request $request)
    {

        try{
        	$userid = intval($request->input('usereditselect'));
        	$first = $request->input('edituserfirstname');
        	$last = $request->input('edituserlastname');    	
        	$primaryphone = $request->input('edituserprimaryphone');
        	$primaryphoneext = $request->input('edituserprimaryphoneext');
        	$otherphone = $request->input('edituserotherphone');
        	$primaryemail = strtolower($request->input('edituserprimaryemail'));
    		$groups = $request->input('editusergroupselect');   	
        	$roles = $request->input('edituserroleselect');
            $pass = $request->input('edituserpass');        
            $managerid = intval($request->input('editusermanagerselect'));
        	$priorityor = $request->input('edituserpriorityorselect');
        	$active = intval($request->input('edituseractive'));
            $direxclude = intval($request->input('edituserdirexclude'));            

            $user = User::find($userid);

            if($active == 0)
            {
                $user->optedin = 0;
            }

            if($pass !== null && $pass !== '')
            {
                $user->password = Hash::make($pass);
            }
            
            $user->email = $primaryemail;
        	$user->name = $first . ' ' . $last;
        	$user->active = $active;
            $user->phoneNumber = $primaryphone;
            $user->extension = $primaryphoneext;
            $user->mobilephone = $otherphone;
            $user->manager = $managerid;
            $user->direxclude = $direxclude;
        	$user->save();

        	$currentgroups = $user->groups()->get();
        	$currentroles = $user->roles()->get();

            if(empty($groups) && !empty($currentgroups))
            {   
                // User has been removed from All Groups
                foreach($currentgroups as $cg)
                {
                    $cg->users()->detach($userid);
                }
            }

        	if(!empty($groups) && !empty($currentgroups))
        	{
    	    	// Unassign User from Groups that they have been removed from
    	    	foreach($currentgroups as $cg)
    	    	{
    	    		if(!in_array($cg, $groups))
    	    		{
    	    			$cg->users()->detach($userid);
    	    		}
        		}
        	}

            if(!empty($groups))
            {
                // Assign User to Groups that they have been added to
                foreach($groups as $gr)
                {
                    $user->groups()->attach($gr);
                }
            }

            if(empty($roles) && !empty($currentroles))
            {
                // All Roles have been removed
                foreach($currentroles as $cr)
                {
                    $cr->users()->detach($userid);
                }
            }

    		if(!empty($roles) && !empty($currentroles))
    		{
    			// Unassign Roles that have been removed
    	    	foreach($currentroles as $cr)
    	    	{
    	    		if(!in_array($cr, $roles))
    	    		{
    	    			$cr->users()->detach($userid);
    	    		}
    	    	}
    		}

            if(!empty($roles))
            {
                // Assign Roles to User that have been added to them
                foreach($roles as $rl)
                {
                    $user->roles()->attach($rl);
                }
            }

        	// Save/Update Priority Override
        	UserPriorityOverride::updateOrCreate(
    						    		['userid' => $userid], 
    						    		['level' => $priorityor]);
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $now = date('Y-m-d H:i:s');
        $activityuser = Auth::user();
        $activity = $activityuser->name . ' edited user ' . $user->name;
        logactivity($activityuser->userid, 12, $activity, $now); // activitytypeid 12 - Edited User

        return 'success';    	
    }

    public function getinfo(Request $request)
    {
    	$userid = $request->input('userid');

    	$selecteduser = User::find($userid);
    	$groups = $selecteduser->groups()->get();
    	$roles = $selecteduser->roles()->get();       
    	$priorityor = $selecteduser->priorityor()->get();

        $perms = getUserPerms($userid);

    	$userinfo = array(
            'selecteduser' => $selecteduser, 
            'groups' => $groups, 
            'roles' => $roles, 
            'priorityor' => $priorityor, 
            'permissions' => $perms
        );

    	return $userinfo;
    }

    public function opt(Request $request)
    {
        $userid = $request->input('userid');
        $now = date('Y-m-d H:i:s');
        $activityuser = Auth::user();
        $user = User::find($userid);

        if($user->optedin == 0)
        {
            $user->optedin = 1;
            $user->save();
            
            logoptin($userid);

            $activity = $activityuser->name . ' opted in '. $user->name;
            logactivity($activityuser->userid, 33, $activity, $now); // activitytypeid 33 - Opted In

            return 'opted in';
        }

        if($user->optedin == 1)
        {
            $user->optedin = 0;
            $user->save();

            logoptout($userid);

            $activity = $activityuser->name . ' opted out ' . $user->name;
            logactivity($activityuser->userid, 34, $activity, $now); // activitytypeid 34 - Opted Out

            return 'opted out';            
        }       
    }

    public function updateself(Request $request)
    {
        $primaryphone = $request->input('updateuserprimaryphone');
        $extension = $request->input('updateuserprimaryphoneext');
        $mobile = $request->input('updateuserotherphone');
        $email = $request->input('updateuserprimaryemail');

        try
        {
            $user = Auth::user();

            $user->phoneNumber = $primaryphone;
            $user->extension = $extension;
            $user->mobilephone = $mobile;
            $user->email = $email;
            $user->save();
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $now = date('Y-m-d H:i:s');        
        $activity = $user->name . ' edited their own information';
        logactivity($user->userid, 12, $activity, $now); // activitytypeid 12 - Edited User

        return 'success';
    }

    public function changepassword(Request $request)
    {
        $oldpass = $request->input('changepasswordcurrent');
        $newpass = $request->input('changepasswordnew');
        $user = Auth::user();

        try
        {
            if(Hash::check($oldpass, $user->password))
            {
                $user->password = Hash::make($newpass);
                $user->save();

                $now = date('Y-m-d H:i:s');        
                $activity = $user->name . ' changed their password';
                logactivity($user->userid, 12, $activity, $now); // activitytypeid 12 - Edited User

                return 'success';
            }
            else
            {
                return 'fail';
            }
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
    }

    public function preferreddash(Request $request)
    {
        try
        {
            $prefdash = $request->input('preferreddashselect');
            $user = Auth::user();

            $user->preferreddash = $prefdash;

            $user->save();
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $now = date('Y-m-d H:i:s');        
        $activity = $user->name . ' changed their preferred dashboard';
        logactivity($user->userid, 12, $activity, $now); // activitytypeid 12 - Edited User

        return 'success';
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
}
