<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\User;
use App\Queue;
use DB;
use Auth;

class ReportsController extends Controller
{
    public function techPerfomance(Request $request)
    {
    	$startdt = $request->input('TPRstartdate');
    	$enddt = $request->input('TPRenddate');
    	$techs = $request->input('TPRuserselect');
    	$resp = array();
    	$techinfoarray = array();
		$totals = array();
		$now = date('Y-m-d H:i:s');
		$start = date('Y-m-d', strtotime($startdt));
		$end = date('Y-m-d', strtotime($enddt));

		$start .= ' 00:00:01';
		$end .= ' 23:59:59';

		$totaltickets = $totalassigned = $totalclosed = $totalescalated = $totalavgclosetime = $totalpercentcovered = $totalrecords = $totaltimeoptedin = 0;

		$ticketsinrange = DB::select(
								DB::raw(
										"SELECT		
											COUNT(ticketid) as totaltix
										FROM
											tickets
										WHERE
											created_at >= :start
										AND 
											created_at <= :end
										AND
											statusid != 4
										AND
											parentticketid is null;"												
										),
								array('start' => $start, 'end' => $end));

		if($ticketsinrange[0]->totaltix > 0)
		{

			$totaltickets = $ticketsinrange[0]->totaltix;
		}
		else
		{
			$totaltickets = 0;
		}

    	for($i=0;$i<count($techs);$i++)
    	{
    		$userid = $techs[$i];
    		$tech = User::find($userid);    		

    		try 
    		{
	    		$info = DB::select(
	    					DB::raw("WITH techs_tix AS (
												SELECT
													ticketid
												FROM
													tickettechs
												WHERE
													created_at >= :start
												AND 
													created_at <= :end
												AND
													ticketid not in (select ticketid from tickets where statusid = 4 and parentticketid is not null)
												AND
													techid = :userid
											),
											tix_closed AS (
												SELECT 
													ticketid
												FROM
													tickets
												WHERE
													ticketid in (select ticketid from tickettechs where techid = :userid)
												AND
													dateresolved >= :start
												AND 
													dateresolved <= :end

											),
											tix_escalated AS (
												SELECT
													ticketid
												FROM
													ticketupdates
												WHERE
													userid = :userid
												AND
													updatetypeid = 1
												AND
													content like '% Escalated'
												AND
													created_at >= :start
												AND 
													created_at <= :end
											),
											avg_close AS (
												SELECT
													timetoclose,
													ticketid
												FROM
													tickets
												WHERE
													ticketid in (select ticketid from tix_closed)
											),
											time_in AS (
												SELECT
													CASE WHEN optout is null THEN 
													(DATE_PART('hour', now()::time - optin::time) * 60 +
               										DATE_PART('minute', now()::time - optin::time)) * 60 +
               										DATE_PART('second', now()::time - optin::time) ELSE total END as timeoptedin,
													optin,
													optout
												FROM
													optlog
												WHERE
													techid = :userid
												AND
													optin >= :start
												AND 
													optin <= :end
												GROUP BY
													CASE WHEN optout is null THEN 
													(DATE_PART('hour', now()::time - optin::time) * 60 +
               										DATE_PART('minute', now()::time - optin::time)) * 60 +
               										DATE_PART('second', now()::time - optin::time) ELSE total END,
													optin,
													optout
											)

											SELECT											
												(SELECT COALESCE(COUNT(tt.ticketid), 0) FROM techs_tix tt) AS totalassigned,
												(SELECT COALESCE(COUNT(tc.ticketid), 0) FROM tix_closed tc) AS ticketsclosed,
												(SELECT COALESCE(COUNT(te.ticketid), 0) FROM tix_escalated te) AS ticketsescalated,
												(SELECT COALESCE(ROUND(AVG(ac.timetoclose)::numeric, 2), 0) FROM avg_close ac) AS averageclosetime,
												ti.timeoptedin,
												ti.optin,
												ti.optout

											FROM
												users u	
											
											LEFT JOIN time_in ti
											ON 1 = 1	

											WHERE
												u.userid = :userid;"
									), 
	    					array('userid' => $userid, 'start' => $start, 'end' => $end));
	    	}
	        catch(\Exception $ex)
	        {
	            return $ex->getMessage();
	        }

    		if(count($info) > 0)
    		{
    			$techinfoarray['techname'] = $tech->name;
	    		$techinfoarray['ticketsassigned'] = $info[0]->totalassigned;
	    		$techinfoarray['ticketsclosed'] = $info[0]->ticketsclosed;
	    		$techinfoarray['ticketsescalated'] = $info[0]->ticketsescalated;

	    		if($totaltickets > 0)
	    		{
	    			$percentcovered = round((intval($info[0]->totalassigned)/intval($totaltickets)) * 100, 2);
	    		}
	    		else
	    		{
	    			$percentcovered = round((intval($info[0]->totalassigned)) * 100, 2);
	    		}

	    		$techinfoarray['percentcovered'] = $percentcovered;
	    		$techinfoarray['avgclosetime'] = seconds2human($info[0]->averageclosetime);
				$techinfoarray['timeoptedin'] = 0;
	    		
	    		for($j=0; $j<count($info); $j++)
	    		{   			
		    		if($info[$j]->timeoptedin !== null)
		    		{
		    			$techinfoarray['timeoptedin'] += $info[$j]->timeoptedin;
		    		}
	    		}
				
				$totaltimeoptedin += $techinfoarray['timeoptedin'];
	    		$techinfoarray['timeoptedin'] = seconds2human($techinfoarray['timeoptedin']);

	    		// Totals
	    		$totalassigned += $info[0]->totalassigned;
	    		$totalclosed += $info[0]->ticketsclosed;
	    		$totalescalated += $info[0]->ticketsescalated;   		

	    		if($info[0]->averageclosetime !== null)
	    		{
	    			$totalavgclosetime += $info[0]->averageclosetime;
	    			$totalrecords++;
	    		}
	    		
	    		$totalpercentcovered += $percentcovered;

	    		array_push($resp, $techinfoarray);
    		}
    	}    	
			
			if($totalrecords < 1)
			{
				$totalrecords = 1;
			}

			$totals['totaltickets'] = $totaltickets;
			$totals['totalassigned'] = $totalassigned;
			$totals['totalclosed'] = $totalclosed;
			$totals['totalescalated'] = $totalescalated;
			$totals['totalavgclosetime'] = seconds2human($totalavgclosetime/$totalrecords);
			$totals['totalpercentcovered'] = $totalpercentcovered;
			$totals['totaltimeoptedin'] = seconds2human($totaltimeoptedin);

			array_push($resp, $totals);

    	return $resp;
    }


    public function catClosureTime(Request $request)
    {
    	$startdt = $request->input('CCTRstartdate');
    	$enddt = $request->input('CCTRenddate');
    	$cats = Category::all();
    	$resp = array();

    	$start = date('Y-m-d', strtotime($startdt));
		$end = date('Y-m-d', strtotime($enddt));
		
		$start .= ' 00:00:01';
		$end .= ' 23:59:59';

    	foreach($cats as $cat)
    	{
    		$catinfoarray = array();

    		$info = DB::table('tickets')
    					->select(DB::raw('COUNT(tickets.ticketid) as numberoftix, ROUND(AVG(tickets.timetoclose)::numeric,2) as averagetimetoclose'))
    					->where([['tickets.categoryid', $cat->categoryid], ['tickets.dateresolved', '>=', $start],['tickets.dateresolved', '<=', $end], ['tickets.statusid', '!=', 4]])
    					->whereNull('tickets.parentticketid')
    					->get();

    		$catinfoarray['category'] = $cat->category;
    		$catinfoarray['avgtimetoclose'] = seconds2human($info[0]->averagetimetoclose);
    		$catinfoarray['totaltickets'] = $info[0]->numberoftix;

    		array_push($resp, $catinfoarray);
    	}

    	return $resp;
    }

    public function frozenTickets(Request $request)
    {
    	$startdt = $request->input('FTRstartdate');
    	$enddt = $request->input('FTRenddate');
    	$ticketinfoarray = array();
    	$resp = array();

    	$start = date('Y-m-d', strtotime($startdt));
		$end = date('Y-m-d', strtotime($enddt));

		$start .= ' 00:00:01';
		$end .= ' 23:59:59';

    	$info = DB::select(
    				DB::raw("WITH tickets_in_range AS (
									SELECT
										ticketid
									FROM
										tickets
									WHERE
										created_at >= :start
									AND 
										created_at <= :end 	
									AND
										statusid != 4	
									AND
										parentticketid is null
								),
								frozen_tix AS (
									SELECT 
										ticketid,
										frozen,
										thawed
									FROM
										ticketfreeze
									WHERE
										ticketid in (SELECT ticketid FROM tickets_in_range)
								),
								ticket_info AS (
									SELECT
										u.name AS requestor,
										c.category,
										t.ticketid
									FROM
										tickets t
									LEFT JOIN users u
									ON t.requestorid = u.userid
									LEFT JOIN categories c
									ON t.categoryid = c.categoryid									
									WHERE
										ticketid in (SELECT ticketid FROM frozen_tix)									
								)

								SELECT
									ft.ticketid,
									ft.frozen,
									ft.thawed,
									ti.requestor,
									ti.category

								FROM 
									frozen_tix ft
									
								LEFT JOIN ticket_info ti
								ON ft.ticketid = ti.ticketid;"
    			),
    				array('start' => $start, 'end' => $end)); 

    	foreach($info as $in)
    	{ 
    		$ticketinfoarray['ticketid'] = $in->ticketid;
    		$ticketinfoarray['requestor'] = $in->requestor;
    		$ticketinfoarray['category'] = $in->category;
    		$ticketinfoarray['frozendt'] = $in->frozen;

    		if($in->thawed !== null)
    		{
    			$ticketinfoarray['timefrozen'] = seconds2human(strtotime($in->thawed) - strtotime($in->frozen));
    		}
    		else
    		{
    			$now = date('Y-m-d H:i:s');
    			$ticketinfoarray['timefrozen'] = seconds2human(strtotime($now) - strtotime($in->frozen));
    		}   		

    		array_push($resp, $ticketinfoarray);
    	}

    	return $resp;
    }

    public function queuePerformance(Request $request)
    {
    	$startdt = $request->input('QPRstartdate');
    	$enddt = $request->input('QPRenddate');
		   
		$resp = array();
		$queueinfoarray = array(); 	
    	$queues = Queue::all();     	

    	$start = date('Y-m-d', strtotime($startdt));
		$end = date('Y-m-d', strtotime($enddt));

		$start .= ' 00:00:01';
		$end .= ' 23:59:59';

    	foreach($queues as $queue)
    	{
    		$tixopened = $tixclosed = $totalclosetime = 0; 
    		$tickets = $queue
    					->tickets()
    					->whereBetween('tickets.created_at', [$start, $end])
    					->where('tickets.statusid', '!=', 4)
    					->whereNull('parentticketid')
    					->get();

    		foreach($tickets as $ticket)
    		{
    			$tixopened++;

    			if($ticket->dateresolved != null)
    			{
    				$tixclosed++;
    				$totalclosetime += $ticket->timetoclose;
    			}
    		}

    		$queueinfoarray['queuename'] = $queue->queuename;
    		$queueinfoarray['ticketsopened'] = $tixopened;
    		$queueinfoarray['ticketsclosed'] = $tixclosed;

    		if($tixclosed > 1)
    		{
    			$queueinfoarray['averageclosetime'] = seconds2human($totalclosetime/$tixclosed);
    		}
    		else
    		{
    			$queueinfoarray['averageclosetime'] = seconds2human($totalclosetime);
    		}    

    		array_push($resp, $queueinfoarray);		
    	}
    	
    	return $resp;
    }

    public function resolutionTimeSearch(Request $request)
    {
    	$timeperiod = $request->input('timeperiod');
    	$ticketinfoarray = array();
    	$resp = array();

    	$tickets = DB::table('tickets')
                        ->leftJoin('users AS u1', 'tickets.requestorid', '=', 'u1.userid')
                        ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
                        ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
                        ->leftJoin('users AS u2', 'tickettechs.techid', '=', 'u2.userid')
                        ->select('u1.name AS requestor', 'statuses.status', 'tickets.ticketid', 'u2.name AS tech', 'tickets.timetoclose')
                        ->where([['tickets.timetoclose', '>', $timeperiod],['tickets.statusid', '!=', 4]])
                        ->whereNull('parentticketid')
                        ->get();

        foreach($tickets as $ticket)
        {
        	$ticketinfoarray['ticketid'] = $ticket->ticketid;
        	$ticketinfoarray['requestor'] = $ticket->requestor;
        	if($ticket->tech == null)
        	{
        		$ticketinfoarray['tech'] = 'Unassigned';
        	}
        	else
        	{
        		$ticketinfoarray['tech'] = $ticket->tech;
        	}
        	$ticketinfoarray['status'] = $ticket->status;
        	$ticketinfoarray['timetoclose'] = seconds2human($ticket->timetoclose);

        	array_push($resp, $ticketinfoarray);
        }

    	return $resp;
    }

    public function techSummary(Request $request)
    {
    	$startdt = $request->input('TSRstartdate');
    	$enddt = $request->input('TSRenddate');
    	$techs = $request->input('TSRuserselect');

    	$start = date('Y-m-d', strtotime($startdt));
		$end = date('Y-m-d', strtotime($enddt));

		$start .= ' 00:00:01';
		$end .= ' 23:59:59';
    	
    	$resp = array();

    	for($i=0; $i<count($techs); $i++)
    	{
    		$techstix = DB::table('tickets')
	                        ->leftJoin('users AS u1', 'tickets.requestorid', '=', 'u1.userid')
	                        ->leftJoin('categories', 'tickets.categoryid', '=', 'categories.categoryid')
	                        ->leftJoin('statuses', 'tickets.statusid', '=', 'statuses.statusid')
	                        ->leftJoin('tickettechs', 'tickets.ticketid', '=', 'tickettechs.ticketid')
	                        ->leftJoin('users AS u2', 'tickettechs.techid', '=', 'u2.userid')
	                        ->leftJoin('ticketqueues', 'tickets.ticketid', '=', 'ticketqueues.ticketid')
	                        ->leftJoin('queues', 'ticketqueues.queueid', '=', 'queues.queueid')
	                        ->select('u1.name AS requestor', 'categories.category', 'statuses.status', 'tickets.requestorid', 'tickets.ticketid', 'tickets.dateresolved', 'tickets.created_at', 'tickettechs.techid', 'u2.name AS tech', 'ticketqueues.queueid', 'queues.queuename', 'tickets.timetoclose')
	                        ->where([['tickettechs.techid', $techs[$i]], ['tickets.statusid', '!=', 4]])
	                        ->whereNull('parentticketid')
	                        ->whereBetween('tickets.created_at', [$start, $end])
	                        ->get(); 

	       	foreach($techstix as $ticket)
	       	{
	       		$techinfoarray = array();

	       		$techinfoarray['ticketid'] = $ticket->ticketid;
	       		$techinfoarray['queue'] = $ticket->queuename;
	       		$techinfoarray['category'] = $ticket->category;
	       		$techinfoarray['requestor'] = $ticket->requestor;
	       		$techinfoarray['tech'] = $ticket->tech;
	       		$techinfoarray['created_at'] = $ticket->created_at;

	       		if($ticket->dateresolved !== null)
	       		{
	       			$techinfoarray['dateresolved'] = $ticket->dateresolved;
	       		}
	       		else
	       		{
	       			$techinfoarray['dateresolved'] = '';
	       		}

	       		$techinfoarray['status'] = $ticket->status;
	       		$techinfoarray['timetoclose'] = seconds2human($ticket->timetoclose);

	       		array_push($resp, $techinfoarray);	      		
	       	}
    		
    	}

    	return $resp;
    }
}
