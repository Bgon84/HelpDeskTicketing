<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Priority; 
use Auth;

class CategoriesController extends Controller
{
    public function create(Request $request)
    {
        try{
        	$name = strtoupper($request->input('createcategoryname'));
            $desc = $request->input('createcategorydescription');            
        	$priority = $request->input('createcategorypriorityselect');
        	$active = $request->input('createcategoryactive');
            $queues = $request->input('createcategoryqueueselect');
            $internal = $request->input('createcategoryinternal');
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');

        	$cat = Category::create(
                [
                    'category' => $name,
                    'priorityid' => $priority, 
                    'active' => $active, 
                    'internal' => $internal,
                    'description' => $desc,
                ]);

            if(!empty($queues))
            {
                foreach($queues as $q)
                {
                    $cat->queues()->attach($q);
                }
            }        
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " created new category " . $cat->category;
        logactivity($user->userid, 21, $activity, $now); // activitytypeid 21 - Created Category

    	$resp = array('success', $cat, $queues);
    	return $resp;
    }

    public function update(Request $request)
    {
        try{
        	$catid = $request->input('editcategoryselect');
        	$name = strtoupper($request->input('editcategoryname'));
            $desc = $request->input('editcategorydescription');  
            $priority = $request->input('editcategorypriorityselect');
        	$active = $request->input('editcategoryactive');
            $queues = $request->input('editcategoryqueueselect');
            $internal = $request->input('createcategoryinternal');    
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');    

        	$cat = Category::find($catid);

        	$cat->category = $name;
        	$cat->priorityid = $priority;
        	$cat->active = $active;
            $cat->internal = $internal;
            $cat->description = $desc;
        	$cat->save();

            $currentqueues = $cat->queues()->get();

            if(empty($queues) && !empty($currentqueues))
            {
                // All Queues have been unassigned
                foreach($currentqueues as $cq)
                {
                    $cat->queues()->detach($cq);
                }
            }

            if(!empty($queues) && !empty($currentqueues))
            {
                // Unassign queues that have been removed
                foreach($currentqueues as $cq)
                {
                    if(!in_array($cq, $queues))
                    {
                        $cat->queues()->detach($cq);
                    }
                }
            }

            if(!empty($queues))
            {
                foreach($queues as $q)
                {
                    $cat->queues()->attach($q);
                }
            }
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        $activity = $user->name . " edited category " . $cat->category;
        logactivity($user->userid, 22, $activity, $now); // activitytypeid 22 - Created Category

    	return 'success';
    }

    public function getinfo(Request $request)
    {
    	$catid = $request->input('categoryid');
    	$cat = Category::find($catid);

        $queues = $cat->queues()->get();

        $resp = array('category' => $cat, 'queues' => $queues);

    	return $resp;
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
}
