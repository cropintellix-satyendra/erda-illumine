<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Reject Remark</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
        </button>
    </div>
    <div class="modal-body">
      <div class="container">
        <div class="row">
          <div class="col" style="font-size: 15px;">
           1. <strong>Farmer Registration</strong>
          </div>
          <div class="col">
          </div>
        </div>
                  <div class="row">
                    <div class="col">
                        <label for="plotno" style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;
                          <input type="checkbox"  id="plotno{{$plot->plot_no}}"
                            {{ $plot->status == 'Rejected' ? 'checked disabled' : ''}}
                            {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                          name="plotno" value="{{$plot->plot_no}}" {{$plot->status_onboarding == 'Approved' ?' ':''}}>&nbsp;
                          {{ $plot->status == 'Rejected' ? '(Rejected)' : ''}}{{ $plot->status == 'Approved' ? '(Approved)' : ''}}
                          {{ $plot->check_update == '1' ? '(Validate)' : ''}}
                    </div>
                    <div class="col">
                         <select {{ $plot->status == 'Rejected' ? 'disabled' : ''}} {{ $plot->status == 'Approved' ? 'disabled' : ''}} id="reasons{{$plot->plot_no}}"
                                    data-plot="{{$plot->plot_no}}"
                                    name="reasons" class="form-control select2">
                             <option value="">Select Reasons</option>
                              @foreach($reject_module as $list)
                                <option value="{{$list->id}}" {{$plot->reason_id == $list->id ? 'Selected' :''}}>{{$list->reasons}}</option>
                              @endforeach
                         </select>
                    </div>
                    <div style="margin: 0px 0px 0px 17px;">
                        <label for="reject_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                        <textarea {{ $plot->status == 'Rejected' ? 'readonly' : ''}}  {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                  class="form-control" id="reject_comment{{$plot->plot_no}}"
                                   name="reject_comment" rows="3" cols="50">{{$plot->reject_comment}}</textarea>
                    </div><br>&nbsp;
                  </div>

                  <div class="row">
                        <div class="col mt-2">
                          {{$plot->FinalUserApprovedRejected->name??''}}   /  {{ Carbon\Carbon::parse($plot->reject_timestamp)->toDayDateTimeString() }}<br>
                          @if(Auth::user()->hasRole('SuperAdmin'))
                            <a  target="_blank"  href="{{Route('admin.validator.edit',$plot->FinalUserApprovedRejected->id??'')}}">{{$plot->FinalUserApprovedRejected->email??""}}</a>
                          @else
                          {{$plot->FinalUserApprovedRejected->email??''}}
                          @endif
                        </div>
                        <div class="col">
                          <button {{ $plot->status == 'Rejected' ? 'disabled' : ''}} {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                   type="button" class="btn btn-primary FarmerReject float-right"
                                   data-rejectplot="{{$plot->plot_no}}" style="margin-top: 5px;">Save plot no {{$plot->plot_no}}
                                   <i id="Rspinner{{$plot->plot_no}}" class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
                        </div>
                      </div>
                  <hr style="margin-top: 51px;">
      </div>
    </div>
    {{-- <div class="modal-footer">
        <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary FarmerReject">Save <i class="fa fa-spinner fa-spin  d-none"></i></button>
    </div> --}}
    </form>
</div>
