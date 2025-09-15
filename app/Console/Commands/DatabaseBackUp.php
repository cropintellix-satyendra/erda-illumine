<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup DB daily @3am';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::alert('backup started');   
        \Log::alert('Start time:. '.\Carbon\Carbon::now());   
        set_time_limit(-1);
        ini_set('memory_limit', '64000M');
        //$filename = "backup-" . Carbon::now()->format('Y-m-d') . ".gz";
        $filename = "backup-" . \Carbon\Carbon::now()->timestamp . ".sql";
        // Create backup folder and set permission if not exist.        
        $storageAt = storage_path() . "/app/backup/";        
        if(!\File::exists($storageAt)) {            
            \File::makeDirectory($storageAt, 0755, true, true);        
        }    

        //$command = "mysqldump --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  | gzip > " . storage_path() . "/app/backup/" . $filename;

        $command = "".env('DB_DUMP_PATH', 'mysqldump')." --user=" . env('DB_USERNAME','kc_live_user') ." --password='" . env('DB_PASSWORD','8o]4bv7Fh-Ik6Lma') . "' --host=" . env('DB_HOST','127.0.0.1') . " " . env('DB_DATABASE','kc_live_awd') . " --skip-add-drop-table | sed -e 's/AUTO_INCREMENT=[[:digit:]]* //' | sed 's/^CREATE TABLE /CREATE TABLE IF NOT EXISTS /'  > " . $storageAt . $filename;   


 \Log::info($command);   

        //   dd($filename, $command, env('DB_DATABASE', 'forge'), getenv('DB_PASSWORD'));
        $returnVar = NULL;
        $output  = NULL;
        exec($command, $output, $returnVar);
        \Log::alert('backup end');   
        \Log::alert('End time:. '.\Carbon\Carbon::now());   
        return $output;
    }
}
