<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notification;
use App\User;
use App\Queue;
use Auth;
use DB;

class NotificationsController extends Controller
{
    public function create(Request $request)
    {
        try{
        	$name = strtoupper($request->input('createnotificationname'));
            $triggeraction = $request->input('createnotificationtriggeraction');
            $filterexp = $request->input('createnotificationtriggerfilterexp');
            $removefilterexp = $request->input('removecreatenotificationtriggerfilterexp');
            $recipient = $request->input('createnotificationrecipient');
        	$message = $request->input('createnotification');
        	$queues = $request->input('createnotificationqueueselect');
            $active = intval($request->input('createnotificationactive'));
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');

        	$not = Notification::create([
        		'notificationname' => $name, 
                'notification' => $message,
                'active' => $active,
                'triggeraction' => $triggeraction,
                'recipient' => $recipient,
        		]);

        	$notid = $not['notificationid'];
            
            $expressions = explode(',', $filterexp);
            $remove = explode(',', $removefilterexp);

            for($i=0; $i<count($expressions); $i++)
            {
                if(count($remove) > 0)
                {
                    if(!in_array($expressions[$i], $remove))
                    {
                        parse_str($expressions[$i]);
                        DB::table('filterexpressions')->insert(
                            [
                                'notificationid' => $notid,
                                'data' => $data, 
                                'operator' => $operator, 
                                'criteria' => $criteria
                            ]);
                    }
                }
                else
                {
                    parse_str($expressions[$i]);
                    DB::table('filterexpressions')->insert(
                        [
                            'notificationid' => $notid,
                            'data' => $data, 
                            'operator' => $operator, 
                            'criteria' => $criteria
                        ]);  
                }
               
            }


        	if(!empty($queues))
        	{
        		foreach($queues as $q)
        		{
        			$not->queues()->attach($q);
        		}
        	}
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new notification " . $not->notificationname;
        logactivity($user->userid, 23, $activity, $now); // activitytypeid 23 - Created Notification

    	$resp = array('success', $not);
    	return $resp;
    }

    public function update(Request $request)
    {
        try{
        	$notid = $request->input('editnotificationselect');
        	$name = strtoupper($request->input('editnotificationname'));
            $triggeraction = $request->input('editnotificationtriggeraction');
            $filterexp = $request->input('editnotificationtriggerfilterexp');
            $removefilterexp = $request->input('removeeditnotificationtriggerfilterexp');
            $recipient = $request->input('editnotificationrecipient');
        	$message = $request->input('editnotification');
        	$queues = $request->input('editnotificationqueueselect');
            $active = intval($request->input('editnotificationactive'));
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');

        	$not = Notification::find($notid);

        	$not->notificationname = $name;
        	$not->notification = $message;
            $not->triggeraction = $triggeraction;
            $not->recipient = $recipient;
            $not->active = $active;
        	$not->save();

            if($filterexp !== null)
            {
               $expressions = explode(',', $filterexp); 
            }
            else
            {
                $expressions = null;
            }

            if($removefilterexp !== null)
            {
                $remove = explode(',', $removefilterexp);
            }
            else
            {
                $remove = null;
            }
            
            if($expressions !== null)
            {
                for($i=0; $i<count($expressions); $i++)
                {
                    if($remove !== null)
                    {
                        if(!in_array($expressions[$i], $remove))
                        { 
                            parse_str($expressions[$i]);

                            if($id == 'null')
                            {
                                DB::table('filterexpressions')->insert(
                                    [
                                        'notificationid' => $notid,
                                        'data' => $data, 
                                        'operator' => $operator, 
                                        'criteria' => $criteria
                                    ]);
                            }

                        }

                        for($j=0;$j<count($remove);$j++)
                        {
                            parse_str($remove[$j]);
                            DB::table('filterexpressions')
                                ->where(
                                    [ 
                                        ['notificationid', $notid],
                                        ['data', $data],
                                        ['operator', $operator],
                                        ['criteria', $criteria]
                                    ])
                                ->delete();
                        }
                    }
                    else
                    {
                        parse_str($expressions[$i]);

                        if($id == 'null')
                        {
                            DB::table('filterexpressions')->insert(
                                [
                                    'notificationid' => $notid,
                                    'data' => $data, 
                                    'operator' => $operator, 
                                    'criteria' => $criteria
                                ]);  
                        }
                    }               
                }
            }
            

        	$currentqueues = $not->queues()->get();

        	if(empty($queues) && !empty($currentqueues))
        	{
        		// All queues have been unassigned from this Notificiation
        		foreach($currentqueues as $cq)
        		{
        			$not->queues()->detach($cq);
        		}
        	}

        	if(!empty($queues) && !empty($currentqueues))
        	{
        		// Remove any Queues that have been unassigned
        		foreach($currentqueues as $cq)
        		{
        			if(!in_array($cq, $queues))
        			{
        				$not->queues()->detach($cq);
        			}
        		}
        	}

        	if(!empty($queues))
        	{
        		// Add new Queues
        		foreach($queues as $q)
        		{
        			$not->queues()->attach($q);
        		}
        	}
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " edited notification " . $not->notificationname;
        logactivity($user->userid, 24, $activity, $now); // activitytypeid 24 - Created Notification

    	return 'success';
    }

    public function getinfo(Request $request)
    {
    	$notid = $request->input('notificationid');

    	$not = Notification::find($notid);
    	$queues = $not->queues()->get();

        $filterexpressions = DB::table('filterexpressions')
                                ->select('*')
                                ->where('notificationid', $notid)
                                ->get();

    	$resp = array('notification' => $not, 'queues' => $queues, 'filterexpressions' => $filterexpressions);
    	return $resp;
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
}
