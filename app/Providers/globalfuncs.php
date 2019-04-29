<?php 
use App\Queue;
use App\User;
use App\Notification;
use App\Description;
use App\Category;
use App\Priority;
use App\Status;
use App\TicketDescription;
use App\Mail\NotificationEmail;
use App\Jobs\SendNotification;


function getUserPerms($userid)
{
    $userperms = array();

    $userperms = DB::select(
                    DB::raw('WITH active_groups as (
                                    SELECT 
                                        groupid
                                    FROM
                                        groups
                                    WHERE 
                                        active = 1
                                ),
                                active_roles as (
                                    SELECT
                                        roleid
                                    FROM
                                        roles
                                    WHERE
                                        active = 1 
                                ),
                                user_groups as (
                                    SELECT
                                        groupid
                                    FROM
                                        usergroups
                                    WHERE
                                        userid = :userid
                                    AND
                                        groupid in (SELECT groupid FROM active_groups)
                                ),
                                group_roles as (
                                    SELECT
                                        roleid
                                    FROM
                                        grouproles
                                    WHERE
                                        groupid in (SELECT groupid FROM user_groups)
                                    AND
                                        groupid in (SELECT groupid FROM active_groups)
                                    AND 
                                        roleid in (SELECT roleid FROM active_roles)
                                ),
                                user_roles as (
                                    SELECT
                                        roleid
                                    FROM
                                        userroles
                                    WHERE
                                        userid = :userid
                                    AND
                                        roleid not in (SELECT roleid FROM group_roles)
                                    AND
                                        roleid in (SELECT roleid FROM active_roles)
                                ),
                                user_perms as (
                                    SELECT
                                        permissionid
                                    FROM
                                        securities
                                    WHERE
                                        roleid in (SELECT roleid FROM group_roles)
                                    OR
                                        roleid in (SELECT roleid FROM user_roles)
                                )
                                SELECT
                                    DISTINCT(up.permissionid) 
                                    ,p.permission
                                FROM 
                                    user_perms up
                                LEFT JOIN permissions p
                                ON up.permissionid = p.permissionid'), 
                    array('userid' => $userid));

    $perms = array();

    foreach($userperms as $up)
    {
        array_push($perms, $up->permission);
    }

    return $perms;
}

function getOpenTicketsForTech($techid)
{
    $techqueues = getUsersQueues($techid);
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
                    ->select('u1.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'tickets.masterticket', 'tickettechs.techid', 'u2.name AS tech', 'ticketqueues.queueid', 'queues.queuename')  
                    ->where(function($query) use($techid)
                    {
                        $query->where('tickettechs.techid', $techid)
                                ->orWhere('tickettechs.techid', null); 
                    }) 
                    ->where(function($query) use($techqueues, $techid)
                    {
                        $query->whereIn('ticketqueues.queueid', $techqueues) 
                                ->orWhere('tickettechs.techid', $techid); 
                    })                                                    
                    ->whereNull('tickets.parentticketid')   
                    ->whereNotIn('tickets.statusid', [3, 4])                                                           
                    ->orderBy('priority', 'desc')
                    ->get();

    return $techtickets;
}

function getAllTicketsForTech($techid)
{
    $techqueues = getUsersQueues($techid);
    $alltechtickets = DB::table('tickets')
                    ->leftJoin('users AS u1', 'tickets.requestorid', '=', 'u1.userid')
                    ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
                    ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
                    ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
                    ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
                    ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
                    ->leftJoin('users AS u2', 'tickettechs.techid', '=', 'u2.userid')
                    ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
                    ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
                    ->select('u1.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'tickets.masterticket', 'tickettechs.techid', 'u2.name AS tech', 'ticketqueues.queueid', 'queues.queuename')  
                    ->where(function($query) use($techid)
                    {
                        $query->where('tickettechs.techid', $techid)
                                ->orWhere('tickettechs.techid', null); 
                    }) 
                    ->whereIn('ticketqueues.queueid', $techqueues)                                
                    ->whereNull('tickets.parentticketid')   
                    ->where('tickets.statusid', '!=', 4)                                                           
                    ->orderBy('priority', 'desc')
                    ->get();

    return $alltechtickets;
}

function getTicketTechs()
{
    $tickettechs = DB::table('tickettechs')
                    ->leftJoin('users', 'users.userid','=','tickettechs.techid')
                    ->leftJoin('tickets', 'tickets.ticketid', '=', 'tickettechs.ticketid')
                    ->select('users.userid', 'users.name', 'users.department', 'users.email', 'users.phoneNumber', 'users.faxnumber', 'users.mobilephone', 'users.location', 'tickets.ticketid', 'tickettechs.techid')
                    ->get();
    return $tickettechs;
}

function getAllTix()
{
    $alltix = DB::table('tickets')
            ->leftJoin('users', 'tickets.requestorid', '=', 'users.userid')
            ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
            ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
            ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
            ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
            ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
            ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
            ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
            ->select('users.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'tickets.masterticket', 'ticketqueues.queueid', 'tickettechs.techid', 'queues.queuename')
            ->whereNull('tickets.parentticketid')
            ->where('tickets.statusid', '!=', 4 )
            ->orderBy('priority', 'desc')
            ->get();
    return $alltix;
}

function getAllOpenTix()
{
    $allopentix = DB::table('tickets')
                ->leftJoin('users', 'tickets.requestorid', '=', 'users.userid')
                ->leftJoin('ticketdescriptions', 'tickets.descriptionid', '=', 'ticketdescriptions.descriptionid')
                ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
                ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
                ->leftJoin('priorities', 'tickets.priorityid', '=', 'priorities.priorityid')
                ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
                ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
                ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
                ->select('users.name AS requestor', 'ticketdescriptions.description', 'categories.category', 'statuses.status', 'priorities.priority', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickets.updated_at', 'tickets.masterticket', 'ticketqueues.queueid', 'tickettechs.techid', 'queues.queuename')
                ->whereNull('tickets.parentticketid')
                ->whereNotIn('tickets.statusid', [3, 4] )
                ->orderBy('priority', 'desc')
                ->get();
    return $allopentix;
}

function getLDAPsyncsettings()
{
    $enabled = DB::table('settings')
                    ->select('value')
                    ->where('setting', 'LDAP_SYNC_ENABLED')
                    ->get();

    $interval = DB::table('settings')
                    ->select('value')
                    ->where('setting', 'LDAP_SYNC_INTERVAL')
                    ->get();

    $lastrun = DB::table('settings')
                    ->select('value')
                    ->where('setting', 'LDAP_SYNC_LAST_RUN')
                    ->get();

    $syncenabled = null;
    foreach($enabled as $en)
    {
        $syncenabled = $en->value;
    }

    $syncinterval = null;
    foreach($interval as $int)
    {
        $syncinterval = $int->value;
    } 

    $synclastrun = null;
    foreach($lastrun as $lr)
    {
        $synclastrun = $lr->value;
    }

    if($synclastrun == '' || $synclastrun == null)
    {
        $synclastrun = 1;
    }
    
    $synclastrundate = date('m-d-Y H:i:s', $synclastrun);

    $return = array('enabled' => $syncenabled, 'interval' => $syncinterval, 'lastrun' => $synclastrun, 'lastrundate' => $synclastrundate);

    return $return;
}

function setManagerIds()
{ 
    try
    {
        $managers = DB::table('users')
                    ->select('manager')
                    ->where('manager', '!=', 'null')
                    ->get();
        $managerarray = array();
        foreach($managers as $mgr)
        {
            $managername = '';
            if(strpos($mgr->manager, 'CN=') !== false)
            {
                $managername = str_replace('CN=', '', explode(',', $mgr->manager)[0]);
            }
            else
            {
                $managername = null;
            }

            if(!in_array($managername, $managerarray)  && $managername !== null)
            {
                array_push($managerarray, $managername);
            }
        }


        for($i=0; $i<count($managerarray); $i++)
        {
            $managerid = DB::table('users')
                    ->select('userid')
                    ->where('name', '=', $managerarray[$i])
                    ->get();

            DB::table('users')
                ->where('manager', 'like', '%'.$managerarray[$i].'%')
                ->orWhere('manager', $managerarray[$i])
                ->update(['manager' => $managerid[0]->userid]);
        }   

    }    
    catch(\Exception $ex)
    {
        logger()->error($ex->getMessage());
        return;
    }

    return;
}

function getQueueTechs($queues)
{
    $techs = array();
    $idcheck = array();
    try
    {  
        foreach($queues as $q)
        {
            $queue = Queue::find($q['queueid']);
            $options = $queue->options()->get();

            if($options->contains('optionname', 'Select Tech'))
            {
                $groups = $queue->groups()->get();
                $users = $queue->users()->get();

                foreach($groups as $group)
                {
                    if($group->active == 1)
                    {
                        $grouptechs = $group->users()->get();
                        $a = 0;

                        for($i=0; $i<count($grouptechs); $i++)
                        {
                            if(!in_array($grouptechs[$i]->userid, $idcheck))
                            {
                                if($grouptechs[$i]->optedin == 1 && $grouptechs[$i]->active == 1)
                                {
                                    $techs[$a]['userid'] = $grouptechs[$i]->userid;
                                    $techs[$a]['name'] = $grouptechs[$i]->name;

                                    array_push($idcheck, $techs[$a]['userid']);

                                    $a++;
                                } 
                            }                   
                        }
                    }
                }

                // start adding users to techs from $users where we left off with $grouptechs
                $s = count($techs);

                for($i=0; $i<count($users); $i++)
                {
                    if(!in_array($users[$i]->userid, $idcheck))
                    { 
                        if($users[$i]->optedin == 1 && $users[$i]->active == 1)
                        {
                            $techs[$s]['userid'] = $users[$i]->userid;
                            $techs[$s]['name'] = $users[$i]->name;
                            $s++;
                        }    
                    }
                }
            }
        }
    }
    catch(\Exception $ex)
    {
        return $ex->getMessage();
    }
    
    if(count($techs) > 0)
    {
        $resp = $techs;
    }
    else
    {
        $resp = "No Techs Available";
    }
    return $resp;
}

function getUsersQueues($userid)
{
    $userqueues = array();

    $userqueues = DB::select(
                    DB::raw('WITH active_groups as (
                                    SELECT 
                                        groupid
                                    FROM
                                        groups
                                    WHERE 
                                        active = 1
                                ),
                                active_queues as (
                                    SELECT 
                                        queueid
                                    FROM
                                        queues
                                    WHERE
                                        active =1
                                    
                                ),
                                user_groups as (
                                    SELECT
                                        groupid
                                    FROM
                                        usergroups
                                    WHERE
                                        userid = :userid
                                    AND
                                        groupid in (SELECT groupid FROM active_groups)
                                ),
                                group_queues as (
                                    SELECT
                                        queueid
                                    FROM
                                        queuegroups
                                    WHERE
                                        groupid in (SELECT groupid FROM user_groups)
                                    AND
                                        groupid in (SELECT groupid FROM active_groups)
                                    AND 
                                        queueid in (SELECT queueid FROM active_queues)
                                ),
                                user_queues as (
                                    SELECT
                                        queueid
                                    FROM
                                        queueusers
                                    WHERE
                                        userid = :userid
                                    AND
                                        queueid not in (SELECT queueid FROM group_queues)
                                    AND
                                        queueid in (SELECT queueid FROM active_queues)
                                ),
                                queue_info as (
                                    SELECT
                                        queueid
                                    FROM
                                        queues
                                    WHERE
                                        queueid in (SELECT queueid FROM group_queues)
                                    OR
                                        queueid in (SELECT queueid FROM user_queues)                                        
                                )
                                SELECT
                                     queueid    
                                FROM 
                                    queue_info
                                ORDER BY
                                    queueid'), 
                    array('userid' => $userid));

    $queues = array();

    foreach($userqueues as $uq)
    {
        array_push($queues, $uq->queueid);
    }

    return $queues;
}

function getTicketsForRequestor($requestorid)
{
    $requestortickets = DB::select(
                DB::raw('SELECT
                            DISTINCT ON (tickets.ticketid)
                            tickets.ticketid,
                            u1.name AS requestor,
                            ticketdescriptions.description, 
                            categories.category, 
                            statuses.status, 
                            priorities.priority,
                            tickets.requestorid,    
                            tickets.dateresolved, 
                            tickets.created_at, 
                            tickets.updated_at,
                            tickets.parentticketid,
                            tickets.masterticket,
                            tickettechs.techid,
                            u2.name as tech,
                            ticketqueues.queueid
                        FROM
                            tickets
                        LEFT JOIN users u1
                        ON tickets.requestorid = u1.userid
                        LEFT JOIN ticketdescriptions
                        ON tickets.descriptionid = ticketdescriptions.descriptionid
                        LEFT JOIN categories 
                        ON tickets.categoryid = categories.categoryid
                        LEFT JOIN statuses 
                        ON tickets.statusid = statuses.statusid
                        LEFT JOIN priorities 
                        ON tickets.priorityid = priorities.priorityid
                        LEFT JOIN tickettechs
                        ON tickets.ticketid = tickettechs.ticketid
                        LEFT JOIN users u2
                        ON tickettechs.techid = u2.userid
                        LEFT JOIN ticketqueues
                        ON tickets.ticketid =  ticketqueues.ticketid

                        WHERE
                            tickets.requestorid = :requestorid 
                        AND
                            tickets.parentticketid is NULL
                        ORDER BY
                            tickets.ticketid'),
                array('requestorid' => $requestorid));

    return $requestortickets;
}


function getuserinfo($userid)
{
    $userinfo = User::find($userid);
    return $userinfo;
}

function logactivity($userid, $activitytypeid, $activity, $now)
{
    DB::table('activitylog')
        ->insert(
            [
                'userid' => $userid, 
                'activitytypeid' => $activitytypeid, 
                'activity' => $activity,
                'created_at' => $now,
            ]);
    return;
}

function logsettingsupdate($userid, $setting, $old, $new, $now)
{
    DB::table('settingsupdates')
        ->insert(
            [
                'updatedby' => intval($userid), 
                'setting' => $setting, 
                'oldvalue' => $old,
                'newvalue' => $new,
                'created_at' => $now,
            ]);
    return;
}


// Notification Functions

function checkfornotification($action, $qids, $ticket)
{
    try
    {
        // Get notifications for this action
        $notificationsforaction = DB::table('notifications')
                                        ->select('*')
                                        ->where([['triggeraction', $action],['active', 1]])
                                        ->get();

        // Check if the notification is tied to a specific queue. 
        foreach($notificationsforaction as $not)
        {
            $notification = Notification::find($not->notificationid);
            $qs = $notification->queues()->get();
            $send = false;
            
            foreach($qids as $qid)
            {
                // If a qid is tied to this notification or if this notification has no queues tied to it, send the notification
                if($qs->contains('queueid', $qid) || count($qs) == 0)
                {
                    $send = true;
                }
            }

            if($send)
            {
                sendnotification($notification, $ticket, $action);
            }
        }    
    }
    catch(\Exception $ex)
    {
        return $ex->getMessage();
    }
}


function sendnotification($notification, $ticket, $action)
{ 
    // pull any Trigger Filter Expressions for this notification
    $expressions = DB::table('filterexpressions')
                        ->select('data', 'operator', 'criteria')
                        ->where('notificationid', $notification->notificationid)
                        ->get();
    $send = false;
    if(count($expressions) > 0)
    { 
        // we have TFEs, let's evaluate them
        if(evaluateTFE($expressions, $ticket))
        {
            // evaulation returned true, so we're good to send
            $send = true;
        }
    }
    else
    { 
        // there are no TFE's for this notification, so we can just send
        $send = true;
    }

    if($send)
    { 
        // replace variables in notification with the proper data
        $msg = constructmessage($notification->notification, $ticket);

        // assign recipients to an array
        if(substr_count($notification->recipient, ',') > 0)
        {   
            $recipients = str_ireplace(' ', '', $notification->recipient);       
            $recipients = explode(',', $recipients); 
        }
        else
        { 
            $recipients = array($notification->recipient);
        }

        $toaddresses = array();

        // get recipient email addresses

        for($i=0; $i<count($recipients); $i++)
        {   
            // check for email addresses added to recipient field
            if(filter_var($recipients[$i], FILTER_VALIDATE_EMAIL))
            {
                array_push($toaddresses, $recipients[$i]);
            }
        }

        if(in_array('%requestor%', $recipients))
        {
            $requestor = User::find($ticket->requestorid);

            if(!in_array($requestor->email, $toaddresses))
            {
                array_push($toaddresses, $requestor->email);
            }            
        }

        if(in_array('%tech%', $recipients))
        { 
            $techs = $ticket->techs()->get();

            if(count($techs) > 0)
            {
                foreach($techs as $tech)
                {
                    if(!in_array($tech->email, $toaddresses))
                    {
                        array_push($toaddresses, $tech->email);
                    }
                }  
            }              
        }

        if(in_array('%queue_techs%', $recipients))
        { 
            $queues = $ticket->queues()->get();
            $techs = array();

            for($i=0; $i<count($queues); $i++)
            {
                $qtechs = getTechsForQueue($queues[$i]->queueid);

                if(count($qtechs) > 0)
                {
                    for($j=0; $j<count($qtechs); $j++)
                    { 
                        array_push($techs, $qtechs[$j]);
                    }     
                }                           
            }

            for($i=0; $i<count($techs); $i++)
            { 
                if(!in_array($techs[$i], $toaddresses))
                { 
                    array_push($toaddresses, $techs[$i]);
                }
            }  
        }

        $techs = $ticket->techs()->get();
        $replyTo = array();

        if(count($techs) > 0)
        {
            foreach($techs as $tech)
            {
                if(!in_array($tech->email, $replyTo))
                {
                    array_push($replyTo, $tech->email);
                }
            }  
        }            

        for($i=0;$i<count($toaddresses);$i++)
        {
            try
            { 
                $to = $toaddresses[$i];

                if($action == 'submit')
                {
                    $subject = 'New Helpdesk Ticket Submitted: #' . $ticket->ticketid;
                }
                elseif($action == 'resolution')
                {
                    $subject = 'Helpdesk Ticket #' . $ticket->ticketid . ' Has Been Resolved.';
                }
                else
                {
                    $subject = 'Update to Helpdesk Ticket #' . $ticket->ticketid;
                }

                dispatch(new SendNotification($to, $subject, $msg, $replyTo));
            }    
            catch(\Exception $ex)
            {
                logger()->error($ex->getMessage());
                echo $ex->getMessage();
                return;
            }
        }
    }    
}

function evaluateTFE($expressions, $ticket)
{
    $booleanarray = array();

    for($i=0;$i<count($expressions);$i++)
    { 
        $data = strtolower($expressions[$i]->data);
        $operator = $expressions[$i]->operator;
        $criteria = $expressions[$i]->criteria;        

        switch ($data) {
            case 'ticket id':
                $ticketid = $ticket->ticketid;
                $boolean = evaluate($ticketid, $operator, $criteria);
                array_push($booleanarray, $boolean);
                break;
            case 'requestor':
                $requestor = User::find($ticket->requestorid);
                $boolean = evaluate(strtolower($requestor->name), $operator, strtolower($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'tech':
                $techs = $ticket->techs()->get();
                foreach($techs as $tech)
                {
                    $boolean = evaluate(strtolower($tech->name), $operator, strtolower($criteria));
                    array_push($booleanarray, $boolean);
                }                
                break;
            case 'description':
                $desc = TicketDescription::find($ticket->descriptionid);
                $boolean = evaluate(strtolower($desc->description), $operator, strtolower($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'category': 
                $cat = Category::find($ticket->categoryid);
                $boolean = evaluate(strtolower($cat->category), $operator, strtolower($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'priority':
                $priority = Priority::find($ticket->priorityid);
                $boolean = evaluate($priority->priority, $operator, $criteria);
                array_push($booleanarray, $boolean);
                break;
            case 'status':
                $status = Status::find($ticket->statusid);
                $boolean = evaluate(strtolower($status->status), $operator, strtolower($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'date entered':
                $dateentered = $ticket->created_at;
                $boolean = evaluate(strtotime($dateentered), $operator, strtotime($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'date updated':
                $dateupdated = $ticket->updated_at;
                $boolean = evaluate(strtotime($dateupdated), $operator, strtotime($criteria));
                array_push($booleanarray, $boolean);
                break;
            case 'date resolved':
                $dateresolved = $ticket->dateresolved;
                $boolean = evaluate(strtotime($dateresolved), $operator, strtotime($criteria));
                array_push($booleanarray, $boolean);
                break;
        }

        if(in_array(0, $booleanarray))
        {
            return false;
        }
    }
    return true;       
}

function evaluate($data, $operator, $criteria)
{

    switch ($operator) {
        case '=':
            return $data == $criteria ? 1 : 0;
        case '!=':
            return $data !== $criteria ? 1 : 0;
        case '>':
            return $data > $criteria ? 1 : 0;
        case '<':
            return $data < $criteria ? 1 : 0;
        case '<=':
            return $data <= $criteria ? 1 : 0;
        case '>=':
            return $data >= $criteria ? 1 : 0;
        case '~':
            return substr_count($data, $criteria) > 0 ? 1 : 0;
        case '!~':
            return substr_count($data, $criteria) > 0 ? 0 : 1;
    }
}

function getTechsForQueue($queueid)
{
    $techs = array();

    $queueusers = DB::table('queueusers')
                        ->select('userid')
                        ->where('queueid', $queueid)
                        ->get();

    if(count($queueusers) > 0)
    {
        for($i=0; $i<count($queueusers); $i++)
        {
            $tech = User::find($queueusers[$i]->userid);
            array_push($techs, $tech->email);
        }
    }

    $queuegroups = DB::table('queuegroups')
                        ->select('groupid')
                        ->where('queueid', $queueid)
                        ->get();

    if(count($queuegroups) > 0)
    {
        for($i=0; $i<count($queuegroups); $i++)
        {
            $groupusers = DB::table('usergroups')
                            ->select('userid')
                            ->where('groupid', $queuegroups[$i]->groupid)
                            ->get();

            if(count($groupusers) > 0)
            {
                for($i=0; $i<count($groupusers); $i++)
                {
                    $tech = User::find($groupusers[$i]->userid);
                    array_push($techs, $tech->email);
                } 
            }
        }
    }

    return $techs;
}

function constructmessage($msg, $ticket)
{
    if(substr_count($msg, '%today%') > 0)
    {
        $today = date('m-d-Y');
        $msg = str_ireplace('%today%', $today, $msg);
    }

    if(substr_count($msg, '%requestor%') > 0)
    { 
        $requestor = User::find($ticket->requestorid);
        $name = $requestor->name;
        $msg = str_ireplace('%requestor%', $name, $msg);
    }

    if(substr_count($msg, '%email%') > 0)
    {
        $requestor = User::find($ticket->requestorid);
        $msg = str_ireplace('%email%', $requestor->email, $msg);
    }

    if(substr_count($msg, '%id%') > 0)
    {
        $msg = str_ireplace('%id%', $ticket->ticketid, $msg);
    }

    if(substr_count($msg, '%status%') > 0)
    {
        $status = Status::find($ticket->statusid);
        $msg = str_ireplace('%status%', $status->status, $msg);
    }

    if(substr_count($msg, '%category%') > 0)
    {
        $cat = Category::find($ticket->categoryid);
        $msg = str_ireplace('%category%', $cat->category, $msg);
    }

    if(substr_count($msg, '%queue%') > 0)
    {
        $qs = $ticket->queues()->get();
        $queue = '';
        foreach($qs as $q)
        {
            $queue .= ' '. $q->queuename;
        }
        $msg = str_ireplace('%queue%', $queue, $msg);
    }

    if(substr_count($msg, '%priority%') > 0)
    {
        $priority = Priority::find($ticket->priorityid);
        $msg = str_ireplace('%priority%', $priority->priority, $msg);
    }

    if(substr_count($msg, '%submit_date%') > 0)
    {
        $date = $ticket->created_at;
        $msg = str_ireplace('%submit_date%', $date, $msg);
    }

    if(substr_count($msg, '%last_update%') > 0)
    {
        $date = $ticket->updated_at;
        $msg = str_ireplace('%last_update%', $date, $msg);
    }

    if(substr_count($msg, '%description%') > 0)
    {
        $desc = TicketDescription::find($ticket->descriptionid);
        $msg = str_ireplace('%description%', $desc->description, $msg);
    }

    if(substr_count($msg, '%tech%') > 0)
    {
        $techs = $ticket->techs()->get();

        if(count($techs) > 0)
        {
            $names = '';
            foreach($techs as $tech)
            {
                $names .= ' ' . $tech->name;
            }
            $msg = str_ireplace('%tech%', $names, $msg);
        }
        else
        {
            $msg = str_ireplace('%tech%', 'No Tech has been assigned to this ticket yet', $msg);
        }

    }

    if(substr_count($msg, '%int_notes%') > 0)
    {
        $intnotes = DB::table('ticketupdates')
                        ->select('content')
                        ->where([['ticketid', $ticket->ticketid], ['updatetypeid', 6]])
                        ->get();
        $notes = '';             
        if(count($intnotes) > 0)
        {
            foreach($intnotes as $note)
            {
                $notes .= ' ' . $note->content;
            } 

            $msg = str_ireplace('%int_notes%', $notes, $msg); 
        }
        else
        {
            $msg = str_ireplace('%int_notes%', 'This ticket has no internal notes', $msg);
        }     
        
    }

    if(substr_count($msg, '%last_int_note%') > 0)
    {
        $lastintnote = DB::table('ticketupdates')                        
                        ->where([['ticketid', $ticket->ticketid], ['updatetypeid', 6]])
                        ->max('ticketupdateid');
                       
        if($lastintnote !== null)
        { 
            $note = DB::table('ticketupdates')
                    ->select('content')
                    ->where([['ticketupdateid', $lastintnote], ['updatetypeid', 6]])
                    ->get();

            $msg = str_ireplace('%last_int_note%', $note[0]->content, $msg);
        }
        else
        {
            $msg = str_ireplace('%last_int_note%', 'This ticket has no internal notes', $msg);
        }        
    }

    if(substr_count($msg, '%notes%') > 0)
    {
        $intnotes = DB::table('ticketupdates')
                        ->select('content')
                        ->where([['ticketid', $ticket->ticketid], ['updatetypeid', 5]])
                        ->get();
        $notes = '';             
        if(count($intnotes) > 0)
        {
            foreach($intnotes as $note)
            {
                $content = str_ireplace('public', '', $note->content);
                $notes .= "<br/>" . $content;
            } 

            $msg = str_ireplace('%notes%', $notes, $msg); 
        }
        else
        {
            $msg = str_ireplace('%notes%', 'This ticket has no internal notes', $msg);
        }     
        
    }

    if(substr_count($msg, '%last_note%') > 0)
    {
        $lastnote = DB::table('ticketupdates')                   
                        ->where([['ticketid', $ticket->ticketid], ['updatetypeid', 5]])
                        ->max('ticketupdateid');
    
        if($lastnote !== null)
        { 
            $note = DB::table('ticketupdates')
                    ->select('content')
                    ->where([['ticketupdateid', $lastnote], ['updatetypeid', 5]])
                    ->get();

            $content = str_ireplace('public', '', $note[0]->content);
            $msg = str_ireplace('%last_note%', $content, $msg);
        }                
        else
        {
            $msg = str_ireplace('%last_note%', 'This ticket has no notes', $msg);
        }
    }

    return $msg;
}

function getqids($ticket)
{
    $queues = $ticket->queues()->get();
    $qids = array();

    if(count($queues) > 0)
    {
        foreach($queues as $q)
        {
            array_push($qids, $q->queueid);
        }
    }
    return $qids;
}

// End Notification Functions



function assignDefaultGroup()
{
    $userstoassign = DB::select(
                            DB::raw(
                                'SELECT
                                    userid
                                FROM
                                    users
                                WHERE
                                    userid not in (SELECT userid FROM usergroups)'
                            ));
    if(count($userstoassign) > 0)
    {
        $defaultgroup = DB::table('settings')
                                ->select('value')
                                ->where('setting', 'DEFAULT_GROUP')
                                ->get();

        for($i=0; $i<count($userstoassign); $i++)
        {
            $user = User::find($userstoassign[$i]->userid);
            $user->groups()->attach($defaultgroup[0]->value);
        }
    }
}

function seconds2human($ss) 
{
    $s = $ss%60;
    $m = floor(($ss%3600)/60);
    $h = floor(($ss%86400)/3600);
    $d = floor(($ss%2592000)/86400);
    // $M = floor($ss/2592000);

    return "$d days $h:$m:$s";
}

function logoptin($techid)
{
    $now = date('Y-m-d H:i:s');

    DB::table('optlog')
        ->insert(
            [
                'techid' => $techid, 
                'optin' => $now, 
            ]);
    return;
}

function logoptout($techid)
{
    $now = date('Y-m-d H:i:s');

    $log = DB::table('optlog')
                ->select('optid', 'optin')
                ->where('techid', $techid)
                ->whereNull('optout')
                ->get();

    $total = strtotime($now) - strtotime($log[0]->optin);

    DB::table('optlog')
        ->where('optid', $log[0]->optid)
        ->update(
            [
                'optout' => $now, 
                'total' => $total,
            ]);
    return;
}

function formatphone($phone)
{
    
    if(!isset($phone{3})) 
    { 
        return ''; 
    }

    // strip out everything but numbers 
    $phone = preg_replace("/[^0-9]/", "", $phone);

    $length = strlen($phone);

    switch($length) 
    {
        case 7:
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2) $3-$4", $phone);
            break;
        default:
            return $phone;
            break;
    }
}
