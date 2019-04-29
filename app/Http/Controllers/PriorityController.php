<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Priority;
use Auth;

class PriorityController extends Controller
{
    public function create(Request $request)
    {
    	try
    	{
    		$priority = $request->input('priority');
			$description = $request->input('description');
			$user = Auth::user();
            $now = date('Y-m-d H:i:s');

			$pri = Priority::create(
					[
						'priority' => $priority,
						'description' => $description,
					]);
    	}        
    	catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new priority " . $pri->priority;
        logactivity($user->userid, 19, $activity, $now); // activitytypeid 19 - Created Priority
		
		$resp = array('success', $pri);
		return $resp;
    }

    public function update(Request $request)
	{
		try
		{
			$priorityid = $request->input('priorityid');
			$priority = $request->input('priority');
			$description = $request->input('description');
			$user = Auth::user();
            $now = date('Y-m-d H:i:s');

			$pri = Priority::find($priorityid);

			$pri->priority = $priority;
			$pri->description = $description;
			$pri->save();
		}
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " edited priority " . $pri->priority;
        logactivity($user->userid, 20, $activity, $now); // activitytypeid 20 - Created Priority

		$resp = array('success', $pri);
		return $resp;
	}

	public function __construct()
    {
        $this->middleware('auth');
    }

}
