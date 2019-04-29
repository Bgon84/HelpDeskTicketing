<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\SMTP;
use Auth;

class SMTPController extends Controller
{
    public function update(Request $request)
    {
    	try
    	{
    		$server = $request->input('smtpserver');
    		$port = intval($request->input('smtpport'));
    		$encryption = $request->input('smtpencryptionselect');
    		$username = $request->input('smtpusername');
    		$password = $request->input('smtppassword');
    		$from = $request->input('smtpfrom');
            $now = date('Y-m-d H:i:s');
            $user = Auth::user();

            if(count($username) < 1 )
            {
                $username = '';
            }
            if(count($password) < 1)
            {
                $password = '';
            }

    		$smtp = SMTP::first();

            $currentserver = $smtp->server;
            $currentport = intval($smtp->port);
            $currentencryption = $smtp->encryption;
            $currentusername = $smtp->username;
            $currentpassword = $smtp->password;
            $currentfrom = $smtp->fromaddress;

            if(count($currentusername) < 1)
            {
                $currentusername = '';
            }
            if(count($currentpassword) < 1)
            {
                $currentpassword = '';
            }

    		$smtp->server = $server;
		    $smtp->port = $port;
		    $smtp->encryption = $encryption;
		    $smtp->username = $username;
		    $smtp->password = Crypt::encryptString($password);
		    $smtp->fromaddress = $from;

		    $smtp->save();

            $this->updateDotEnv('MAIL_HOST', $server);
            $this->updateDotEnv('MAIL_PORT', $port);
            $this->updateDotEnv('MAIL_ENCRYPTION', $encryption);            
            $this->updateDotEnv('MAIL_USERNAME', $username);
            $this->updateDotEnv('MAIL_PASSWORD', $password);
            $this->updateDotEnv('MAIL_FROM_ADDRESS', $from);

            if($currentserver !== $server)
            {
                logsettingsupdate($user->userid, 'SMTP Server', $currentserver, $server, $now);
            }
            
            if($currentport !== $port)
            {
                logsettingsupdate($user->userid, 'SMTP Port', $currentport, $port, $now);
            }

            if($currentencryption !== $encryption)
            {
                logsettingsupdate($user->userid, 'SMTP Encryption', $currentencryption, $encryption, $now);
            }

            if($currentusername !== $username)
            {
                logsettingsupdate($user->userid, 'SMTP Username', $currentusername, $username, $now);
            }
            
            if($currentpassword !== null & $currentpassword !== '')
            {
                $currentpassword = Crypt::decryptString($currentpassword);
            }

            if($currentpassword !== $password)
            {
                logsettingsupdate($user->userid, 'SMTP Password', $currentpassword, Crypt::encryptString($password), $now);
            }

            if($currentfrom !== $from)
            {
                logsettingsupdate($user->userid, 'SMTP From Address', $currentfrom, $from, $now);
            }
    	}        
    	catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
		
        $activity = $user->name . ' edited SMTP settings';
        logactivity($user->userid, 31, $activity, $now); // Activitytypeid 31 - Edited SMTP settings

		$resp = array('success', $smtp);
		return $resp;
    }


    public function sendSMTPtest(Request $request)
    {
    	try
    	{
	    	$to = $request->input('to');
	    	$from = $request->input('from');
	    	$msg = $request->input('msg');
	    	$title = "This is a test of SMTP settings";
            $user = Auth::user();
            $now = date('Y-m-d H:i:s');

	    	Mail::send('emails.smtptestemail', ['title' => $title, 'body' => $msg], function($message) use ($to, $from)
	        {

	            $message->from($from);

	            $message->to($to);

	        });            

            $activity = $user->name . ' successfully ran SMTP test';
            logactivity($user->userid, 37, $activity, $now); // Activitytypeid 37 - Successful SMTP test
	    }
    	catch(\Exception $ex)
        {
            $activity = $user->name . ' unsuccessfully ran SMTP test';
            logactivity($user->userid, 38, $activity, $now); // Activitytypeid 38 - Unsuccessful SMTP test

            return $ex->getMessage();
        }
        return 'success';
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

    public function __construct()
    {
        $this->middleware('auth');
    }
}
