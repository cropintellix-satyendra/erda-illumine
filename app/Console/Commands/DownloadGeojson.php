<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PipeInstallation;
use App\Exports\PipeInstallationExport;
use App\Exports\L2PipeInstallationExport;
use App\Exports\AllPipeInstallationExport;
use \App\Exports\AllPolygonExport;
use DB;
use Log;

class DownloadGeojson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:geojson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'From download geojson, which data is stored in table temp_jobs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       \Log::info('Geojson File Download');
        if (version_compare(phpversion(), '7.1', '>=')) {
            ini_set( 'precision', 17 );
            ini_set( 'serialize_precision', -1 );
        }
        set_time_limit(-1);
        ini_set('memory_limit', '640000M');
        $begin = microtime(true);
        $job=\DB::table('temp_jobs')->where('queue','geojson')->where('attempts',0)->orderBy('id','asc')->first();
        if($job){    

          \Log::info('Job Retrieved:', ['job_id' => $job->id, 'payload' => $job->payload]);
            $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>2,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            $payload=json_decode($job->payload);         
            
            $data=new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]);
           
            $command = $payload->data->command;
            $file_name = $payload->data->filename;

            if($command == '\App\Exports\AllPolygonExport'){//for l1 validator
                $raw = new AllPolygonExport('All',$data->getRequest());

            }
            // elseif($command == '\App\Exports\AllPolygonExport'){//for l2 validator
            //     $raw = new L2PipeInstallationExport('All',$request);
            // }elseif($command == '\App\Exports\AllPolygonExport'){//for l2 validator
            //     $raw = new AllPipeInstallationExport('All',$request);
            // }
      
        // GeometryCollection
        $json=$raw->collection();

        // dd($command , $request , $json);

        $features=[];
          if(count($json)>0){
            foreach($json as $items){
              $rawdata=[];
              $polygon  =  json_decode($items['ranges']);
            //   $polygon_act  =  json_decode($items['ranges']);
              if(is_array($polygon) && count($polygon) > 2 )
              {
                /*If survey has updated polygon then */
                /*for updatedpolygon no need of doing array chunk*/
                $multipolygon = json_decode($items['ranges']);
                foreach($multipolygon as $index=>$array){
                  $data = [0=> floatval($array->lng), 1 =>  floatval($array->lat)];
                  $multipolygon[$index] = $data;
                }
                //check whether first and last coordinates are matching or not
                $firstarray = $multipolygon[0];
                $lastarray = $multipolygon[array_key_last($multipolygon)];
                if($firstarray != $lastarray){
                    $multipolygon[]=$multipolygon[0];
                }
                $multipolygonreverse = [];
                $rawdata[] = [$multipolygon]; //here adding square bracs to match geojson format for multipolygon coordinates need to be deeply nested
              }//end check polygon count
              $features[]=[
                      "type"=> "Feature",
                      "geometry"=>[
                              "type"=>"MultiPolygon",
                              "coordinates"=>$rawdata,
                          ],
                      'properties'=>$items
                  ];
            }//forach end
          }//if json end
          $collection=[
                  "type"=> "FeatureCollection",
                  "features"=>$features
              ];
         $collection=collect($collection)->toJson();
         $filename = $file_name;//'pipe-installation_geojson_'.time().'.geojson';
         $path = base_path('public/geojson/').$filename;
         $handle = fopen($path, 'w+');
         fputs($handle, $collection);
         fclose($handle);
         header('Content-Type: application/octet-stream');
         header("Content-Transfer-Encoding: Binary");
         header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
         readfile(base_path('public/geojson/'.$filename));



         $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
       }//end of if for temp job
        // return Command::SUCCESS;
    }





    public function handle_old()
    {
        if (version_compare(phpversion(), '7.1', '>=')) {
            ini_set( 'precision', 17 );
            ini_set( 'serialize_precision', -1 );
        }
        set_time_limit(-1);
        ini_set('memory_limit', '640000M');
        $begin = microtime(true);
        $job=\DB::table('temp_jobs')->where('queue','geojson')->where('attempts',0)->orderBy('id','asc')->first();
        if($job){    
            $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>2,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            $payload=json_decode($job->payload);         
            $data=new $payload->data->command($payload->data->parameters[0]??'',$payload->data->parameters[1]??[]);
            $command = $payload->data->command;
            $file_name = $payload->data->filename;
            if($command == '\App\Exports\AllPolygonExport'){//for l1 validator
                $raw = new AllPolygonExport('All',$data->getRequest());
            }
            // elseif($command == '\App\Exports\AllPolygonExport'){//for l2 validator
            //     $raw = new L2PipeInstallationExport('All',$request);
            // }elseif($command == '\App\Exports\AllPolygonExport'){//for l2 validator
            //     $raw = new AllPipeInstallationExport('All',$request);
            // }
        // GeometryCollection
        $json=$raw->collection();
        // dd($command , $request , $json);
        $features=[];
          if(count($json)>0){
            foreach($json as $items){
              $rawdata=[];
              $polygon  =  json_decode($items['ranges']);
            //   $polygon_act  =  json_decode($items['ranges']);
              if(is_array($polygon) && count($polygon) > 2 )
              {
                /*If survey has updated polygon then */
                /*for updatedpolygon no need of doing array chunk*/
                $multipolygon = json_decode($items['ranges']);
                foreach($multipolygon as $index=>$array){
                  $data = [0=> floatval($array->lng), 1 =>  floatval($array->lat)];
                  $multipolygon[$index] = $data;
                }
                //check whether first and last coordinates are matching or not
                $firstarray = $multipolygon[0];
                $lastarray = $multipolygon[array_key_last($multipolygon)];
                if($firstarray != $lastarray){
                    $multipolygon[]=$multipolygon[0];
                }
                $multipolygonreverse = [];
                $rawdata[] = [$multipolygon]; //here adding square bracs to match geojson format for multipolygon coordinates need to be deeply nested
              }//end check polygon count
              $features[]=[
                      "type"=> "Feature",
                      "geometry"=>[
                              "type"=>"MultiPolygon",
                              "coordinates"=>$rawdata,
                          ],
                      'properties'=>$items
                  ];
            }//forach end
          }//if json end
          $collection=[
                  "type"=> "FeatureCollection",
                  "features"=>$features
              ];
         $collection=collect($collection)->toJson();
         $filename = $file_name;//'pipe-installation_geojson_'.time().'.geojson';
         $path = base_path('public/geojson/').$filename;
         $handle = fopen($path, 'w+');
         fputs($handle, $collection);
         fclose($handle);
         header('Content-Type: application/octet-stream');
         header("Content-Transfer-Encoding: Binary");
         header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
         readfile(base_path('public/geojson/'.$filename));



         $update=\DB::table('temp_jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
       }//end of if for temp job
        // return Command::SUCCESS;
    }
}
