<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ticket;
use App\Category;
use App\Status;
use App\Priority;
use App\User;
use App\TicketAttachment;
use App\TicketDescription;
use App\TicketUpdate;
use DB;
use Auth;

class TicketsController extends Controller
{
    public function create(Request $request)
    {
        try
        {
        	// Get data from form
            $requestor = Auth::user();
        	$requestorid = intval($requestor->userid);
            $proxyid = $request->input('proxyselect');
        	$categoryid = intval($request->input('categoryselect'));
        	$desc = $request->input('description');
        	$attachments = $request->file('attachment');
            $selectedtech = $request->input('techselect');

            // Check if this ticket is a proxy ticket
            if($proxyid !== 'self')
            {
                $submitter = User::find($requestorid);
                $actualrequestor = User::find(intval($proxyid));
                $proxymessage = ' *** ' . $submitter->name . ' opened this ticket on behalf of ' . $actualrequestor->name . ' ***';
                $desc .= $proxymessage;
                $requestorid = intval($proxyid);
            }

        	// Set statusid to 1 for 'Submitted'
        	$statusid = 1;

        	// Insert the description into the TicketDescriptions table and retrieve its ID
        	$description = TicketDescription::create(['description' => $desc]);
        	$descid = $description['descriptionid'];

        	// Check for priority override for Requestor
        	$requestor = User::find($requestorid);
        	$priorityor = $requestor->priorityor()->get()->toArray();
            $category = Category::find($categoryid);
            $ticketpriorityid = intval($category->priorityid);
            $ticketpriority = Priority::find($ticketpriorityid);

        	if(!empty($priorityor))
        	{	
        		// Requestor has priority override
        		$priorityorlvl = $priorityor[0]['level'];
                $orpriority = Priority::where('priority', '=', $priorityorlvl)->get();
                
                if($ticketpriority->priority > $priorityorlvl)
                {
                    $priorityid = $ticketpriorityid;
                }
                else
                {
                    $priorityid = $orpriority[0]->priorityid;
                }
        	}
        	else
        	{	
        		$priorityid = $ticketpriorityid;
        	}

        	// Create the ticket and retrieve its ID
    		$ticket = Ticket::create([
    				'requestorid' => $requestorid, 
    				'descriptionid' => $descid,
    				'categoryid' => $categoryid, 
    				'priorityid' => $priorityid,
    				'statusid' => $statusid,
    			]);

    		$ticketid = intval($ticket->ticketid);

            // Check for any attachments that were sent and process them
            if($attachments !== null)
            {           
                try{ 
                    $count = 1;
                    foreach($attachments as $att)
                    { 
                        $ext = $att->extension();    
                        $filename = 'Ticket_' . $ticketid . '_attachment_' . $count . '.' . $ext;
                        $att->storeAs('public', $filename);
                        $path = '/storage/' . $filename;

                        TicketAttachment::create([
                                    'ticketid' => $ticketid,
                                    'attachmentpath' => $path]);
                        $count++; 
                    }
                } 
                catch(\Exception $ex)
                {
                    error_log($ex->getMessage());
                    return 'Unable to upload your file(s) ' . $ex->getMessage();
                }
            }

            // Assign to Queue(s) based on Category
            $queues = $category->queues()->where('queues.active', 1)->get();
            $qids = array();
            foreach($queues as $q)
            {
                $q->tickets()->attach($ticket);

                array_push($qids, $q->queueid);

                 // Check Assignment Options while we're here
                $options = $q->options()->get();
                foreach($options as $option)
                {   
                    $assignedtechs = $ticket->techs()->get();

                    if($option->optionid == 1)
                    {
                        //Round Robin Assignment
                        $whogetsit = $this->assignRoundRobin($q->queueid);
                        if($whogetsit !== 0 && !$assignedtechs->contains('userid', $whogetsit))
                        {
                            $ticket->techs()->attach($whogetsit);
                            $this->logRoundRobin($q->queueid, $whogetsit);
                        }
                    }
                    elseif($option->optionid == 2)
                    {
                        // Select Tech
                        if($selectedtech !== 0 && $selectedtech !== null && $selectedtech !== 'unassigned' && !$assignedtechs->contains('userid', $selectedtech))
                        {
                            $ticket->techs()->attach($selectedtech);
                        }
                    }
                }
            }
        } 
        catch(\Exception $ex)
        {
            error_log($ex->getMessage());
            return $ex->getMessage();
        }      
              
        $action = 'submit';
        checkfornotification($action, $qids, $ticket);

        $now = date('Y-m-d H:i:s');
        $activity = $requestor->name . ' opened Ticket #' . $ticketid;
        logactivity($requestor->userid, 3, $activity, $now); // activitytypeid 3 - Opened Ticket

		return 'success';
    }

    public function update(Request $request)
    {
        $ticketid = intval($request->input('ticketid'));
        $changedby = Auth::user();
        $statusid = intval($request->input('updateticketstatus'));
        $priorityid = intval($request->input('updateticketpriority'));
        $categoryid = intval($request->input('updateticketcategory'));
        $techids = $request->input('updatetickettech');
        $pubnote = $request->input('ticketupdatemessage');
        $internalnote = $request->input('ticketupdateinternalnotes');
        $attachments = $request->file('ticketupdateattachment');
        $now = date('Y-m-d H:i:s');

        // pull all current info for ticket to see what is being updated
        $currentinfo = Ticket::find($ticketid);
        $qids = getqids($currentinfo);

        $updatearray = array();
        $newinfoarray = array();

        $pubnoteadded = false;
        $intnoteadded = false;

        //Notes can be added without checking old values, just make sure there's something there
        if($pubnote !== null && $pubnote !== '')
        {
            array_push($updatearray, ['updatetypeid' => 5, 'content' => 'New Public Note: ' . $pubnote]);
            $newinfoarray['publicnote'] = $pubnote;

            $pubnoteadded = true;            
        }

        if($internalnote !== null && $internalnote !== '')
        {
            array_push($updatearray, ['updatetypeid' => 6, 'content' => 'New Internal Note: ' . $internalnote]);
            $newinfoarray['internalnote'] = $internalnote;    

            $intnoteadded = true;                  
        }

        // Check new values against old and add to updatearray if they've changed
        if($statusid !== null && $statusid !== $currentinfo->statusid)
        {
            $oldvalue = $currentinfo->statusid;
            $newvalue = $statusid;
            $currentinfo->statusid = $newvalue;
            $oldstatus = Status::where('statusid', '=', $oldvalue)->get();
            $newstatus = Status::where('statusid', '=', $newvalue)->get();

            if($newstatus[0]->status == 'Resolved')
            {
                $currentinfo->dateresolved = $now;
                
                // Calculate the time to close the ticket
                $opened = strtotime($currentinfo->created_at);
                $closed = strtotime($currentinfo->dateresolved);

                $timetoclose = $closed - $opened;

                // Check if the ticket was frozen
                $frozen = DB::table('ticketfreeze')
                                ->where('ticketid', $ticketid)
                                ->get();

                if(count($frozen) > 0)
                {
                    $timefrozen = strtotime($frozen[0]->thawed) - strtotime($frozen[0]->frozen);
                    $timetoclose -= $timefrozen;
                }

                $currentinfo->timetoclose = $timetoclose;
                
                $activity = $changedby->name . ' resolved Ticket #' . $currentinfo->ticketid;
                logactivity($changedby->userid, 5, $activity, $now); // activitytypeid 5 - Resolved Ticket


                $action = 'resolution';
                checkfornotification($action, $qids, $currentinfo);

                // find any child tickets and mark them as resolved
                $children = DB::table('tickets')
                                ->select('ticketid')
                                ->where('parentticketid', $ticketid)
                                ->get();

                if(!empty($children))
                {
                    foreach($children as $child)
                    { 
                        $kid = Ticket::find($child->ticketid);
                        $kid->dateresolved = $now;
                        $kidstatus = Status::find($kid->statusid);

                        TicketUpdate::create([
                            'ticketid' => $kid->ticketid,
                            'userid' => $changedby->userid,
                            'content' => 'Status changed from ' . $kidstatus->status . ' to Resolved',
                            'updatetypeid' => 1
                            ]);   

                         // Calculate the time to close the ticket
                        $opened = strtotime($kid->created_at);
                        $closed = strtotime($kid->dateresolved);

                        $timetoclose = $closed - $opened;

                        // Check if the ticket was frozen and substract that time from timetoclose
                        $frozen = DB::table('ticketfreeze')
                                        ->where('ticketid', $kid->ticketid)
                                        ->get();

                        if(count($frozen) > 0)
                        {
                            $timefrozen = strtotime($frozen[0]->thawed) - strtotime($frozen[0]->frozen);
                            $timetoclose -= $timefrozen;
                        }

                        $kid->timetoclose = $timetoclose;                   

                        $kid->statusid = 3; // statusid 3 for resolved
                        $kid->save();

                        $activity = $changedby->name . ' resolved Ticket #' . $kid->ticketid;
                        logactivity($changedby->userid, 5, $activity, $now); // activitytypeid 5 - Resolved Ticket

                        $qids = getqids($kid);
                        $action = 'resolution';
                        checkfornotification($action, $qids, $kid);
                    }
                }
            }

            // remove resolved date if ticket is reopened
            if($currentinfo->statusid == 3 && $newstatus[0]->status !== 'Resolved')
            {
                $currentinfo->dateresolved = null;
            }

            array_push($updatearray, ['updatetypeid' => 1, 'content' => 'Status changed from ' . $oldstatus[0]->status . ' to ' . $newstatus[0]->status]);
            $newinfoarray['status'] = $newstatus[0]->status;  

            $action = 'status';
            checkfornotification($action, $qids, $currentinfo);
        }

        if($priorityid !== null && $priorityid !== $currentinfo->priorityid)
        {
            $oldvalue = $currentinfo->priorityid;
            $newvalue = $priorityid;
            $currentinfo->priorityid = $newvalue;

            $oldpriority = Priority::where('priorityid', '=', $oldvalue)->get();
            $newpriority = Priority::where('priorityid', '=', $newvalue)->get();

            array_push($updatearray, ['updatetypeid' => 2, 'content' => 'Priority changed from ' . $oldpriority[0]->priority . ' to ' . $newpriority[0]->priority]);
            $newinfoarray['priority'] = $newvalue;

            $action = 'priority';
            checkfornotification($action, $qids, $currentinfo);
        }

        if($categoryid !== null && $categoryid !== $currentinfo->categoryid)
        {
            $oldvalue = $currentinfo->categoryid;
            $newvalue = $categoryid;
            $currentinfo->categoryid = $newvalue;
            $oldcat = Category::where('categoryid', '=',  $oldvalue)->get();
            $newcat = Category::where('categoryid', '=',  $newvalue)->get();

            array_push($updatearray, ['updatetypeid' => 3, 'content' => 'Category changed from ' . $oldcat[0]->category . ' to ' . $newcat[0]->category]);
            $newinfoarray['category'] = $newcat[0]->category;     

            $action = 'cat';
            checkfornotification($action, $qids, $currentinfo);  
        }

        // pull Current Techs and New Techs and assign/unassign as needed
        $currenttechs = $currentinfo->techs()->get();
        $addedtechs = User::find($techids);
        $newtechs = '';
        $oldtechs = '';

        if(empty($techids) && !empty($currenttechs) && $techids !== null)
        {
            // All techs have been removed from ticket
            foreach($currenttechs as $tech)
            {
                $currentinfo->techs()->detach($tech);
                $oldtechs .= ' ' . $tech->name;
            }

            array_push($updatearray, ['updatetypeid' => 4, 'content' => 'Tech(s) changed from ' . $oldtechs . ' to ' . $newtechs]);
            $currentinfo->updated_at = date('Y-m-d H:i:s');

            $action = 'tech';
            checkfornotification($action, $qids, $currentinfo); 
        }

        if(!empty($techids) && !empty($currenttechs))
        {
            // Remove the techs that have been unassigned
            foreach($currenttechs as $tech)
            {
                if(!in_array($tech, $techids))
                {
                    $currentinfo->techs()->detach($tech);
                    $oldtechs .= ' ' . $tech->name;
                }
            }
        }

        if(!empty($techids) && $techids !== null)
        {
            // Add new permissions
            foreach($techids as $tech)
            {   
                $currentinfo->techs()->attach($tech);
            }

            for($i=0; $i<count($addedtechs); $i++)
            {
                $newtechs .= ' ' . $addedtechs[$i]->name;
            }

            if($oldtechs !== $newtechs)
            {
                array_push($updatearray, ['updatetypeid' => 4, 'content' => 'Tech(s) changed from' . $oldtechs . ' to' . $newtechs]); 
                $currentinfo->updated_at = date('Y-m-d H:i:s');  
                $newinfoarray['techs'] = $newtechs;

                $action = 'tech';
                checkfornotification($action, $qids, $currentinfo);
            }
        }

        $currentinfo->save();

        $newinfoarray['changedby'] = $changedby->name;

        // Check for any attachments that were sent and process them
        if($attachments !== null)
        {
            
            try{
                $count = DB::table('ticketattachments')->where('ticketid', '=', $ticketid)->count() + 1;
                $numberadded = 0;

                foreach($attachments as $att)
                { 
                    $ext = $att->extension();                   
                    $filename = 'Ticket_' . $ticketid . '_attachment_' . $count . '.' . $ext;
                    $att->storeAs('public', $filename);
                    $path = '/storage/' . $filename;

                    TicketAttachment::create([
                                'ticketid' => $ticketid,
                                'attachmentpath' => $path]);
                    $count++; 
                    $numberadded++;
                }

                array_push($updatearray, ['updatetypeid' => 7, 'content' => $numberadded .' new attachment(s) added']);

                $action = 'attachment';
                checkfornotification($action, $qids, $currentinfo);
            } 
            catch(Exception $ex)
            {
                error_log($ex->getMessage());
                return 'Unable to upload your file(s)';
            }
        }

        if(!empty($updatearray))
        {
            foreach($updatearray as $update)
            {
                TicketUpdate::create([
                    'ticketid' => $ticketid,
                    'userid' => $changedby->userid,
                    'content' => $update['content'],
                    'updatetypeid' => $update['updatetypeid']
                    ]);
            }

            $resp = json_encode(array(array('status' => 'success'), $newinfoarray, $currentinfo, $updatearray));
        }
        else
        {
            $resp = json_encode("nochange");
        }

        if($pubnoteadded)
        {
            $action = 'pubnote';
            checkfornotification($action, $qids, $currentinfo);
        }

        if($intnoteadded)
        { 
            $action = 'intnote';
            checkfornotification($action, $qids, $currentinfo);  
        }

        $activity = $changedby->name . ' updated Ticket #' . $ticketid;
        logactivity($changedby->userid, 4, $activity, $now); // activitytypeid 4 - Updated Ticket

        return $resp;
    }

    public function getinfo($id)
    {
        // Info for choosen ticket
    	$ticketinfo = DB::table('tickets')
                    ->leftJoin('users', 'tickets.requestorid', '=', 'users.userid')
                    ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
                    ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
                    ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
                    ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
                    ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
                    ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
                    ->select('users.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'ticketqueues.queueid', 'queues.elevationqueue', 'queues.queuename', 'queues.queueid', 'tickets.masterticket', 'tickets.parentticketid', 'tickets.requestorid')
                    ->where('tickets.ticketid', '=', $id)
                    ->get();

        // If it's a Master, find its Children
        if($ticketinfo[0]->masterticket == 1)
        {
            $children = DB::table('tickets')
                        ->select('ticketid')
                        ->where('parentticketid', $ticketinfo[0]->ticketid)
                        ->orderBy('ticketid')
                        ->get();
        }
        else
        {
            $children = null;
        }

        // Find all open tickets in the same queue that are not Master tickets or already merged (children)
        $opentixsameq = DB::table('tickets')
                        ->leftJoin('users', 'tickets.requestorid', '=', 'users.userid')
                        ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
                        ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
                        ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
                        ->select('users.name AS requestor', 'ticketdescriptions.description', 'tickets.ticketid', 'ticketqueues.queueid')
                        ->where([['ticketqueues.queueid', '=', $ticketinfo[0]->queueid],['tickets.ticketid', '<>', $id], ['tickets.masterticket', 0]])
                        ->whereNull('tickets.dateresolved')
                        ->whereNull('tickets.parentticketid')
                        ->get();

        // Find attachments
        $attachments = DB::table('ticketattachments')
        				->select('attachmentpath')
        				->where('ticketid', '=', $id)
        				->get();

        // Find updates
        $updates = DB::table('ticketupdates AS tu')
                        ->leftJoin('users AS u', 'tu.userid', '=', 'u.userid')
                        ->select('tu.content', 'tu.updated_at', 'tu.updatetypeid', 'u.name', 'u.department', 'u.email', 'u.phoneNumber', 'u.faxnumber', 'u.mobilephone', 'u.location')
                        ->where('tu.ticketid', '=', $id)
                        ->get();
        // Find techs
        $techs = DB::table('tickettechs')
                    ->leftJoin('users', 'tickettechs.techid', '=', 'users.userid')
                    ->select('users.name', 'tickettechs.ticketid', 'tickettechs.techid')
                    ->where('tickettechs.ticketid', '=', $id)
                    ->get();

        $requestorid = $ticketinfo[0]->requestorid;
        $requestorinfo = User::find($requestorid);

        foreach($techs as $tech){
            $techarray[] = $tech->techid;
        }        

        return view('ticketdetails', compact(['ticketinfo', 'attachments', 'updates', 'techarray', 'opentixsameq', 'children', 'requestorinfo']));  
    }	

    public function logRoundRobin($qid, $techid)
    {
        DB::table('roundrobin')
            ->insert(['queueid' => $qid, 'techid' => $techid, 'created_at' => date('Y-m-d H:i:s')]);
    }

    public function assignRoundRobin($qid)
    {
        // Find all techs that are opted in
        $optedintechs = DB::table('users')
                        ->select('userid')
                        ->where('optedin', '=', 1)
                        ->get();


        if(count($optedintechs) > 0)
        {
            // We have opted in techs, let's check if any of them service this queue
            $availabletechs = array();
            foreach($optedintechs as $tech)
            {
                $userqueues = getUsersQueues($tech->userid);

                if(in_array($qid, $userqueues))
                {
                    array_push($availabletechs, $tech->userid);
                }
            }

            if(count($availabletechs) < 1)
            {
                // No techs that service this queue are opted in
                $whogetsit = 0;
                return $whogetsit;
            }

            if(count($availabletechs) == 1)
            {
                // Only one tech who services this queue is available, so they get the ticket
                $whogetsit = $availabletechs[0];
                return $whogetsit;
            }

            // More than one opted in tech services this queue, let's pull their last assignments and compare
            $lastassignments = DB::table('roundrobin')
                                ->select(DB::raw('techid, MAX(created_at) as latest'))
                                ->where('queueid', intval($qid))
                                ->whereIn('techid', $availabletechs)
                                ->groupBy('techid')
                                ->get();

            // If there are at least as many last assignments as there are available techs we compare the dates, oldest gets the ticket
            if(count($lastassignments) >= count($availabletechs))
            {
                $oldest = strtotime(date('Y-m-d H:i:s'));
                foreach($lastassignments as $assignment)
                {
                    $latestassigment = strtotime($assignment->latest);
                    if($latestassigment < $oldest)
                    {
                        $oldest = $latestassigment;
                        $whogetsit = $assignment->techid;
                    }
                }         
            }
            else
            {
                // There are less last assignments than available techs, so if tech's id is not in one of the last assignments, they get the ticket
                for($i=0; $i<count($availabletechs); $i++)
                {
                    if(!$lastassignments->contains('techid', $availabletechs[$i]))
                    {
                        $whogetsit = $availabletechs[$i];
                        return $whogetsit;
                    }
                }
            }
        }
        else
        {
            $whogetsit = 0;
        }      
        return $whogetsit;
    }

    public function voidticket(Request $request)
    {
        $ticketid = intval($request->input('ticketid'));
        $now = date('Y-m-d H:i:s');
        $user = Auth::user();
        
        try
        {
            DB::table('tickets')
                ->where('ticketid', $ticketid)
                ->update(['statusid' => 4, 'dateresolved' => $now]);            
        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => "VOIDED",
            'updatetypeid' => 1
            ]);

        $activity = $user->name . ' voided Ticket #' . $ticketid;
        logactivity($user->userid, 6, $activity, $now); // activitytypeid 6 - Voided Ticket

        $ticket = Ticket::find($ticketid);
        $qids = getqids($ticket);
        $action = 'void';
        checkfornotification($action, $qids, $ticket);

        return 'success';
    }

    public function escalateticket(Request $request)
    {
        try
        {
            //Escalation Questionnaire fields
            $application = $request->input('application'); 
            $area = $request->input('area'); 
            $fields = $request->input('fields'); 
            $problem = $request->input('problem');
            $steps = $request->input('steps');
            $actions = $request->input('actions');

            $ticketid = intval($request->input('ticketid'));
            $currentstatus = $request->input('currentstatus');
            $currentqueue = intval($request->input('currentqueue'));
            $elevationqueue = intval($request->input('elevationqueueid'));
            $user = Auth::user();

            $ticket = Ticket::find($ticketid);

            //Build internal note from Escalation Questionnaire
            $internalnote = $user->name . ' escalated this ticket with the following information: APPLICATION - ' . $application; 
            $internalnote .= ', AREA OF APPLICATION - ' . $area . ', FIELDS - ' . $fields . ', PROBLEM STATEMENT - ' . $problem; 
            $internalnote .= ', STEPS TO REPRODUCE - ' . $steps . ', ACTIONS TAKEN - ' . $actions;

            $ticket->queues()->detach();
            $ticket->queues()->attach($elevationqueue);
            $ticket->techs()->detach();
            $ticket->statusid = 6;
            $ticket->save();        
        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        $content = "Status changed from " . $currentstatus . " to Escalated";
        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => $content,
            'updatetypeid' => 1
            ]);

        $now = date('Y-m-d H:i:s');
        $activity = $user->name . ' escalated Ticket #' . $ticketid;
        logactivity($user->userid, 7, $activity, $now); // activitytypeid 7 - Escalated Ticket

        $qids = getqids($ticket);
        $action = 'escalate';
        checkfornotification($action, $qids, $ticket);

        // Add internal note with Escalation Questionnaire values
        $content = 'New Internal Note: ' . $internalnote;
        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => $content,
            'updatetypeid' => 6
            ]);

        $action = 'intnote';
        checkfornotification($action, $qids, $ticket);

        return 'success';
    }

    public function freezeticket(Request $request)
    {
        try
        {
            $ticketid = intval($request->input('ticketid'));
            $currentstatus = $request->input('currentstatus');
            $now = date('Y-m-d H:i:s');
            $user = Auth::user();

            $ticket = Ticket::find($ticketid);
            $ticket->statusid = 5;
            $ticket->save();

            DB::table('ticketfreeze')
                ->insert(['ticketid' => $ticketid, 'frozen' => $now]);

        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        $content = "Status changed from " . $currentstatus . " to Frozen";
        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => $content,
            'updatetypeid' => 1
            ]);

        $activity = $user->name . ' froze Ticket #' . $ticketid;
        logactivity($user->userid, 8, $activity, $now); // activitytypeid 8 - Froze Ticket

        $qids = getqids($ticket);
        $action = 'freeze';
        checkfornotification($action, $qids, $ticket);

        return 'success';
    }

    public function thawticket(Request $request)
    {
        try
        {
            $ticketid = intval($request->input('ticketid'));
            $currentstatus = $request->input('currentstatus');
            $now = date('Y-m-d H:i:s');
            $user = Auth::user();

            $ticket = Ticket::find($ticketid);
            $ticket->statusid = 2;
            $ticket->save();

            $freezeid = DB::table('ticketfreeze')
                            ->where('ticketid', $ticketid)
                            ->max('freezeid');

            DB::table('ticketfreeze')
                ->where('freezeid', $freezeid)
                ->update(['thawed' => $now]);

        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        $content = "Status changed from Frozen to In Progress";
        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => $content,
            'updatetypeid' => 1
            ]);

        $activity = $user->name . ' thawed Ticket #' . $ticketid;
        logactivity($user->userid, 9, $activity, $now); // activitytypeid 9 - Thawed Ticket

        $qids = getqids($ticket);
        $action = 'thaw';
        checkfornotification($action, $qids, $ticket);

        return 'success';
    }

    public function mergetickets(Request $request)
    {
        $ticketid = intval($request->input('ticketid'));
        $mergewithids = $request->input('mergewithids');

        if($mergewithids == null)
        { 
            return;
        }
       
        try
        {
            // Get this ticket's info
            $thisticket = Ticket::find($ticketid);
            $thisticketdesc = TicketDescription::find($thisticket->descriptionid);
            $requestor = User::find($thisticket->requestorid);

            // Begin building new description
            $newdesc = "[Ticket #" . $thisticket->ticketid . " - " . $requestor->name . "] - "  . $thisticketdesc->description;

            // Add descriptions from merging tickets
            foreach($mergewithids as $id)
            { 
                $ticket = Ticket::find(intval($id));
                $ticketdesc = TicketDescription::find($ticket->descriptionid);
                $ticketrequestor = User::find($ticket->requestorid);
                $newdesc .= " [Ticket #" . $ticket->ticketid . " - " . $ticketrequestor->name . "] - " . $ticketdesc->description;

                $qids = getqids($ticket);
                $action = 'merge';
                checkfornotification($action, $qids, $ticket);
            }

            // Create description for Master
            $description = TicketDescription::create(['description' => $newdesc]);
            $descid = $description['descriptionid'];
            $statusid = 2; // Status is In Progress

            // Create the new Master ticket
            $masterticket = Ticket::create([
                'requestorid' => $thisticket->requestorid,
                'descriptionid' => $descid,
                'categoryid' => $thisticket->categoryid, 
                'priorityid' => $thisticket->priorityid,
                'statusid' => $statusid,
                'masterticket' => 1,
            ]);

            // Find tech from original ticket and assign to Master
            $thistech = $thisticket->techs()->get();
            $masterticket->techs()->attach($thistech);

            // Assign Master to original Queue
            $thisqueue = $thisticket->queues()->get();
            $masterticket->queues()->attach($thisqueue);

            // Assign parentticketid to the children
            $parentid = $masterticket->ticketid;
            $thisticket->parentticketid = $parentid;
            $thisticket->save();

            foreach($mergewithids as $id)
            { 
                $ticket = Ticket::find(intval($id));
                $ticket->parentticketid = $parentid;
                $ticket->save();
            }
        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        $now = date('Y-m-d H:i:s');
        $user = Auth::user();
        $activity = $user->name . ' merged Ticket #' . $ticketid . ' with tickets #' . implode(', #', $mergewithids);
        logactivity($user->userid, 10, $activity, $now); // activitytypeid 10 - Merged Tickets       

        $resp = array('success', $parentid);
        return $resp;
    }

    public function newtechtixcheck(Request $request)
    {
        $userid = $request->input('userid');
        $userqueues = getUsersQueues($userid);
        $lastupdate = $request->input('lastupdate');
        $lastupdate = date('Y-m-d H:i:s', ($lastupdate/1000));

        $techtickets = DB::table('tickets')
                    ->leftJoin('users AS u1', 'tickets.requestorid', '=', 'u1.userid')
                    ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
                    ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
                    ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
                    ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
                    ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
                    ->leftJoin('users AS u2', 'tickettechs.techid', '=', 'u2.userid')
                    ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
                    ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
                    ->select('u1.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'tickettechs.techid', 'u2.name AS tech', 'ticketqueues.queueid', 'queues.queuename')
                    ->whereIn('ticketqueues.queueid', $userqueues)
                    ->where('tickets.created_at', '>=', $lastupdate)
                    ->whereNull('tickets.parentticketid')
                    ->whereNull('tickets.dateresolved')
                    ->where(function($query) use($userid)
                    {
                        $query->whereNull('tickettechs.techid')
                                ->orWhere('tickettechs.techid', $userid);
                    })                    
                    ->orderBy('priority', 'desc')
                    ->get();

        //$perms = getUserPerms($userid);

        $resp = array($techtickets);

        return $resp;
    }

    public function reopenTicket(Request $request)
    {
        $ticketid = $request->input('ticketid');
        $ticket = Ticket::find($ticketid);
        $user = Auth::user();
        $now = date('Y-m-d H:i:s');

        try
        {
            $ticket->dateresolved = null;
            $ticket->statusid = 2; //Statusid 2 = In Progress
            $ticket->save();
        }
        catch(Exception $ex)
        {
            logger()->error($ex->getMessage());
            return 'fail';
        }

        $content = "Status changed from Resolved to In Progress";
        TicketUpdate::create([
            'ticketid' => $ticketid,
            'userid' => $user->userid,
            'content' => $content,
            'updatetypeid' => 1
            ]);

        $activity = $user->name . ' reopened Ticket #' . $ticketid;
        logactivity($user->userid, 39, $activity, $now); // activitytypeid 39 - Reopened Ticket

        $qids = getqids($ticket);
        $action = 'reopen';
        checkfornotification($action, $qids, $ticket);

        return 'success';
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
}
