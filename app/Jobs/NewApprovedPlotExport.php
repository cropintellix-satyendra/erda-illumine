<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewApprovedPlotExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $unique; 
    public $data; 
    public $filename; 
    public function __construct($unique,$data,$filename)
    {
        $this->unique=$unique;
        $this->data=$data;
        $this->filename=$filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        dd($this->data->state);
        //Excel::store(new ApprovedPlotExport($this->data, $this->filename,'local');
        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\ApprovedPlotExport($this->unique,(object)$this->data),$this->filename,'local');
        
        
    }
}
