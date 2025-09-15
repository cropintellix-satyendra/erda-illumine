<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\AllBenefitExport;

class DownloadExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download excel file from job table';

    /**
     * Execute the console command.
     *
     * @return int
     */

     public function handle(){
        set_time_limit(-1);
        ini_set('memory_limit', '640000M');
        $begin = microtime(true);
        $job=\DB::table('temp_jobs')->where('queue','excel')->where('attempts',0)->orderBy('id','asc')->first();
        if($job){    
            $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>2,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            $payload=json_decode($job->payload);
            $data=new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]);
            //$excel=\Maatwebsite\Excel\Facades\Excel::store(new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //entering data
            if(count($data->collection())>0){
                //exporting header
                $heading_col='A';
                $heading_row=1;
                if(count($data->headings())>0){
                    foreach($data->headings() as $key=>$value){
                        $sheet->setCellValue($heading_col.$heading_row, $value);
                        $heading_col++;
                    }
                }
                //exporting data
                $heading_col='A';
                $heading_row=2;
                foreach($data->collection() as $items){
                    $items=$data->map($items);
                    if(count($items)>0){
                        foreach($items as $key=>$value){
                            //  \Log::info("Key: ".$key);
                            // if(is_array($value)){
                            //     $value = implode(', ', $value);
                            // }
                            $sheet->setCellValue($heading_col.$heading_row, $value);
                            $heading_col++;
                        }
                    } 
                    $heading_row++;
                    $heading_col='A';
                }
            }
            $writer = new Xlsx($spreadsheet);
            
            $writer->save(base_path('public/excel/'.$payload->data->filename));
            $end = microtime(true) - $begin;
            //echo $farmer->count().'<br/>';
            //echo ($end);                    
            //\Log::info('Total count:'.$data->collection());    
            //\Log::info('Time: '.$end); 
            $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
        }
    } 

    

    
    // public function handle(){
    //     set_time_limit(-1);
    //     ini_set('memory_limit', '640000M');
    //     $begin = microtime(true);
    //     $job=\DB::table('temp_jobs')->where('queue','excel')->where('attempts',0)->orderBy('id','asc')->first();
    //     if($job){    
    //         $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
    //                     'attempts'=>2,
    //                     'reserved_at'=>\Carbon\Carbon::now()->timestamp
    //                 ]);
    //         $payload=json_decode($job->payload);
    //         $data=new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]);
    //         //$excel=\Maatwebsite\Excel\Facades\Excel::store(new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
            
    //         $spreadsheet = new Spreadsheet();
    //         $sheet = $spreadsheet->getActiveSheet();
    //         //entering data
    //         if(count($data->collection())>0){
    //             //exporting header
    //             $heading_col='A';
    //             $heading_row=1;
    //             if(count($data->headings())>0){
    //                 foreach($data->headings() as $key=>$value){
    //                     $sheet->setCellValue($heading_col.$heading_row, $value);
    //                     $heading_col++;
    //                 }
    //             }
    //             //exporting data
    //             $heading_col='A';
    //             $heading_row=2;
    //             foreach($data->collection() as $items){
    //                 $items=$data->map($items);
    //                 if(count($items)>0){
    //                     foreach($items as $key=>$value){
    //                         $sheet->setCellValue($heading_col.$heading_row, $value);
    //                         $heading_col++;
    //                     }
    //                 } 
    //                 $heading_row++;
    //                 $heading_col='A';
    //             }
    //         }
    //         $writer = new Xlsx($spreadsheet);
            
    //         $writer->save(base_path('public/excel/'.$payload->data->filename));
    //         $end = microtime(true) - $begin;
    //         //echo $farmer->count().'<br/>';
    //         //echo ($end);                    
    //         //\Log::info('Total count:'.$data->collection());    
    //         //\Log::info('Time: '.$end); 
    //         $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
    //                     'attempts'=>1,
    //                     'reserved_at'=>\Carbon\Carbon::now()->timestamp
    //                 ]);
    //     }
    // } 
    public function old_handle()
    {
        set_time_limit(-1);
        ini_set('memory_limit', '64000M');
        \Log::info('working...');
        $job=\DB::table('temp_jobs')->where('queue','excel')->where('attempts',0)->first();
        if($job){
            $update=\DB::table('jobs')->where('id',$job->id)->update([
                        'attempts'=>2,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            $payload=json_decode($job->payload);
                //dd($payload);
                //$command=new $payload->data->command($payload->data->parameters[0],$payload->data->parameters[1]);
                //\Log::info(json_encode($payload->data->command));
                //$export=$payload->data->command;
                $excel=\Maatwebsite\Excel\Facades\Excel::store(new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
                //$excel=\Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\L1ApprovedExport('all',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
                if(!$excel){
                    
                }
                $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
        }
        /*if($jobs->count()>0){
            foreach($jobs as $job){
                $payload=json_decode($job->payload);
                //dd($payload);
                //$command=new $payload->data->command($payload->data->parameters[0],$payload->data->parameters[1]);
                //\Log::info(json_encode($payload->data->command));
                //$export=$payload->data->command;
                //$excel=\Maatwebsite\Excel\Facades\Excel::store(new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
                //$excel=\Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\L1ApprovedExport('all',$payload->data->parameters[1]??[]), $payload->data->filename,$payload->data->drive);
                if(!$excel){
                    
                }
                $update=\DB::table('jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            }
        }*/

    }
}
