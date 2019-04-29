<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;
use Auth;

class RolesController extends Controller
{
	public function create(Request $request)
	{
	try{
			$name = strtoupper($request->input('createrolename'));
			$desc = $request->input('createroledescription');
			$perms = $request->input('createrolepermissionselect');
			$users = $request->input('createroleuserselect');
			$active = $request->input('createroleactive');
			$user = Auth::user();
			$now = date('Y-m-d H:i:s');

			$role = Role::create(
				[
					'rolename' => $name, 
					'active' => $active, 
					'description' => $desc
				]);

			if(!empty($perms))
			{
				foreach($perms as $perm)
				{
					$role->permissions()->attach($perm);
				}
			}

			if(!empty($users))
			{
				foreach($users as $usr)
				{
					$role->users()->attach($usr);
				}
			}
		}
		catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new role " . $name;
        logactivity($user->userid, 13, $activity, $now); // activitytypeid 13 - Created Role

		$resp = array('success', $role);
		return $resp;
	}

	public function update(Request $request)
	{
		try{
			$roleid = $request->input('editroleselect');
			$name = strtoupper($request->input('editrolename'));
			$desc = $request->input('editroledescription');			
			$perms = $request->input('editrolepermissionselect');
			$users = $request->input('editroleuserselect');
			$active = $request->input('editroleactive');
			$user = Auth::user();
			$now = date('Y-m-d H:i:s');

			$role = Role::find($roleid);
			$role->rolename = $name;
			$role->active = $active;
			$role->description = $desc;
			$role->save();

			$currentperms = $role->permissions()->get();
			$currentusers = $role->users()->get();

			if(empty($perms) && !empty($currentperms))
			{
				// All permissions have been removed
				foreach($currentperms as $cp)
				{
					$role->permissions()->detach($cp);
				}
			}

			if(!empty($perms) && !empty($currentperms))
			{	
				// Remove permissions that have been deselected
				foreach($currentperms as $cp)
				{
					if(!in_array($cp, $perms))
					{
						$role->permissions()->detach($cp);
					}
				}
			}

			if(!empty($perms))
			{
				// Add new permissions
				foreach($perms as $perm)
				{	
					$role->permissions()->attach($perm);
				}
			}


			if(empty($users) && !empty($currentusers))
			{
				// All Users have been removed
				foreach($currentusers as $cu)
				{
					$role->users()->detach($cu);
				}
			}

			if(!empty($users) && !empty($currentusers))
			{	
				// Remove Users that have been deselected
				foreach($currentusers as $cu)
				{
					if(!in_array($cu, $users))
					{
						$role->users()->detach($cu);
					}
				}
			}

			if(!empty($users))
			{
				// Add new Users
				foreach($users as $usr)
				{	
					$role->users()->attach($usr);
				}
			}
		}
		catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

		$activity = $user->name . " edited role " . $role->rolename;
        logactivity($user->userid, 14, $activity, $now); // activitytypeid 14 - Edited Role

		return 'success';
	}

	public function getinfo(Request $request)
	{
		$roleid = $request->input('roleid');

		$role = Role::find($roleid);

		$perms = $role->permissions()->get();
		$users = $role->users()->get();

		$roleinfo = array('role' => $role, 'perms' => $perms, 'users' => $users);

		return $roleinfo;
	}

	public function __construct()
    {
        $this->middleware('auth');
    }

}
