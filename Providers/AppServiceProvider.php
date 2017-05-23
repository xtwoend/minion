<?php

namespace Minion\Providers;

use Illuminate\Support\ServiceProvider;
use Minion\Entities\Post;
use Minion\Entities\User;
use Minion\Observers\PostObserver;
use Minion\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any Application services.
     *
     * @return void
     */
    public function boot()
    {
        $config = cache()->get('config');
        if(is_array($config)){
            foreach($config as $key => $val)
            {
                config(['config.'.$key => $val]);
            }
            // app
            config(['app.debug' => $config['debug']?? config('app.debug')]);
            config(['app.locale' => $config['language']?? config('app.fallback_locale')]);
            config(['app.url' => $config['url']?? config('app.url')]);
            config(['app.timezone' => $config['timezone']?? config('app.timezone')]);
            // database
            // config(['database.default' => $config['db_connection']??config('database.default')]);
            // config(['database.connections.'.config('database.default').'.host' => $config['db_host']??'127.0.0.1']);
            // config(['database.connections.'.config('database.default').'.port' => $config['db_port']??'3306']);
            // config(['database.connections.'.config('database.default').'.database' => $config['db_database']??'forge']);
            // config(['database.connections.'.config('database.default').'.username' => $config['db_username']??'root']);
            // config(['database.connections.'.config('database.default').'.password' => $config['db_password']??'']);
            // filesystem
            config(['filesystems.default' => $config['disks']??'local']);
        }
        

        // Observers
        Post::observe(PostObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Register any Application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
