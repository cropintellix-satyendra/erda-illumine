<div class="card" style="margin-right: -24px;margin-left: -24px;">
    <div class="card-body" style="padding-left: 23px;padding-top: 11px;padding-right: 2px;">
      <div class="mb-2 mt-1" style="background-color: #450b5a;width: 107%;margin-left: -21px;height: 43px;">
        <p class="text-center text-white pt-2"><b>CURRENT STATUS</b></p>
      </div>
      <div class="mb-1">
          <div class="row mb-3">
            <a style="width: 30%;" href="" target="" class="active btn btn-status{{$plot->onboarding_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>

            <a style="width: 26%;" href="" target="_blank" class="CropDataShow btn btn-status{{$plot->cropdata_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
            <a style="width: 31%;" href="" target="_blank" class="btn btn-status m-b-0 mr-3"><span class="btn-txt">Polygon</span></a>
            
          </div>
          <div class="row mb-3">
            <a style="width: 31%;" href="{{url('l2/pipeinstallation/plot/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="btn btn-status{{$check_pipedata ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
            <a style="width: 30%;" href="" target="_blank" class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
            <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->benefit_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
            {{-- <a style="width: 30%;" class="btn btn-status{{$plot->other_form ? '-done' : ' disabled'}} m-b-0"><span class="btn-txt">Others</span></a> --}}
          </div>
          @if(!Auth::user()->hasRole('Viewer'))
          <div class="row">
                  <a style="width: 30%;" href=""
                    class="btn btn-info m-b-0 mr-3 EditBtn d-none"
                     @if($plot->status_onboarding == 'Approved')
                      disabled
                    @elseif($plot->status_onboarding == 'Rejected')
                      disabled
                    @else
                    @endif
                  >EDIT</a>
              <!-- end button end -->
            <button style="width: 26%;"
                data-toggle="modal" data-target="#ApproveModal"
                class="btn btn-success ApproveBtn m-b-0 mr-3 d-none" {{-- below code is to disable button if --}}
                  >
                  Approve
                  <i class="fa fa-spinner fa-spin Aspinner"></i>
            </button>
            <!-- approve end -->
            <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark"
                  class="btn btn-danger RejectBtn m-b-0 mr-3 d-none">
                  Reject
            </button>
          </div>
          @endif
          <!-- end final approve module -->
        </div><!-- button end -->
    </div>
</div>
