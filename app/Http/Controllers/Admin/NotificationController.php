<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $notifys = Notification::orderBy('created_at','desc')->get();
       $page_title = 'Notification';
       $page_description = 'Some description for the page';
       $action = 'table_landownerships';
       return view('admin.notification.index', compact('notifys','page_title', 'page_description','action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $users = User::whereHas('roles', function($q){
            $q->where('name', 'AppUser');//fetch user from users table hasrole User
        }
        )->select('id','name','mobile')->with('device')->orderBy('created_at','desc')->get();
        $action = 'form_pickers';
        $page_title = 'Create notifications';
        $page_description = 'Create notifications';
        return view('admin.notification.create',compact('action','page_title','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->notify_selection == 'all'){
            $notify= new Notification;
            $notify->title  =   $request->title;
            $notify->body   =   $request->body;
            $notify->send_by   =   auth()->user()->id;            
            $notify->type   =   $request->notify_selection;
            
            $users = User::whereHas('roles', function($q){
                $q->where('name', 'AppUser');//fetch user from users table hasrole User
            }
            )->select('id','name')->with('device')
            ->whereHas('device')->get();

            $notify->user_count   =    $users->count();//save how much user were found
            $notify->save();

            //now send notification to all
            foreach($users as $id){
                $this->send_notification($request->title, $request->body, $id->device->fcm_token);
            }
        }elseif($request->notify_selection == 'multi-user'){
            $notify= new Notification;
            $notify->title  =   $request->title;
            $notify->body   =   $request->body;
            $notify->send_by   =   auth()->user()->id;
            $notify->type   =   $request->notify_selection;
            $notify->user_count   =   count($request->select_user);//$request->user_count;
            $notify->save();
            foreach($request->select_user as $id){
                $fcm = \DB::table('user_devices')->where('user_id',$id)->select('fcm_token')->first();
                $response = $this->send_notification($request->title, $request->body, $fcm->fcm_token);
            }
            
        }
      if(!$notify){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      return redirect()->route('admin.notification.index')->with('success', 'Send Successfully');
    }

    /**
     * Send notification
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function send_notification($title,$body,$device_id)
    {
        $ids=[];
        if(is_array($device_id)){
            $ids=$device_id;
        }
        else{
            $ids=[$device_id];
        }

        $headers = [
            // 'Authorization' => 'key=AAAA0vOjJRA:APA91bH8LLq7TJytkFVMgRt9m5H_q_AXHv7Rw3zRmaAYVsHECVyy0SJgAZvy_jqVLBTdWfUBIsgS-Dtp9tklQMu4z8zG7CFUP1OD0PUTW5qh43KDhJmrPlId80GE6y4XO2-82tnfnqYB',
            'Authorization' => 'key=AAAAbHv_RbM:APA91bGDwrvuOLljr6VoxlHcHZ0OHzmdNhE9w6f9pd90EceQDju2GWDND-LEJYaN2iJe_hieF341MBUMpoEXb0iDamQ-RqSEtzKzQhfQQi0D_0kfaiUdmzt7zEl4WAurW2e5c2nlP9SK',            
            'Content-Type'  => 'application/json',
        ];
        $data = [
            "registration_ids" => $ids,
            "priority"=> "high",
            //"to" => "/topics/all",
            "notification" => [
                "title" => $title,
                "body" => $body,
                "sound" =>'custom_ringtone.wav',
                "android_channel_id" => 'high_importance_channel',
            ],
            "data" => $data??NULL,
        ];
        $fields = json_encode ( $data );
        $client = new \GuzzleHttp\Client();


        // dd($fields );
        try{
            $request = $client->post("https://fcm.googleapis.com/fcm/send",[
                'headers' => $headers,
                'body' => $fields,
            ]);
            $response = $request->getBody();
            // dd($response);
            return $response;
        }
        catch (\Exception $e){
            return $e;
        }

    }

    


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
