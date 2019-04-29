<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Authentication;
use DB;
use Auth;


class AuthenticationController extends Controller
{
    public function create(Request $request)
    {
        try{
        	$name = strtoupper($request->input('createauthname'));
        	$server = $request->input('createauthserver');
        	$port = $request->input('createauthport');
        	$username = $request->input('createauthusername');
        	$password = $request->input('createauthpass');
        	$binddn = $request->input('createauthbind');
        	$filter = $request->input('createauthfilter');

        	$auth = Authentication::create(
                [
                    'name' => $name, 
                    'server' => $server, 
                    'port' => $port, 
                    'username' => $username, 
                    'password' => Crypt::encryptString($password), 
                    'binddn' => $binddn, 
                    'filter' => $filter
                ]);
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

    	$resp = array('success', $auth);

    	return $resp;
    }

    public function updateAuthOption(Request $request)
    {
        // Get the post values.
        $id = $request->input('optionID');
        $state = $request->input('state');
        $returnArray = array();
        $user = Auth::user();
        $now = date('Y-m-d H:i:s');

        if($id > 0)
        {
            try
            {
                $auth = Authentication::find($id);

                // Check that a Native login user exists with Admin permissions if LDAP is being turned off
                $nativeids = array();
                if($auth->name == 'LDAP' && $auth->active == 1)
                {
                    $nativeusers = DB::table('users')
                                    ->select('userid')
                                    ->where([['logintype', '=', 'Native'], ['active', '=', 1]])
                                    ->get();
                    foreach($nativeusers as $nu)
                    {
                        array_push($nativeids, $nu->userid);
                    }

                    $nativeadmin = false;                    
                    foreach($nativeids as $id)
                    {   
                        $userperms = getUserPerms($id);
                        
                        if(in_array('View_Admin_Dashboard', $userperms) && in_array('View_Authentication_Tab', $userperms) && in_array('View_Authentication_Options', $userperms) && in_array('Edit_Authentication_Options', $userperms))
                        {
                            $nativeadmin = true;
                            break;
                        }
                    }

                    if($nativeadmin == false)
                    {
                        return 'no admin';
                    }
                }

                // if LDAP is being turned on, check settings 
                if($auth->name == 'LDAP' && $auth->active == 0)
                {
                    $server = $auth->server;
                    $port = $auth->port;
                    $binddn = $auth->binddn;
                    $username = $auth->username;
                    $password = Crypt::decryptString($auth->password);

                    $verify = $this->verifyLDAP($server, $port, $binddn, $username, $password);

                    if( $verify !== 'success')
                    {
                        return 'connection failed';
                    }
                }

                $auth->active = $state;
                $auth->save();

                if($auth->name == 'LDAP' && $auth->active == 0)
                {
                    $returnArray['ldap'] = 0;                   
                }
                else
                {
                    $returnArray['ldap'] = 1;                    
                }

                if($auth->name == 'LDAP')
                {
                    if($auth->active == 0)
                    {
                        $this->updateDotEnv('ADLDAP_ADMIN_USERNAME', null);
                        $activity = $user->name . ' disabled LDAP login';
                        logactivity($user->userid, 26, $activity, $now); // Activitytypeid 26 - Disabled LDAP Login
                    }
                    else
                    {
                        $this->updateDotEnv('ADLDAP_ADMIN_USERNAME', env('ADLDAP_ADMIN_USERNAME_PLACEHOLDER'));
                        $activity = $user->name . ' enabled LDAP login';
                        logactivity($user->userid, 25, $activity, $now); // Activitytypeid 25 - Enabled LDAP Login
                    }
                }
                else if($auth->name == 'Native')
                {
                    if($auth->active == 0)
                    {
                        $this->updateDotEnv('ADLDAP_LOGIN_FALLBACK', 'false');
                        $activity = $user->name . ' disabled Native login';
                        logactivity($user->userid, 28, $activity, $now); // Activitytypeid 28 - Disabled Native Login
                    }
                    else
                    {
                        $this->updateDotEnv('ADLDAP_LOGIN_FALLBACK', 'true');
                        $activity = $user->name . ' enabled Native login';
                        logactivity($user->userid, 27, $activity, $now); // Activitytypeid 27 - Enabled Native Login
                    }
                }

                return json_encode($returnArray);
            }
            catch(\Exception $ex)
            {
                $returnArray['error'] = "Failed";
                $returnArray['errorMessage'] = $ex->getMessage();
                return json_encode($returnArray);
            }
        }
    }

    public function update(Request $request)
    {
        try{
        	$authid = $request->input('authid');
        	$server = $request->input('editauthserver');
        	$port = $request->input('editauthport');
        	$username = $request->input('editauthusername');
        	$password = $request->input('editauthpass');
        	$binddn = $request->input('editauthbind');
        	$filter = $request->input('editauthfilter');
            $now = date('Y-m-d H:i:s');
            $user = Auth::user();

            //Check settings to ensure they're valid
            $valid = false;

            $verify = $this->verifyLDAP($server, $port, $binddn, $username, $password);

            if( $verify == 'success')
            {
                $valid = true;
            }
            else
            {
                die("connection failed");
            }

            if($valid == true)
            {
                $currentauth = Authentication::find($authid);

                $auth = Authentication::find($authid)
                                    ->update(
                                        [ 
                                            'server' => $server, 
                                            'port' => $port, 
                                            'username' => $username, 
                                            'password' => Crypt::encryptString($password), 
                                            'binddn' => $binddn, 
                                            'filter' => $filter
                                        ]);


                if($currentauth->server !== $server)
                {
                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Server', $currentauth->server, $server, $now);
                }

                if($currentauth->port !== $port)
                {
                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Port', $currentauth->port, $port, $now);
                }

                if($currentauth->username !== $username)
                {
                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Username', $currentauth->username, $username, $now);
                }

                if($currentauth->password !== null && $currentauth->password !== '')
                {
                    $currentpassword = Crypt::decryptString($currentauth->password);
                }
                else
                {
                    $currentpassword = '';
                }

                if($currentpassword !== $password)
                {
                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Password', $currentauth->password, Crypt::encryptString($password), $now);
                }

                if($currentauth->binddn !== $binddn)
                {
                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Bind DN', $currentauth->binddn, $binddn, $now);
                }

                if($currentauth->filter !== $filter)
                {
                    if($currentauth->filter == null)
                    {
                        $oldfilter = '';
                    }
                    else
                    {
                        $oldfilter = $currentauth->filter;
                    }

                    if($filter == null)
                    {
                        $filter = '';
                    }

                    logsettingsupdate($user->userid, $currentauth->name . ' Authentication Filter', $oldfilter, $filter, $now);
                }

                $this->updateDotEnv('ADLDAP_CONTROLLERS', '['.$server.']');
                $this->updateDotEnv('ADLDAP_PORT', $port);
                $this->updateDotEnv('ADLDAP_BASEDN', '"'. $binddn . '"');
                $this->updateDotEnv('ADLDAP_ADMIN_USERNAME', $username);
                $this->updateDotEnv('ADLDAP_ADMIN_USERNAME_PLACEHOLDER', $username);
                $this->updateDotEnv('ADLDAP_ADMIN_PASSWORD', "'". $password. "'");

                $activity = $user->name . ' edited authentication settings';
                logactivity($user->userid, 29, $activity, $now); // Activitytypeid 29 - Edited Authentication Settings
            }
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
        
    	return "success";
    }

    public function updateldapsynctrigger(Request $request)
    {
        $interval = $request->input('interval');
        $enabled = $request->input('enabled');
        $user = Auth::user();
        $now = date('Y-m-d H:i:s');

        try
        {
            $currentenabled = DB::table('settings')
                                ->select('value')
                                ->where('setting', 'LDAP_SYNC_ENABLED')
                                ->get();

            $currentinterval = DB::table('settings')
                                ->select('value')
                                ->where('setting', 'LDAP_SYNC_INTERVAL')
                                ->get();
            

            DB::table('settings')
                ->where('setting', 'LDAP_SYNC_ENABLED')
                ->update(['value' => $enabled]);

            DB::table('settings')
                ->where('setting', 'LDAP_SYNC_INTERVAL')
                ->update(['value' => $interval]);
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }

        if($currentenabled[0]->value !== $enabled)
        {
            logsettingsupdate($user->userid, 'LDAP_SYNC_ENABLED', $currentenabled[0]->value, $enabled, $now);
        }

        if($currentinterval[0]->value !== $interval)
        {
            logsettingsupdate($user->userid, 'LDAP_SYNC_INTERVAL', $currentinterval[0]->value, $interval, $now);
        }

        $activity = $user->name . ' edited LDAP sync settings';
        logactivity($user->userid, 30, $activity, $now); // Activitytypeid 30 - Edited LDAP Sync Settings
        
        return "success";
    }

    public function updateDotEnv($key, $newValue)
    {

        $path = base_path('.env');
        // get old value from current env
        $oldValue = env($key);

        // was there any change?
        if ($oldValue === $newValue) {
            return;
        }

        if(is_bool(env($key)))
        {
            $oldValue = env($key) ? 'true' : 'false';
        }

        if(is_bool($newValue))
        {
            if($newValue)
            {
                $newValue = 'true';
            }
            else
            {
                $newValue = 'false';
            }
        }

        // rewrite file content with changed data
        if (file_exists($path)) {
            // replace current value with new value 
            file_put_contents(
                $path, str_replace(
                    $key.'='.$oldValue, 
                    $key.'='.$newValue, 
                    file_get_contents($path)
                )
            );
        }
    }

    public function verifyLDAP($server, $port, $binddn, $username, $password)
    {
        $config = [
            'default' => 
            [
                'domain_controllers'    => array($server),
                'port'                  => $port,
                'base_dn'               => $binddn,
                'admin_username'        => $username,
                'admin_password'        => $password,
            ],
        ];
       
        $ad = new \Adldap\Adldap($config);        

        try 
        {
            $provider = $ad->connect('default');
            
            // Connection was successful.
            return 'success';
        }
        catch (\Adldap\Auth\BindException $e) 
        {
            return 'fail';
        }
    }

    public function ldapsync()
    {
        $now = strtotime(date('Y-m-d h:i:s'));
        $user = Auth::user();
        $now1 = date('Y-m-d H:i:s');

        try
        {
            \Artisan::call('adldap:import');    

            try 
            {
                setManagerIds();      
                assignDefaultGroup();    
            }
            catch (\Exception $e) 
            {
                logger()->error("Unable to set Manager IDs. {$e->getMessage()}");            
            }            

            DB::table('settings')
                ->where('setting', 'LDAP_SYNC_LAST_RUN')
                ->update(['value' => $now]);
        
            $activity = $user->name . ' successfully ran LDAP sync';
            logactivity($user->userid, 35, $activity, $now1); // Activitytypeid 35 - Successful LDAP Sync
        }
        catch(\Exception $ex)
        {
            $activity = $user->name . ' unsuccessfully ran LDAP sync';
            logactivity($user->userid, 36, $activity, $now1); // Activitytypeid 36 - Unsuccessful LDAP Sync
            
            return $ex->getMessage();
        }

        dd("done");
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
}
