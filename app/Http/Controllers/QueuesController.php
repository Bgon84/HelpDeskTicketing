<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Queue;
use DB;
use Auth;


class QueuesController extends Controller
{
	public function create(Request $request)
	{
		try{
			$name = strtoupper($request->input('createqueuename'));
			$desc = $request->input('createqueuedescription');
			$groups = $request->input('createqueuegroupselect');
			$users = $request->input('createqueueuserselect');
			$options = $request->input('createqueueassignmentopts');
			$elevateq = $request->input('createqueueelevationselect');
			$active = $request->input('createqueueactive');
			$user = Auth::user();
            $now = date('Y-m-d H:i:s');

			$q = Queue::create(
				[
					'queuename' => $name,
					'elevationqueue' => $elevateq, 
					'active' => $active,
					'description' => $desc,
				]);

			if(!empty($users))
			{
				foreach($users as $usr)
				{
					$q->users()->attach($usr);
				}
			}

			if(!empty($groups))
			{
				foreach($groups as $group)
				{
					$q->groups()->attach($group);
				}
			}

			if(!empty($options))
			{
				$q->options()->attach($options);
			}					

		}
		catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new queue " . $q->queuename;
        logactivity($user->userid, 17, $activity, $now); // activitytypeid 17 - Created Queue

		$resp = array('success', $q);
		return $resp;
	}

	public function update(Request $request)
	{		
		try{
			$qid = intval($request->input('editqueueselect'));
			$name = strtoupper($request->input('editqueuename'));
			$desc = $request->input('editqueuedescription');			
			$groups = $request->input('editqueuegroupselect');
			$users = $request->input('editqueueuserselect');
			$options = $request->input('editqueueassignmentopts');
			$elevateq = $request->input('editqueueelevationselect');
			$active = intval($request->input('editqueueactive'));
			$user = Auth::user();
            $now = date('Y-m-d H:i:s');

			$q = Queue::find($qid);
			$q->queuename = $name;
			$q->description = $desc;
			$q->elevationqueue = $elevateq;
			$q->active = $active;
			$q->save();

			$currentusers = $q->users()->get();
			$currentgroups = $q->groups()->get();
			$currentoptions = $q->options()->get();

			if(empty($users) && !empty($currentusers))
			{
				// All users have been removed from this queue
				foreach($currentusers as $cu)
				{
					$q->users()->detach($cu);
				}
			}

			if(!empty($users) && !empty($currentusers))
			{
				// Unassign users that have been removed
				foreach ($currentusers as $cu) 
				{
					if(!in_array($cu, $users))
					{
						$q->users()->detach($cu);
					}
				}
			}

			if(!empty($users))
			{
				// Assign users that have been added to Queue
				foreach($users as $usr)
				{
					$q->users()->attach($usr);
				}
			}

			if(empty($groups) && !empty($currentgroups))
			{
				// All groups have been removed from this queue
				foreach($currentgroups as $cg)
				{
					$q->groups()->detach($cg);
				}
			}

			if(!empty($groups) && !empty($currentgroups))
			{
				// Unassign groups that have been removed
				foreach ($currentgroups as $cg) 
				{
					if(!in_array($cg, $groups))
					{
						$q->groups()->detach($cg);
					}
				}
			}

			if(!empty($groups))
			{
				// Assign groups that have been added to Queue
				foreach($groups as $group)
				{
					$q->groups()->attach($group);
				}
			}

			if($options !== $currentoptions)
			{

				$q->options()->detach($currentoptions);
				$q->options()->attach($options);
			}

		}
		catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

		$activity = $user->name . " edited queue " . $q->queuename;
        logactivity($user->userid, 18, $activity, $now); // activitytypeid 18 - Edited Queue

		$resp = array('success', $q);
		return $resp;		
	}

	public function getinfo(Request $request)
	{
		$qid = $request->input('queueid');

		$q = Queue::find($qid);
		$users = $q->users()->get();
		$groups = $q->groups()->get();
		$options = $q->options()->get();

		$resp = array('queue' => $q, 'users' => $users, 'groups' => $groups, 'options' => $options);

		return $resp;
	}

	public function gettechs(Request $request)
	{
		$queues = $request->input('queues');
		$techs = getQueueTechs($queues);

		return $techs;
	}

	public function checkfortickets(Request $request)
	{
		$qid = $request->input('queueid');
	    $q = Queue::find($qid);
	    $tickets = $q->tickets()->whereNotIn('statusid', [3, 4])->get();

	    if(count($tickets) > 0)
	    {
	    	$ticketids = array();
	    	foreach($tickets as $tick)
	    	{
	    		if(!in_array($tick->ticketid, $ticketids))
	    		{
	    			array_push($ticketids, $tick->ticketid);
	    		}
	    	}

	    	$ticketinfo = DB::table('tickets')
		                        ->leftJoin('users', 'tickets.requestorid', '=', 'users.userid')
		                        ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
		                        ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
		                        ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
		                        ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
		                        ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
		                        ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
		                        ->select('users.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'ticketqueues.queueid', 'queues.queuename')
		                        ->whereIn('tickets.ticketid', $ticketids)
		                        ->where([['tickets.parentticketid', null],['ticketqueues.queueid', $qid]])
		                        ->orderBy('priority', 'desc')
		                        ->get();
	    }
	    else
	    {
	    	$ticketinfo = null;
	    }	    

	    return $ticketinfo;
	}

	public function movetickets(Request $request)
	{
		$fromqid = $request->input('fromqueueid');
		$tickids = $request->input('ticketids');
		$toqid = $request->input('toqueueselect');

		$ticketids = explode(',', $tickids);

		try
		{
			$from = Queue::find($fromqid);
			$to = Queue::find($toqid);

			$existing = DB::table('ticketqueues')
							->select('ticketid')
							->where('queueid', $toqid)
							->get();

			foreach($ticketids as $ticket)
			{
				$from->tickets()->detach($ticket);
				
				if(!$existing->contains('ticketid', $ticket))
				{
					$to->tickets()->attach($ticket);
				}				
			}
		}
		catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $from->active = 0;
        $from->save();
        $fromqname = $from->queuename;
        $resp = array('success', $fromqid, $fromqname);
        return $resp;
	}

	public function __construct()
    {
        $this->middleware('auth');
    }
}
