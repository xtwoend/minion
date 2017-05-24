<?php

namespace Minion\Console\Commands;

use DB;
use File;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minion:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Minion CMS Setup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->checkInstaller()) {
            
            $driver = $this->anticipate('Database driver ? (mysql or pgsql)', ['pgsql', 'mysql']);
            $host = $this->ask('Database host?', 'localhost');
            $port = $this->ask('Database port?', ($driver == 'pgsql') ? '5432' : '3306');
            $name = $this->ask('Database name?');
            $user = $this->ask('Database user?');
            $password = $this->secret('Database password?');

            $this->generateEnvFile($driver, $host, $port, $name, $user, $password);

            $this->call('key:generate');

            // check database
            $this->setLaravelConfiguration($driver, $host, $port, $name, $user, $password);

            if ($this->databaseConnectionIsValid()) {
                $this->info('Database connected');

                $this->info('Start migration and seeder');
                $this->call('migrate');
                $this->call('db:seed');

                $this->migratePlugins();

                $this->info('Publish themes assets');
                $this->call('theme:publish');
                $this->call('storage:link');
            }
        }
    }

    private function checkInstaller()
    {
        if (File::exists('.env')) {
            return $this->confirm('Your environment file already exists, continue instalation (reinstall) ?');
        }

        return true;
    }

    public function migratePlugins()
    {
        $plugins = app('plugins')->all();

        foreach ($plugins as $name => $plugin) {
            $this->call('plugin:migrate', [$name]);
            $this->call('plugin:seed', [$name]);
        }
    }

    /**
     * generate .env file and fill the value from command.
     */
    private function generateEnvFile($databaseDriver, $databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword)
    {

        File::copy('.env.example', '.env');

        $envContents = fopen('.env', 'r+');

        $newEnv = '';

        while (!feof($envContents)) {
            $env = explode('=', fgets($envContents));

            $envKey = $env[0];
            $envVal = !empty($env[1]) ? $env[1] : null;

            if ($envKey != "\n" && $envKey != '') {
                switch ($envKey) {
                    case 'DB_CONNECTION':
                        $newEnv .= $envKey.'='.$databaseDriver.PHP_EOL;
                        break;
                    case 'DB_HOST':
                        $newEnv .= $envKey.'='.$databaseHost.PHP_EOL;
                        break;
                    case 'DB_PORT':
                        if (empty($databasePort)) {
                            $databasePort = ($databaseDriver == 'pgsql') ? '5432' : '3306';
                        }

                        $newEnv .= $envKey.'='.$databasePort.PHP_EOL;
                        break;
                    case 'DB_DATABASE':
                        $newEnv .= $envKey.'='.$databaseName.PHP_EOL;
                        break;
                    case 'DB_USERNAME':
                        $newEnv .= $envKey.'='.$databaseUser.PHP_EOL;
                        break;
                    case 'DB_PASSWORD':
                        $newEnv .= $envKey.'='.$databasePassword.PHP_EOL;
                        break;

                    default:
                        $newEnv .= $envKey.'='.$envVal;

                        break;
                }
            } else {
                $newEnv .= "\n";
            }
        }

        file_put_contents('.env', $newEnv);

        fclose($envContents);
    }

    /**
     * @param $driver
     * @param $name
     * @param $port
     * @param $user
     * @param $password
     */
    protected function setLaravelConfiguration($driver, $host, $port, $name, $user, $password)
    {
        config(['app.env' => 'local']);
        config(['database.default' => $driver]);
        config(['database.connections.'.$driver.'.host' => $host]);
        config(['database.connections.'.$driver.'.port' => $port]);
        config(['database.connections.'.$driver.'.database' => $name]);
        config(['database.connections.'.$driver.'.username' => $user]);
        config(['database.connections.'.$driver.'.password' => $password]);
    }

    /**
     * Is the database connection valid?
     * @return bool
     */
    protected function databaseConnectionIsValid()
    {
        try {
            app('db')->reconnect();
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}