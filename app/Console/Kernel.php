<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\WebSocketServer::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('adldap:import')
                    ->everyMinute()
                    ->when(function()
                    {
                        $settings = getLDAPsyncsettings();
                        $lastrun = $settings['lastrun'];
                        $interval = $settings['interval'];
                        $now = strtotime(date('Y-m-d h:i:s'));
                        
                        $diff = $now - $lastrun;

                        if($settings['enabled'] == 'true' && $diff >= $interval)
                        {                            
                            return true;
                        }
                    })
                    ->after(function()
                    {
                        setManagerIds();  
                        assignDefaultGroup();
                        
                        $now = strtotime(date('Y-m-d h:i:s'));

                        DB::table('settings')
                            ->where('setting', 'LDAP_SYNC_LAST_RUN')
                            ->update(['value' => $now]);
                    });
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

/*  

    * * * * * /usr/bin/php /var/www/slick_tix/artisan schedule:run >> /dev/null 2>&1 

*/