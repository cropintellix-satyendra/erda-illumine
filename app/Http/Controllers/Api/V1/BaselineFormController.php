<?php

namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BaselineAditionalQuiz;
use App\Models\BaselineCropDetail;
use App\Models\BaselineFarmerDetail;
use App\Models\BaselineFertilizerDetail;
use App\Models\BaselineManureQuiz;
use App\Models\BaselinePersonalQuiz;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\BaselineForm;
use App\Models\BaselineFormDetails;
use Exception;
use Illuminate\Support\Facades\Storage;


class BaselineFormController extends Controller
{

    public function farmer_detail(Request $request)
    {
        Log::info('Request data1:', $request->all());

        $jsonData = json_decode($request->getContent(), true);

        foreach ($jsonData['data'] as $surveyData) {
            $survey = new BaselineFarmerDetail();
            $survey->form_number = $surveyData['formNumber']??"NA";
            $survey->surveyor_id =  auth()->user()->id;
            $survey->date_of_survey = $surveyData['datesurvey']??"NA";
            $survey->farmer_name = $surveyData['farmername']??"NA";
            $survey->mob_no = $surveyData['mobile']??"NA";
            $survey->state = $surveyData['state']??"NA";
            $survey->district = $surveyData['district']??"NA";
            $survey->taluka = $surveyData['taluka']??"NA";
            $survey->panchayat = $surveyData['panchayat']??"NA";
            $survey->village = $surveyData['village']??"NA";
            $survey->total_land = $surveyData['totalland']??"NA";
            $survey->land_ownership = $surveyData['ownership']??"NA";
            $survey->save();
            return response()->json(['success' => true, 'message' => 'Data stored successfully'], 200);

            if(!$survey)
            {
                return response()->json(['error' => true, 'message' => 'Something went wrong'], 422);
            }
        }

    }

    


    public function farmer_crop_detail(Request $request)
    {
        Log::info('Request data:', $request->all());
        
        // Decode JSON data from the request
        $jsonData = json_decode($request->getContent(), true);
        
        // Check if jsonData contains 'data' key and if it's an array
        if (!isset($jsonData['data']) || !is_array($jsonData['data'])) {
            return response()->json(['error' => 'Invalid data format'], 422);
        }
    
        try {
            foreach ($jsonData['data'] as $surveyData) {
                // Create a new BaselineCropDetail instance for each survey data
                $survey = new BaselineCropDetail();
                $survey->form_number = $surveyData['formNumber'] ?? "NA";
                $survey->surveyor_id = auth()->user()->id;
                $survey->year = $surveyData['selectyear'] ?? "NA";
                $survey->season = $surveyData['selectseason'] ?? "NA";
                $survey->variety = $surveyData['thevariety'] ?? "NA";
                $survey->area_nursery = $surveyData['areanursery'] ?? "NA";
                $survey->field_area = $surveyData['fieldarea'] ?? "NA";
                $survey->manure_applied = $surveyData['manureapplied'] ?? "NA";
                $survey->before_sowing = $surveyData['beforesowing'] ?? "NA";
                $survey->quantity_applied = $surveyData['quantityapplied'] ?? "NA";
                $survey->final_grain = $surveyData['Finalgrain'] ?? "NA";
                $survey->status = $surveyData['status'] ?? "NA";
                $survey->save();
            }
    
            // Return success response after all data has been processed
            return response()->json(['success' => true, 'message' => 'Data stored successfully'], 200);
    
        } catch (\Exception $e) {
            // Log the error and return an error response
            Log::error('Error storing farmer crop detail:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    

    public function fertilizer_detail(Request $request)
    {
        Log::info('Request data:', $request->all());
        $jsonData = json_decode($request->getContent(), true);
    

        try {
        foreach ($jsonData['data'] as $formData) {
            $newFormData = new BaselineFertilizerDetail();
            // $newFormData->uid = $formData['uid'];
            $newFormData->form_number = $formData['formNumber']??"NA";
            $newFormData->year = $formData['selectyear']??"NA";
            $newFormData->season = $formData['selectseason']??"NA";
            $newFormData->date = $formData['date_date']??"NA";
            $newFormData->fertiliser = $formData['Fertiliser']??"NA";
            $newFormData->quantity = $formData['quantity']??"NA";
            $newFormData->status = $formData['status']??"NA";
            $newFormData->surveyor_id = auth()->user()->id;
            $newFormData->save();
            }   
        return response()->json(['success' => true, 'message' => 'Data stored successfully'], 200);

    } catch (\Exception $e) {
        // Log the error and return an error response
        Log::error('Error storing farmer crop detail:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Something went wrong'], 500);
    }
    }


    public function additional_quiz(Request $request)
    {
        Log::info('Request data:', $request->all());

        // Validate incoming request data
        $validatedData = $request->validate([
            'data1' => 'array',
            'data2' => 'array',
            'data3' => 'array',

        ]);

        try {
            // Process data1
            foreach ($validatedData['data1'] as $formData) {
                $newFormData = new BaselineAditionalQuiz();
                $newFormData->form_number = $formData['formNumber'] ?? "NA";
                $newFormData->source_of_irrigation = $formData['Irrigationsource'] ?? "NA";
                $newFormData->season = $formData['SelectSession'] ?? "NA";
                $newFormData->crop_growwing = $formData['cropgrowing'] ?? "NA";
                $newFormData->duration_of_main_field = $formData['mainfieldcrops'] ?? "NA";
                $newFormData->duration_of_nursery = $formData['durationnurserycrops'] ?? "NA";
                $newFormData->drain_no = $formData['draintheplot'] ?? "NA";
                $newFormData->no_of_weeding = $formData['weedingevents'] ?? "NA";
                $newFormData->plot_drain_days = $formData['plotremaindrained'] ?? "NA";
                $newFormData->avg_drain_period = $formData['averagedrainageperiod'] ?? "NA";
                $newFormData->awd_practice = $formData['AWDpracticebefore'] ?? "NA";
                $newFormData->stubbles_burn = $formData['burningStubbles'] ?? "NA";
                $newFormData->method_of_sowing = $formData['methodSowing'] ?? "NA";
                $newFormData->micro_finance = $formData['accessMicroFinance'] ?? "NA";
                $newFormData->irrigation_control = $formData['controverirrigation'] ?? "NA";
                $newFormData->seasons_awd = $formData['seasonsfollowingAWD'] ?? "NA";
                $newFormData->change_variety = $formData['wantchangevariety'] ?? "NA";
                $newFormData->surveyor_id = auth()->user()->id;
                $newFormData->save();
            }

            // Process data2
            foreach ($validatedData['data2'] as $data) {
                $baselinePersonalQuiz = new BaselinePersonalQuiz();
                $baselinePersonalQuiz->form_number = $data['formNumber'] ?? "NA";
                $baselinePersonalQuiz->season = $data['SelectSession'] ?? "NA";
                $baselinePersonalQuiz->education = $data['highestEducation'] ?? "NA";
                $baselinePersonalQuiz->member = $data['howmanymembers'] ?? "NA";
                $baselinePersonalQuiz->adult = $data['adult'] ?? "NA";
                $baselinePersonalQuiz->child = $data['children'] ?? "NA";
                $baselinePersonalQuiz->male = $data['male'] ?? "NA";
                $baselinePersonalQuiz->female = $data['female'] ?? "NA";
                $baselinePersonalQuiz->live_in_area = $data['livedinthisarea'] ?? "NA";
                $baselinePersonalQuiz->profession = $data['primaryProfession'] ?? "NA";
                $baselinePersonalQuiz->cultivating_paddy = $data['arecultivatingpaddy'] ?? "NA";
                $baselinePersonalQuiz->change_profession = $data['changetheprofession'] ?? "NA";
                $baselinePersonalQuiz->off_season = $data['youworkinoffseason'] ?? "NA";
                $baselinePersonalQuiz->avg_in_year = $data['onaverageinayear'] ?? "NA";
                $baselinePersonalQuiz->external_labour = $data['haveexternallabors'] ?? "NA";
                $baselinePersonalQuiz->amount = $data['INR'] ?? "NA";
                $baselinePersonalQuiz->per_cost = $data['howmuchitcostsper'] ?? "NA";
                $baselinePersonalQuiz->area = $data['thisarea'] ?? "NA";
                $baselinePersonalQuiz->awd = $data['seewithAWD'] ?? "NA";
                $baselinePersonalQuiz->participation = $data['participatingthisproject'] ?? "NA";
                $baselinePersonalQuiz->interest = $data['afeguardsyourinterests'] ?? "NA";
                $baselinePersonalQuiz->surveyor_id = auth()->user()->id;
                $baselinePersonalQuiz->save();
            }

            // Process data3
            foreach ($validatedData['data3'] as $data) {
                $baselineManure = new BaselineManureQuiz();
                $baselineManure->form_number = $data['formNumber'] ?? "NA";
                $baselineManure->season = $data['SelectSession'] ?? "NA";
                $baselineManure->animal_no = $data['Numberanima'] ?? "NA";
                $baselineManure->keep_animal = $data['keepyouranimals'] ?? "NA";
                $baselineManure->produce_animal = $data['producedanimals'] ?? "NA";
                $baselineManure->amount_manure = $data['amountmanure'] ?? "NA";
                $baselineManure->animals_field = $data['animalswhenthey'] ?? "NA";
                $baselineManure->surveyor_id = auth()->user()->id;
                $baselineManure->farmersignature = $data['farmersignature'] ?? "NA";
                $baselineManure->save();

                // $imageData = $data['farmersignature'];
                // if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                //     $imageData = substr($imageData, strpos($imageData, ',') + 1);
                //     $type = strtolower($type[1]); // jpg, png, gif
                
                //     $imageData = base64_decode($imageData);
                
                //     if ($imageData === false) {
                //         return response()->json(['error' => 'base64_decode failed'], 422);
                //     }
                
                //     $fileName = uniqid() . '.' . $type;
                //     $filePath = config('storagesystems.path') . '/BaselineForm/AdditionalQuiz/' . $fileName;
                
                //     $stored = Storage::disk('s3')->put($filePath, $imageData);
                
                //     if ($stored) {
                //         $baselineManure->farmersignature = Storage::disk('s3')->url($filePath);
                //         $baselineManure->save();
                //     } else {
                //         return response()->json(['error' => 'Failed to store image'], 500);
                //     }
                // } else {
                //     return response()->json(['error' => 'Invalid image data'], 422);
                // }
                
            }

            // $imageStore = BaselineManureQuiz::where('form_number', $newFormData->form_number)->first();
            // $imageData = $validatedData['farmersignature'];

        

            return response()->json(['success' => true, 'message' => 'Data stored successfully'], 200);
        } catch (Exception $e) {
            // dd($e);
            Log::error('Error storing data: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
    }

    public function baseline_form_image(Request $request)
    {
        // Log request data
        Log::info('Request data:', $request->all());
    
        // Validate request
        $request->validate([
            'form_number'  => 'required',
            'farmersignature' => 'required|image',
            'type'  => 'required'
        ]);
    
        // Define the path based on request type
        $path = '';
        if ($request->type == 'baselineform') {
            $path = config('storagesystems.path') . '/baselineForm'.'/'.$request->form_number;
        } elseif ($request->type == 'stakeholderform') {
            $path = config('storagesystems.path') . '/stakeholderForm'.'/'.$request->form_number;
        } else {
            return response()->json(['message' => 'Invalid type'], 400);
        }
    
        // Handle the file upload
        if ($request->hasFile('farmersignature')) {
            $file = $request->file('farmersignature');
            $filePath = Storage::disk('s3')->put($path, $file);
            $url = Storage::disk('s3')->url($filePath);
    
            try {
                // Save to database
                if ($request->type == 'baselineform') {
                    $baselineManureQuiz = BaselineManureQuiz::where('form_number', $request->form_number)
                        ->firstOrFail();
                    $baselineManureQuiz->farmersignature = $url;
                    $baselineManureQuiz->save();
                } elseif ($request->type == 'stakeholderform') {
                    $baselineForm = BaselineForm::where('form_number', $request->form_number)
                        ->firstOrFail();
                    $baselineForm->farmersignature = $url;
                    $baselineForm->save();
                }
                
                return response()->json(['message' => 'Image uploaded successfully'], 201);
            } catch (\Exception $e) {
                Log::error('Database update failed: ' . $e->getMessage());
                return response()->json(['message' => 'Image uploaded but failed to update the database'], 500);
            }
    
        return response()->json(['message' => 'Image upload failed'], 423);
    }
    
        return response()->json(['message' => 'Something Went Wrong!!'], 422);
    }
    


    // $imageData =  $jsonData->farmersignature;
    // $imageData = base64_decode($imageData);
    // if(!$imageData){
    //     return response()->json(['error' => 'something Went Wrong!'], 422);
    // }
    // if (!isset($jsonData['farmersignature'])) {
    //     return response()->json(['error' => 'Farmersignature is missing'], 422);
    // }

    // $imageData = $jsonData['farmersignature'];
    // $imageData = base64_decode($imageData);
    // if (!$imageData) {
    //     return response()->json(['error' => 'something Went Wrong!'], 422);
    // }

    public function baseline_survey(Request $request){

        Log::info('Request data:', $request->all());
        $jsonData = json_decode($request->getContent(), true);


        try {
        foreach ($jsonData['data1'] as $survey) {
            $baseline_form = new BaselineForm();
            $baseline_form->form_number            = $survey['formNumber'];
            $baseline_form->surveyor_id            = auth()->user()->id;
            $baseline_form->surveyor_name          = $survey['surveyconducted'];
            $baseline_form->coordinator_name       = $survey['Coordinatorname'];
            $baseline_form->stakeholder_name       = $survey['nameStakeholder'];
            $baseline_form->age                    = $survey['nameAge'];
            $baseline_form->gender                 = $survey['genderradioGroup'];
            $baseline_form->state                  = $survey['state'];
            $baseline_form->district               = $survey['district'];
            $baseline_form->taluka                 = $survey['taluka'];
            $baseline_form->panchayat              = $survey['panchayat'];
            $baseline_form->village                = $survey['village'];
            $baseline_form->profession             = $survey['profession'];
            $baseline_form->designation            = $survey['designation'];
            $baseline_form->farmsizeframhect       = $survey['farmsizeframhect'];
            $baseline_form->farmsizeAcre           = $survey['farmsizeAcre'];
            $baseline_form->farmsizeGunta          = $survey['farmsizeGunta'];
            $baseline_form->farmsizeHectares       = $survey['farmsizeHectares'];
            $baseline_form->livedinthisarea        = $survey['livedinthisarea'];
            $baseline_form->rabi_cultivation       = $survey['cultivatepaddyRabi'];
            $baseline_form->kharif_cultivation     = $survey['cultivatepaddyKharif'];
            $baseline_form->summer_cultivation     = $survey['cultivatepaddySummer'];
            $baseline_form->primary_profession     = $survey['agricultureProfessionvillage'];
            $baseline_form->irrigation_source      = $survey['irrigationradioGroup'];
            $baseline_form->AWD_progressive_farmer = $survey['AWDfarmersfollowing'];
            $baseline_form->org_or_ngo             = $survey['organisationsngo'];
            $baseline_form->farmersignature = $survey['farmersignature'];

            // // dd($imageData);

            //  // Handle the farmer signature image
            //  $imageData = $survey['farmersignature'];
            //  if ($imageData) {
            //      if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            //          $imageData = substr($imageData, strpos($imageData, ',') + 1);
            //          $type = strtolower($type[1]); // jpg, png, gif
             
            //          $imageData = base64_decode($imageData);
             
            //          if ($imageData === false) {
            //              return response()->json(['error' => 'base64_decode failed'], 422);
            //          }
             
            //          $fileName = uniqid() . '.' . $type;
            //          $filePath = config('storagesystems.path') . '/Stake-Holder-Docs/' . $baseline_form->form_number . '/' . $fileName;
             
            //          // Store the image in the configured storage (e.g., s3, local, etc.)
            //          $stored = Storage::disk('s3')->put($filePath, $imageData);
             
            //          if ($stored) {
            //              $farmersignature = Storage::disk('s3')->url($filePath);
            //              $baseline_form->farmersignature = $farmersignature;
            //              $baseline_form->save();
            //          } else {
            //              return response()->json(['error' => 'Failed to store image'], 500);
            //          }
            //      } else {
            //          return response()->json(['error' => 'Invalid image data'], 422);
            //      }
            //  } else {
            //      return response()->json(['error' => 'Farmer signature is missing'], 422);
            //  }
             
               // Handle the farmer signature image
                // Handle the farmer signature image
            // $imageData = $survey['farmersignature'];
            // if($imageData){
            //     // Remove the "data:image/jpeg;base64," part
            //     $imageData = substr($imageData, strpos($imageData, ',') + 1);
            //     // Decode the base64 data
            //     $imageData = base64_decode($imageData);
                
            //     if ($imageData === false) {
            //         return response()->json(['error' => 'base64_decode failed'], 422);
            //     }

            //     // Generate a unique filename
            //     $fileName = uniqid() . '.jpg';

            //     // Define the storage path
            //     $path = config('storagesystems.path') . '/' . $baseline_form->form_number . '/Stake-Holder-Docs/' . $fileName;
            //     // dd($path);

            //     // Store the image in the configured storage (e.g., s3, local, etc.)
            //     Storage::disk('s3')->put($path, $imageData);

            //     // Retrieve the URL to the stored image
            //     $farmersignature = Storage::disk('s3')->url($path);
            //     // dd($farmersignature);
            //     $baseline_form->farmersignature = $farmersignature;
            // } else {
            //     return response()->json(['error' => 'Farmer signature is missing'], 422);
            // }
   
             $baseline_form->save();


            $baseline_form_details = new BaselineFormDetails();
            $baseline_form_details->form_number            = $survey['formNumber'];
            $baseline_form_details->surveyor_id            = auth()->user()->id;
            $baseline_form_details->trasplantation         = $survey['percentageTrans'];
            $baseline_form_details->broadcasting           = $survey['percentageBroadcast'];
            $baseline_form_details->drilling               = $survey['percentageDrilling'];
            $baseline_form_details->drainage               = $survey['drainageavailablevillage'];
            $baseline_form_details->drainage_percentage    = $survey['forhowmuch'];
            $baseline_form_details->farm_situation         = $survey['relatedirrigation'];
            $baseline_form_details->kharif_irrigation      = $survey['irrigationsKharif'];
            $baseline_form_details->rabi_irrigation        = $survey['irrigationsRabi'];
            $baseline_form_details->summer_irrigation      = $survey['irrigationsSummer'];
            $baseline_form_details->kharif_cropduration    = $survey['cropKharif'];
            $baseline_form_details->rabi_cropduration      = $survey['cropRabi'];
            $baseline_form_details->summer_cropduration    = $survey['cropSummer'];
            $baseline_form_details->cultivating_years      = $survey['cultivatingyears'];
            $baseline_form_details->variety_change         = $survey['changetheVariety'];
            $baseline_form_details->secondary_profession   = $survey['intheoffseason'];
            $baseline_form_details->mgnrega_in_work        = $survey['mGNREGAaverageyear'];
            $baseline_form_details->labour_cost            = $survey['perseasonApprox'];
            $baseline_form_details->kharif_inr             = $survey['KharifINR']; 
            $baseline_form_details->rabi_inr               = $survey['rabiINR'];
            $baseline_form_details->summer_inr             = $survey['summerINR'];
            $baseline_form_details->microfinance_or_loans  = $survey['microfinanceloans'];
            $baseline_form_details->mention_name           = $survey['mentionname'];
            $baseline_form_details->natural_risks          = $survey['keepyouranimals'];
            $baseline_form_details->awd_suitable           = $survey['AWDsuitableVillage'];
            $baseline_form_details->awd_benefits           = $survey['benefitsofAWD'];
            $baseline_form_details->farmer_benefits        = $survey['benefittedproject'];
            $baseline_form_details->farmer_interested      = $survey['farmersinterests'];
            $baseline_form_details->any_suggestions        = $survey['anySuggestions'];
            $baseline_form_details->save();
        }

        return response()->json(['success' => true, 'message' => 'Data stored successfully'], 200);

   } catch (Exception $e) {
        Log::error('Error storing data: ' . $e->getMessage());
        return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }

}



}