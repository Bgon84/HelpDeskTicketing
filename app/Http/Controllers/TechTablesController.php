<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;

class TechTablesController extends Controller
{
    public function getAllTix()
    {
    	$user = Auth::user();
    	$alltickets = getAllTix();
    	$tickettechs = getTicketTechs();
    	$userperms = getUserPerms($user->userid);
    	$rows = '';

    	foreach($alltickets as $tik) 
    	{
    		$status = '';
            if($tik->status == 'Submitted' || $tik->status == 'In Progress') 
            {
                $status = '<span class="label label-success">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Resolved' || $tik->status == 'Voided') 
            {
                $status = '<span class="label label-danger">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Frozen')
            {
                $status = '<span class="label label-primary">' . $tik->status . '</span>';
            }
            else
            {
                $status = '<span class="label label-warning">' . $tik->status . '</span>';
            }  

            $ondblclick = "window.open('getticketinfo/". $tik->ticketid . "', '_blank')";
    		$rows .= '<tr class="queueitem searchable" id="row-'.$tik->ticketid.'" ondblclick="'. $ondblclick .'">';
    		$rows .='<td>'. $tik->ticketid . '</td>';  
            $rows .= '<td>'. $status .'</td>';
            $rows .= '<td class="techcell">';

            if($tik->techid !== null)
            {
                foreach($tickettechs as $tech)
                {
                    if($tech->ticketid == $tik->ticketid)
                    {
                        if(in_array('View_Tech_Info_On_Hover', $userperms))
                        {
                    		$rows .= "<label class='techlabel' title='$tech->department 
    								Email: $tech->email
    								Phone: $tech->phoneNumber 
    								Fax: $tech->faxnumber 
    								Cell: $tech->mobilephone 
    								Location: $tech->location'>
                                <a href='mailto:$tech->email'>$tech->name</a>
                            </label>";
                        }
                        else 
                        {
                            $rows .= '<label class="techlabel">$tech->name</label>';                
                        }
                    }
                }
            }
            else
            {
                $rows.= '<label class="red">Unassigned</label>';
            }

            $rows .= '</td><td>'. $tik->queuename .'</td>';
            $rows .= '<td>'. $tik->category .'</td>';                                                        
            $rows .= '<td>'. $tik->priority .'</td>';
           	$rows .= '<td>';

            if(in_array('View_User_Info_On_Hover', $userperms))
            {
                $requestorinfo = getuserinfo($tik->requestorid);
                                
            	$rows .= "<label id='ticketrequestor' title='$requestorinfo->department 
					Email: $requestorinfo->email
					Phone: $requestorinfo->phoneNumber 
					Fax: $requestorinfo->faxnumber 
					Cell: $requestorinfo->mobilephone 
					Location: $requestorinfo->location'>
            		<a href='mailto:$requestorinfo->email'>$tik->requestor</a>";
            }
            else 
            {
                $rows .= '<label id="ticketrequestor">$tik->requestor</label>';
            }
           
            $rows .= '</td><td class="desc"><div class="desc" title="' . $tik->description . '">' . $tik->description . '</div></td>';                 
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->created_at)) . '</td>';
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->updated_at)) . '</td>';   
            $rows .= '<td>';

            if($tik->masterticket == 1) 
            {
                $rows .= '<p>Yes</p>';
            }
            else
            {
                $rows .= '<p>No</p>'; 
            }
            $rows .= '</td></tr>';
        }

        return $rows;
    }

    public function getAllTechTix()
    {
    	$user = Auth::user();
    	$alltechtickets = getAllTicketsForTech($user->userid);
    	$tickettechs = getTicketTechs();
    	$userperms = getUserPerms($user->userid);
    	$rows = '';

    	foreach($alltechtickets as $tik) 
    	{
    		$status = '';
            if($tik->status == 'Submitted' || $tik->status == 'In Progress') 
            {
                $status = '<span class="label label-success">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Resolved' || $tik->status == 'Voided') 
            {
                $status = '<span class="label label-danger">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Frozen')
            {
                $status = '<span class="label label-primary">' . $tik->status . '</span>';
            }
            else
            {
                $status = '<span class="label label-warning">' . $tik->status . '</span>';
            }  

            $ondblclick = "window.open('getticketinfo/". $tik->ticketid . "', '_blank')";
    		$rows .= '<tr class="queueitem searchable" id="row-'.$tik->ticketid.'" ondblclick="'. $ondblclick .'">';
    		$rows .='<td>'. $tik->ticketid . '</td>';  
            $rows .= '<td>'. $status .'</td>';
            $rows .= '<td class="techcell">';

            if($tik->techid !== null)
            {
                foreach($tickettechs as $tech)
                {
                    if($tech->ticketid == $tik->ticketid)
                    {
                        if(in_array('View_Tech_Info_On_Hover', $userperms))
                        {
                            $rows .= "<label class='techlabel' title='$tech->department 
                                    Email: $tech->email
                                    Phone: $tech->phoneNumber 
                                    Fax: $tech->faxnumber 
                                    Cell: $tech->mobilephone 
                                    Location: $tech->location'>
                                <a href='mailto:$tech->email'>$tech->name</a>
                            </label>";
                        }
                        else 
                        {
                            $rows .= '<label class="techlabel">$tech->name</label>';                
                        }
                    }
                }
            }
            else
            {
                $rows.= '<label class="red">Unassigned</label>';
            }
                

            $rows .= '</td><td>'. $tik->queuename .'</td>';
            $rows .= '<td>'. $tik->category .'</td>';                                                        
            $rows .= '<td>'. $tik->priority .'</td>';
           	$rows .= '<td>';

            if(in_array('View_User_Info_On_Hover', $userperms))
            {
                $requestorinfo = getuserinfo($tik->requestorid);
                                
            	$rows .= "<label id='ticketrequestor' title='$requestorinfo->department 
					Email: $requestorinfo->email
					Phone: $requestorinfo->phoneNumber 
					Fax: $requestorinfo->faxnumber 
					Cell: $requestorinfo->mobilephone 
					Location: $requestorinfo->location'>
            		<a href='mailto:$requestorinfo->email'>$tik->requestor</a>";
            }
            else 
            {
                $rows .= '<label id="ticketrequestor">$tik->requestor</label>';
            }
           
            $rows .= '</td><td class="desc"><div class="desc" title="' . $tik->description . '">' . $tik->description . '</div></td>';                 
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->created_at)) . '</td>';
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->updated_at)) . '</td>';   
            $rows .= '<td>';

            if($tik->masterticket == 1) 
            {
                $rows .= '<p>Yes</p>';
            }
            else
            {
                $rows .= '<p>No</p>'; 
            }
            $rows .= '</td></tr>';
        }

        return $rows;
    }

    public function getAllOpenTix()
    {
    	$user = Auth::user();
    	$allopentickets = getAllOpenTix();
    	$tickettechs = getTicketTechs();
    	$userperms = getUserPerms($user->userid);
    	$rows = '';

    	foreach($allopentickets as $tik) 
    	{
    		$status = '';
            if($tik->status == 'Submitted' || $tik->status == 'In Progress') 
            {
                $status = '<span class="label label-success">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Resolved' || $tik->status == 'Voided') 
            {
                $status = '<span class="label label-danger">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Frozen')
            {
                $status = '<span class="label label-primary">' . $tik->status . '</span>';
            }
            else
            {
                $status = '<span class="label label-warning">' . $tik->status . '</span>';
            }  

            $ondblclick = "window.open('getticketinfo/". $tik->ticketid . "', '_blank')";
    		$rows .= '<tr class="queueitem searchable" id="row-'.$tik->ticketid.'" ondblclick="'. $ondblclick .'">';
    		$rows .='<td>'. $tik->ticketid . '</td>';  
            $rows .= '<td>'. $status .'</td>';
            $rows .= '<td class="techcell">';

            if($tik->techid !== null)
            {
                foreach($tickettechs as $tech)
                {
                    if($tech->ticketid == $tik->ticketid)
                    {
                        if(in_array('View_Tech_Info_On_Hover', $userperms))
                        {
                    		$rows .= "<label class='techlabel' title='$tech->department 
    								Email: $tech->email
    								Phone: $tech->phoneNumber 
    								Fax: $tech->faxnumber 
    								Cell: $tech->mobilephone 
    								Location: $tech->location'>
                                <a href='mailto:$tech->email'>$tech->name</a>
                            </label>";
                        }
                        else 
                        {
                            $rows .= '<label class="techlabel">$tech->name</label>';                
                        }
                    }
                }
            }
            else
            {
                $rows.= '<label class="red">Unassigned</label>';
            }

            $rows .= '</td><td>'. $tik->queuename .'</td>';
            $rows .= '<td>'. $tik->category .'</td>';                                                        
            $rows .= '<td>'. $tik->priority .'</td>';
           	$rows .= '<td>';

            if(in_array('View_User_Info_On_Hover', $userperms))
            {
                $requestorinfo = getuserinfo($tik->requestorid);
                                
        		$rows .= "<label id='ticketrequestor' title='$requestorinfo->department 
					Email: $requestorinfo->email
					Phone: $requestorinfo->phoneNumber 
					Fax: $requestorinfo->faxnumber 
					Cell: $requestorinfo->mobilephone 
					Location: $requestorinfo->location'>
            		<a href='mailto:$requestorinfo->email'>$tik->requestor</a>";
            }
            else 
            {
                $rows .= '<label id="ticketrequestor">$tik->requestor</label>';
            }
           
            $rows .= '</td><td class="desc"><div class="desc" title="' . $tik->description . '">' . $tik->description . '</div></td>';                 
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->created_at)) . '</td>';
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->updated_at)) . '</td>';   
            $rows .= '<td>';

            if($tik->masterticket == 1) 
            {
                $rows .= '<p>Yes</p>';
            }
            else
            {
                $rows .= '<p>No</p>'; 
            }
            $rows .= '</td></tr>';
        }

        return $rows;
    }

    public function getAllOpenTechTix()
    {
    	$user = Auth::user();
    	$techopentickets = getOpenTicketsForTech($user->userid);
    	$tickettechs = getTicketTechs();
    	$userperms = getUserPerms($user->userid);
    	$rows = '';

    	foreach($techopentickets as $tik) 
    	{
    		$status = '';
            if($tik->status == 'Submitted' || $tik->status == 'In Progress') 
            {
                $status = '<span class="label label-success">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Resolved' || $tik->status == 'Voided') 
            {
                $status = '<span class="label label-danger">' . $tik->status . '</span>';
            }
            elseif($tik->status == 'Frozen')
            {
                $status = '<span class="label label-primary">' . $tik->status . '</span>';
            }
            else
            {
                $status = '<span class="label label-warning">' . $tik->status . '</span>';
            }  

            $ondblclick = "window.open('getticketinfo/". $tik->ticketid . "', '_blank')";
    		$rows .= '<tr class="queueitem searchable" id="row-'.$tik->ticketid.'" ondblclick="'. $ondblclick .'">';
    		$rows .='<td>'. $tik->ticketid . '</td>';  
            $rows .= '<td>'. $status .'</td>';
            $rows .= '<td class="techcell">';

            if($tik->techid !== null)
            {
                foreach($tickettechs as $tech)
                {
                    if($tech->ticketid == $tik->ticketid)
                    {
                        if(in_array('View_Tech_Info_On_Hover', $userperms))
                        {
                    		$rows .= "<label class='techlabel' title='$tech->department 
    								Email: $tech->email
    								Phone: $tech->phoneNumber 
    								Fax: $tech->faxnumber 
    								Cell: $tech->mobilephone 
    								Location: $tech->location'>
                                <a href='mailto:$tech->email'>$tech->name</a>
                            </label>";
                        }
                        else 
                        {
                            $rows .= '<label class="techlabel">$tech->name</label>';                
                        }
                    }
                }
            }
            else
            {
                $rows.= '<label class="red">Unassigned</label>';
            }

            $rows .= '</td><td>'. $tik->queuename .'</td>';
            $rows .= '<td>'. $tik->category .'</td>';                                                        
            $rows .= '<td>'. $tik->priority .'</td>';
           	$rows .= '<td>';

            if(in_array('View_User_Info_On_Hover', $userperms))
            {
                $requestorinfo = getuserinfo($tik->requestorid);
                                
            	$rows .= "<label id='ticketrequestor' title='$requestorinfo->department 
					Email: $requestorinfo->email
					Phone: $requestorinfo->phoneNumber 
					Fax: $requestorinfo->faxnumber 
					Cell: $requestorinfo->mobilephone 
					Location: $requestorinfo->location'>
            		<a href='mailto:$requestorinfo->email'>$tik->requestor</a>";
            }
            else 
            {
                $rows .= '<label id="ticketrequestor">$tik->requestor</label>';
            }
           
            $rows .= '</td><td class="desc"><div class="desc" title="' . $tik->description . '">' . $tik->description . '</div></td>';                 
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->created_at)) . '</td>';
            $rows .= '<td>'. date('m-d-Y H:i:s', strtotime($tik->updated_at)) . '</td>';   
            $rows .= '<td>';

            if($tik->masterticket == 1) 
            {
                $rows .= '<p>Yes</p>';
            }
            else
            {
                $rows .= '<p>No</p>'; 
            }
            $rows .= '</td></tr>';
        }

        return $rows;
    }
}
