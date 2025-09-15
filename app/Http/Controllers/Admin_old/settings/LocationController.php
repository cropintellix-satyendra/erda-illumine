<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Taluka;
use App\Models\Village;
use App\Models\LandUnit;
use App\Models\User;
use App\Models\Company;
use App\Models\Panchayat;
use App\Models\Minimumvalue;
use DB;
use App\Models\VendorLocation;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function location_list()
    {
        $States = State::orderBy('id','desc')->get();


        $page_title = 'Locations';
        $page_description = 'Some description for the page';
   		  $action = 'table_landownerships';//using same landownership because here css and js link will for for location also
        return view('admin.settings.Location.index',compact('States','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function district_list()
    {
        $districts = DB::table('districts')
            ->join('states', 'districts.state_id', '=', 'states.id')
            ->select('districts.*', 'states.name as state_name')
            ->orderBy('districts.id', 'desc')
            ->get();

        $page_title = 'Locations';
        $page_description = 'Some description for the page';
   		  $action = 'table_landownerships';//using same landownership because here css and js link will for for location also
        return view('admin.settings.Location.district_list',compact('districts','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function taluka_list()
    {

        $Talukas = Taluka::orderBy('id','desc')->get();


        $page_title = 'Locations';
        $page_description = 'Some description for the page';
   		  $action = 'table_landownerships';//using same landownership because here css and js link will for for location also
        return view('admin.settings.Location.taluka_list',compact( 'Talukas','page_title', 'page_description','action'));
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function panchayat_list()
    {


        $Panchayats = Panchayat::orderBy('id','desc')->get();


        $page_title = 'Locations';
        $page_description = 'Some description for the page';
   		  $action = 'table_landownerships';//using same landownership because here css and js link will for for location also
        return view('admin.settings.Location.panchayat_list',compact('Panchayats','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the villages.
     *
     * @return \Illuminate\Http\Response
     */
    public function villages_list()
    {
      $Villages = Village::query();
      $Villages->join('talukas', 'villages.taluka_id', '=', 'talukas.id')
        ->join('districts', 'talukas.district_id', '=', 'districts.id')
        ->join('states', 'districts.state_id', '=', 'states.id')
        ->join('panchayats', 'villages.panchayat_id', '=', 'panchayats.id');
        if(request()->search){
          $Villages->where('villages.village', 'like', '%'.request()->search.'%');
        }
        if(request()->states){
          if(count(request()->states) > 0){
            $Villages->whereIn('villages.state_id',request()->states);
          } 
        }
               
        
        $Villages->select('villages.*', 'talukas.taluka', 'districts.district', 'states.name as state_name', 'panchayats.panchayat')
        ->orderBy('villages.id', 'desc');
        $Villages = $Villages->paginate(request()->limit??'20');
        $request = request()->all();
        $states = State::all();
        $page_title = 'Village';

        $page_description = 'Some description for the page';
   		  $action = 'table_landownerships';//using same landownership because here css and js link will for for location also
        return view('admin.settings.Location.villages_list',compact( 'Villages','page_title', 'page_description','action','states','request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_state()
    {
      $action = 'form_pickers';
      $page_title = 'Create state';
      $method = "@method('POST')";
      $minimum=Minimumvalue::get();
      $landunit=LandUnit::get();
      return view('admin.settings.Location.state_form',compact('action','method','page_title','minimum','landunit'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_state(Request $request)
    {
      $state = new State;
      $state->name = $request->state;
      $state->country_id = '101';
      $state->status = $request->status;
      $state->units = $request->units;
      $state->base_value = $request->base_value;
      $state->land_unit_id = $request->land_unit_id;
      $state->min_base_value = $request->min_base_value;
      $state->max_base_value = $request->max_base_value;
      $state->save();
      if(!$state){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.location').'#state')->with('success', 'Saved Successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_state($id)
    {
      $State = State::find($id);
      $action = 'form_pickers';
      $page_title = 'Edit state';
      $landunit=LandUnit::get();
      return view('admin.settings.Location.state_form',compact('action','State','page_title','landunit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_state(Request $request, $id)
    {
      $state = State::find($id);
      $state->name = $request->state;
      $state->country_id = '101';
      $state->status = $request->status;
      $state->units = $request->units;
      $state->land_unit_id = $request->land_unit_id;
      $state->min_base_value = $request->min_base_value;
      $state->base_value = $request->base_value;
      $state->max_base_value = $request->max_base_value;
      $state->save();
      if(!$state){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.location').'#state')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_state(Request $request)
    {
      try {
            $state = State::destroy($request->id);
            if(!$state){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_district()
    {
      $States = State::whereCountryId(101)->orderBy('id','asc')->get();
      $action = 'form_pickers';
      $method = "@method('POST')";
      $page_title = 'Create districts';
      return view('admin.settings.Location.district_form',compact('action','States','method','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_district(Request $request)
    {
      $validatedData = $request->validate([
        'district' => 'required',
        'state_id' => 'required',
      ]);
      $district = new District;
      $district->district = $request->district;
      $district->state_id = $request->state_id;
      $district->status = $request->status;
      $district->save();
      if(!$district){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.settings.Location.district_list').'#district')->with('success', 'Saved Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_district($id)
    {
      $States = State::whereCountryId(101)->get();
      $District = District::find($id);
      $action = 'form_pickers';
      $page_title = 'Edit districts';
      return view('admin.settings.Location.district_form',compact('action','District','States','page_title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_district(Request $request, $id)
    {
      $district = District::find($id);
      $district->district = $request->district;
      $district->state_id = $request->state_id;
      $district->status = $request->status;
      $district->save();
      if(!$district){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.district').'#district')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_district(Request $request)
    {
      try {
            $District =District::destroy($request->id);
            if(!$District){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_taluka()
    {
      $States = State::whereCountryId(101)->get();
      $Districts = District::all();
      $action = 'form_pickers';
      $method = "@method('POST')";
      $page_title = 'Create Taluka';
      return view('admin.settings.Location.taluka_form',compact('action','States','Districts','method','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_taluka(Request $request)
    {
      $Taluka =Taluka::select('taluka')->where('taluka',$request->taluka)->first();
      if($Taluka){
        return redirect()->back()->with('error', 'Taluka already exists');
      }
      $taluka = new Taluka;
      $taluka->taluka = $request->taluka;
      $taluka->district_id = $request->district_id;
      $taluka->state_id = $request->state_id;
      $taluka->status = $request->status;
      $taluka->save();
      if(!$taluka){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.taluka').'#taluka')->with('success', 'Saved Successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_taluka($id)
    {
      $States = State::whereCountryId(101)->get();
      $Districts = District::all();
      $taluka = Taluka::find($id);
      $action = 'form_pickers';
      $page_title = 'Edit Taluka';
      return view('admin.settings.Location.taluka_form',compact('action','States','taluka','Districts','page_title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_taluka(Request $request, $id)
    {
      $taluka = Taluka::find($id);
      $taluka->taluka = $request->taluka;
      $taluka->district_id = $request->district_id;
      $taluka->state_id = $request->state_id;
      $taluka->status = $request->status;
      $taluka->save();
      if(!$taluka){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.taluka').'#taluka')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_taluka(Request $request)
    {
      try {
            $taluka =Taluka::destroy($request->id);
            if(!$taluka){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_panchayat()
    {
      $districts = District::all();
      $States = State::whereCountryId(101)->get();
      $Talukas = Taluka::all();
      $action = 'form_pickers';
      $method = "@method('POST')";
      $page_title = 'Create Panchayat';
      return view('admin.settings.Location.panchayat_form',compact('action','districts','States','Talukas','method','page_title'));
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_panchayat(Request $request)
    {
      $panchayat =Panchayat::select('panchayat')->where('panchayat',$request->panchayat)->first();
      if($panchayat){
         return redirect()->back()->with('error', 'panchayat already exists');
       }
      $Taluka = Taluka::whereId($request->taluka_id)->first();
      $panchayats = new Panchayat;
      $panchayats->panchayat = $request->panchayat;
      $panchayats->taluka_id  = $request->block_id;
      $panchayats->district_id  =  $request->district_id;
      $panchayats->state_id = $request->state_id;
      $panchayats->save();
      if(!$panchayats){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.panchayat').'#villagepanchayat')->with('success', 'Saved Successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_panchayat($id)
    {
      $States = State::all();
      $District = District::all();
      $Talukas = Taluka::all();
      $Panchayat = Panchayat::find($id);
      $action = 'form_pickers';
      $page_title = 'Edit Panchayat';
      return view('admin.settings.Location.panchayat_form',compact('action','Panchayat','Talukas','States','District','page_title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_panchayat(Request $request, $id)
    {
      $Taluka = Taluka::whereId($request->taluka_id)->first();
      $panchayat = Panchayat::find($id);
      $panchayat->panchayat = $request->panchayat;
      $panchayat->taluka_id = $request->block_id;
      $panchayat->district_id = $request->district_id;
      $panchayat->state_id = $request->state_id;
      $panchayat->save();
      if(!$panchayat){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.panchayat').'#villagepanchayat')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_panchayat(Request $request)
    {
      try {
            $panchayat =Panchayat::destroy($request->id);
            if(!$panchayat){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_village()
    {
      $states = State::all();
      $Talukas = Taluka::all();
      $panchayats = Village::all();
      $district = District::all();
      $action = 'form_pickers';
      $method = "@method('POST')";
      $page_title = 'Create village';
      return view('admin.settings.Location.village_form',compact('action','district','states','Talukas','panchayats','method','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_village(Request $request)
    {
      $village =village::select('village')->where('village',$request->village)->first();
      if($village){
        return redirect()->back()->with('error', 'Village already exists');
      }
      $village = new Village;
      $village->village = $request->village;
      $village->state_id  = $request->state_id;
      $village->district_id  =  $request->district_id;
      $village->taluka_id = $request->block_id;
      $village->panchayat_id  =  $request->panchayat_id;
      $village->save();
      if(!$village){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.villages').'#village')->with('success', 'Saved Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_village($id)
    {
      $states = State::all();
      $Talukas = Taluka::all();
      $panchayats = Village::all();
      $district = District::all();
      $Village = Village::find($id);
      $action = 'form_pickers';
      $page_title = 'Edit village';
      return view('admin.settings.Location.village_form',compact('action','states','Village','panchayats','Talukas','page_title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_village(Request $request, $id)
    {
      $village = Village::find($id);
      $village->village = $request->village;
      $village->panchayat_id  =  $request->panchayat_id;
      $village->taluka_id = $request->block_id;
      $village->district_id  = $request->district_id;
      $village->state_id  = $request->state_id;
      $village->save();
      if(!$village){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.villages').'#village')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_village(Request $request)
    {
      try {
            $village =Village::destroy($request->id);
            if(!$village){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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

    /**
     * Display a listing of the resource through api response.
     *
     * @return \Illuminate\Http\Response
     */
     public function getState(){
       try{
         $state = State::whereCountryId(101)->select('id','name','units','base_value','max_base_value')
                        ->where('status',1)->orderBy('name','asc')->get();
         return response()->json(['success'=>true,'state'=>$state],200);
       }catch(Exception $e){
         return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
       }
     }



     /**
      * Display a listing of the resource through api response.
      *
      * @return \Illuminate\Http\Response
      */
      public function getDistricts($id){
        try{
            $District = District::query();
            $District = $District->where('state_id',$id)->where('status',1)->orderBy('district','asc');
            if(auth()->user()){
                if(auth()->user()->hasRole('L-1-Validator')){
                    $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                    $District = $District->whereIn('id',explode(',',$VendorLocation->district));
                }
            }
            if(auth()->user()){
                if(auth()->user()->hasRole('L-2-Validator')){
                    $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                    $District = $District->whereIn('id',explode(',',$VendorLocation->district));
                }
            }
            $District = $District->get();
          return response()->json(['success'=>true,'district'=>$District],200);
        }catch(Exception $e){
          return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
        }
      }

      /**
      * Display a listing of the resource through api response.
      *
      * @return \Illuminate\Http\Response
      */
      public function districts(Request $request){
        try{
            $user=User::where('id',$request->id)->first();
            $company=Company::where('company_code' ,$user->company_code)->select('district_id')->first();
            $district = District::whereIn('id',explode(',',$company->district_id))->get();
          return response()->json(['success'=>true,'district'=>$district],200);
        }catch(Exception $e){
          return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
        }
      }

      /**
       * Display a listing of the resource through api response.
       *
       * @return \Illuminate\Http\Response
       */
       public function getTaluka($id){
         try{
            $Taluka = Taluka::query();
            $Taluka = $Taluka->where('district_id',$id)->where('status',1)->orderBy('taluka','asc');
            if(auth()->user()){
                if(auth()->user()->hasRole('L-1-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $Taluka = $Taluka->whereIn('id',explode(',',$VendorLocation->taluka));
                }
            }
            $Taluka = $Taluka->get();
           return response()->json(['success'=>true,'Taluka'=>$Taluka],200);
         }catch(Exception $e){
           return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
         }
       }


       /**
        * Display a listing of the resource through api response.
        *
        * @return \Illuminate\Http\Response
        */
        public function village_panchayat($id){
          try{
            $panchayat = Panchayat::query();
            $panchayat = $panchayat->where('taluka_id',$id)->orderBy('panchayat','asc');
            // if(auth()->user()){ will use in future
            //     if(auth()->user()->hasRole('L-1-Validator')){
            //         $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
            //         $panchayat = $panchayat->whereIn('id',explode(',',$VendorLocation->panchayat));
            //     }
            // }
            $panchayat = $panchayat->get();
            return response()->json(['success'=>true,'panchayat'=>$panchayat],200);
          }catch(Exception $e){
            return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
          }
        }

       /**
        * Display a listing of the resource through api response.
        *
        * @return \Illuminate\Http\Response
        */
        public function getVillage($id){
          try{
            $Village = Village::query();
            $Village = $Village->where('panchayat_id',$id)->orderBy('village','asc');
            // if(auth()->user()){ will use in future
            //     if(auth()->user()->hasRole('L-1-Validator')){
            //         $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
            //         $Village = $Village->whereIn('id',explode(',',$VendorLocation->village));
            //     }
            // }
            $Village = $Village->get();
            return response()->json(['success'=>true,'Village'=>$Village],200);
          }catch(Exception $e){
            return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
          }
        }
}
