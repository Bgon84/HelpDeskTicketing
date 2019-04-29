<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\User;
use App\Role;
use DB;
use Auth;

class GroupsController extends Controller
{
    public function create(Request $request)
    {
        try{
        	$groupname = strtoupper($request->input('creategroupname'));
            $desc = $request->input('creategroupdescription');
        	$roles = $request->input('creategrouproleselect');
        	$users = $request->input('creategroupuserselect');
        	$active = $request->input('creategroupactive');
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');

    		$group = Group::create(
                [
                    'groupname' => $groupname, 
                    'active' => $active,
                    'description' => $desc,
                ]);

      		$groupid = $group['groupid'];	

      		// Assign users to groups via usergroups table
      		if(!empty($users))
      		{
    	  		foreach($users as $usr)
    	    	{
    	    		$group->users()->attach($usr);
    	    	}
    	    }

    	    // Assign roles to group via grouproles table
        	if(!empty($roles))
        	{
               	foreach($roles as $role)
        		{
        			$group->roles()->attach($role);
        		}
        	}
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new group " . $group->groupname;
        logactivity($user->userid, 15, $activity, $now); // activitytypeid 15 - Created Group

        $resp = array('success', $group);
    	return $resp;
    }


    public function update(Request $request)
    {
        try{
        	$groupid = $request->input('editgroupselect');
        	$groupname = strtoupper($request->input('editgroupname'));
            $desc = $request->input('editgroupdescription');            
        	$roles = $request->input('editgrouproleselect');
        	$users = $request->input('editgroupuserselect');
        	$active = intval($request->input('editgroupactive'));	
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');
        	
        	$group = Group::find($groupid);

        	$group->groupname = $groupname;
        	$group->active = $active;
            $group->description = $desc;
        	$group->save();

        	$currentusers = $group->users()->get();
        	$currentroles = $group->roles()->get();


            if(empty($users) && !empty($currentusers))
            {
                // All Users have been deselected from Group, remove them
                foreach($currentusers as $cu)
                {
                    $group->users()->detach($cu);
                }
            }

            if(!empty($users) && !empty($currentusers))
            {
                // Unassign users that have been removed from Group
                foreach($currentusers as $cu)
                {
                    if(!in_array($cu, $users))
                    {
                        $group->users()->detach($cu);
                    }
                }  
            }

            if(!empty($users))
            {
                // Add new Users to Group
                foreach($users as $usr)
                {
                    $group->users()->attach($usr);
                }  
            }

            if(empty($roles) && !empty($currentroles))
            {
                // All Roles have been deselected from Group, remove them
                foreach($currentroles as $cr)
                {
                    $group->roles()->detach($cr);
                }
            }

            if(!empty($roles) && !empty($currentroles))
            {
                // Unassign roles that have been removed from Group
                foreach($currentroles as $cr)
                {
                    if(!in_array($cr, $roles))
                    {
                        $group->roles()->detach($cr);
                    }
                }
            }

            if(!empty($roles))
            {
                // Assign roles to group via grouproles table                            
                foreach($roles as $role)
                {
                    $group->roles()->attach($role);
                }            
            }
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " edited group " . $group->groupname;
        logactivity($user->userid, 16, $activity, $now); // activitytypeid 16 - Edited Group
    	
    	return ('success');
    }


    public function getinfo(Request $request)
    {
    	$groupid = $request->input('groupid');
    	
    	$group = Group::find($groupid);

    	$users = $group->users()->get();
    	$roles = $group->roles()->get();

    	$groupinfo = array('group' => $group, 'users' => $users, 'roles' => $roles);

    	return $groupinfo;
    }

    public function setdefaultgroup(Request $request)
    {
        try
        {
            $groupid = $request->input('groupid');

            DB::table('settings')
                ->where('setting', 'DEFAULT_GROUP')
                ->update(['value' => $groupid]);
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
