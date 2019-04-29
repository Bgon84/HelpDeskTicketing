<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Crypt;
use Auth;
use DB;
use App;
use App\User;
use App\Group;
use App\Role;
use App\Permission;
use App\Queue;
use App\Priority;
use App\Status;
use App\Category;
use App\Notification;
use App\Authentication;
use App\Option;
use App\SMTP;
use App\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('modals.smtptest', function($view)
        {
            $view->with('smtptestmessage', 'Congratulations! Your SMTP settings test was successful!');
        });

        view()->composer('admin.admingeneralsettings', function($view)
        {
            $defaultgroup = DB::table('settings')
                                ->select('value')
                                ->where('setting', 'DEFAULT_GROUP')
                                ->get();

            $view->with('defaultgroup', $defaultgroup);
        });

        view()->composer('admin.*', function($view)
        {            
            $view->with('groups', Group::all()->sortBy('groupname'));
            $view->with('roles', Role::all()->sortBy('rolename'));
            $view->with('perms', Permission::all()->sortBy('permission'));            
            $view->with('notifications', Notification::all()->sortBy('notificationname')); 
            $view->with('authentication', Authentication::all()->sortByDesc('name')); 
            $view->with('ldapsettings', Authentication::where('name', '=', 'LDAP')->first()); 
            $view->with('options', Option::all()->sortBy('optionname')); 
            $view->with('smtpsettings', SMTP::all()->first());            
        });

        view()->composer(['admin.admingeneralsettings', 'techdash'], function($view)
        {
            $view->with('techdashrefresh', Setting::where('setting', 'TECH_DASH_REFRESH_RATE')->first());
        });

        view()->composer('ticketdetails', function($view)
        {
            $users = User::all();
            $techs = array();

            foreach($users as $user)
            {
                $perms = getUserPerms($user->userid);
                if(in_array('Receive_Tickets', $perms))
                {
                    $techinfo = array('userid' => $user->userid, 'name' => $user->name);
                    array_push($techs, $techinfo);
                }
            }
            
            $view->with('techs', $techs);
        });

        view()->composer('*',function($view) {
            
            $view->with('user', Auth::user());
            $view->with('users', User::all()->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE));            
            $view->with('queues', Queue::all()->sortBy('queuename'));
            $view->with('categories', Category::all()->sortBy('category'));
            $view->with('statuses', Status::all()->sortBy('status'));            
            $view->with('priorities', Priority::all()->sortBy('priority'));            

            $ldapsyncsettings = getLDAPsyncsettings();
            $view->with('ldapsyncsettings', $ldapsyncsettings);   

            if(Auth::user() !== null)
            {
                $user = Auth::user();
                $userid = $user->userid;
               
                $userroles = $user->roles()->get();
                $usergroups = $user->groups()->get();
                $perms = getUserPerms($userid);
                $usersqueues = getUsersQueues($userid);         

                $view->with('userperms', $perms);
                $view->with('userroles', $userroles);
                $view->with('usergroups', $usergroups);            
                $view->with('usersqueues', $usersqueues);
            }

        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
