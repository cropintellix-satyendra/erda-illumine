@extends('layout.default')
@section('content')
<div class="container-fluid">
  <!-- row -->
  
  <div id="countBoxRow" class="row  ">

                    <div class=" bg-info countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="PlotCount">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Total Plot
                                </h6>
                            </div>
                        </div>
                    </div>



                    <div class=" bg-success countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="RecordApproved">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Approved Plots
                                </h6>
                            </div>
                        </div>
                    </div>



                    <div class=" bg-info countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center pi-font text-white" id="RecordPending">0</h2>
                                <h6 class="text-uppercase-mb-3 pin-font font-size-16 text-white text-center">Pending Plots</h6>
                            </div>
                        </div>
                    </div>


                    <div class=" bg-warning countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="RecordRejected">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Rejected Plots
                                </h6>
                            </div>
                        </div>
                    </div>




                    <div class=" bg-success cc countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center tt-font text-white" id="TotalArea">0</h2>
                                <h6 class="text-uppercase-mb-3 tt-font text-white text-center">
                                    Total Area in Hectare<span class="a-font">(Approved)</span></h6>
                            </div>
                        </div>
                    </div>
                    
                    <div class=" bg-warning countBox">
                        <div class="card-body mini-stat-img boxm">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Others
                                </h6>
                            </div>
                        </div>
                    </div>

                </div>
  <!-- end new  -->
    <div class="row">
        <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Approved Farmers</h4>
                <div class="dropdown ml-auto">
                    @can('Download Excel')
                      <div class="btn-link" data-toggle="dropdown">
                        Download Excel<i class="fa fa-arrow-down" aria-hidden="true"></i>
                      </div>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item download-excel" data-type="onboarding" href="{{url('admin/download/approved/file')}}">Farmer onboarding</a>
                        <a class="dropdown-item download-excel" data-type="cropdata" href="{{url('admin/download/approved/file')}}">Crop data</a>
                        <a class="dropdown-item download-excel" data-type="PipeInstalltion" href="{{url('admin/download/approved/file')}}">PipeInstallation</a> 
                        <a class="dropdown-item download-excel" data-type="benefitsdata" href="{{url('admin/download/approved/file')}}">Benefits</a>
                        <a class="dropdown-item download-excel" data-type="Aeration" href="{{url('admin/download/approved/file')}}">Aerations</a>
                        <a class="dropdown-item download-excel" data-type="PipeGeojson" href="{{url('admin/download/approved/file')}}">Pipe Geojson</a>
                    </div>
                    @endcan                  
                </div>
              </div>
                <div class="card-body">
                    <!-- start filter -->
                    <div class="card-body card-filter flex-container farmer-filter px-0">
                            <form class="row gx-3 gy-2 align-items-center form-filter">
                                    <div class="col-sm-4">
                                        <div class="input-daterange input-group" data-date-format="dd M, yyyy"  data-date-autoclose="true"  data-provide="datepickers">
                                            <input type="text" class="form-control start_date" name="start" placeholder="To"/>
                                            <input type="text" class="form-control end_date" name="end" placeholder="From"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                            <select id="states" onchange="FetchDistrict(this.value)" name="state" class="form-control select2">
                                                    <option value="">States</option>
                                                    @if($states)
                                                    @foreach($states as $state)
                                                        <option value="{!! $state->id !!}">{!! $state->name !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                            <select id="districts" onchange="FetchBlock(this.value)"  name="district" class="form-control select2">
                                                    <option value="">Districts</option>
                                                    @if($districts)
                                                    @foreach($districts as $district)
                                                    <option value="{!! $district->id !!}">{!! $district->district !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                            <select id="talukas" onchange="FetchPanchayat(this.value)" name="taluka" class="form-control select2">
                                                    <option value="">Taluka</option>
                                                    @if($talukas)
                                                    @foreach($talukas as $taluka)
                                                    <option value="{!! $taluka->id !!}">{!! $taluka->taluka !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                            <select id="panchayats" name="panchayats" onchange="FetchVillage(this.value)" class="form-control select2">
                                                    <option value="">Select Panchayat</option>
                                                    @if($panchayats)
                                                    @foreach($panchayats as $panchayat)
                                                    <option value="{!! $panchayat->id !!}">{!! $panchayat->panchayat !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                            <select id="villages" name="village" class="form-control select2">
                                                    <option value="">Village</option>
                                                    @if($villages)
                                                    @foreach($villages as $village)
                                                    <option value="{!! $village->id !!}">{!! $village->village !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2 mt-2">
                                            <select id="farmer_status" name="farmer_status" class="form-control select2">
                                                    <option value="">Select Status</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Approved">Approved</option>
                                                    <option value="Rejected">Rejected</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-2 mt-2">
                                            <select id="executive_onboarding" name="executive_onboarding" class="form-control select2">
                                                    <option value="">Select Executive</option>
                                                    @if($onboarding_executive)
                                                    @foreach($onboarding_executive as $excutive)
                                                        <option value="{{$excutive->surveyor_id}}">{{$excutive->surveyor_name}}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2 mt-2">
                                            <select id="module" name="module" class="form-control select2">
                                                    <option value="">Select Module</option>
                                                    <option value="CropData">Crop Data</option>
                                                    <option value="Benefit">Benefits Data</option>
                                                    <option value="PipeInstalltion">PipeInstallation</option>
                                                    <option value="Aeration">Aeration</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <select id="l2_validator" name="l2_validator" class="form-control select2">
                                                    <option  value="">Select L2 validator</option>
                                                    @if($l2_validators)
                                                    @foreach($l2_validators as $list)
                                                        <option value="{!! $list->id !!}">{!! $list->name !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-rounded btn-danger filter-remove"><span class="btn-icon-start text-dangers"><i class="fa fa-filter color-danger"></i> </span>Clear</button>
                                    </div>
                                </form>
                    </div> <!--card end div tag-->
                    <!-- end filter -->
                    <div class="table-responsive">
                        <table id="example3" class="table table-bordered dt-responsive nowrap display data-table" style="min-width: 845px">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Farmer Name</th>
                                    <th>Plot No.</th>
                                    <th>Plot Area</th>
                                    <th>Mobile</th>
                                    <th>State</th>
                                    <th>District</th>
                                    <th>Taluka</th>
                                    <th>Village</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')

 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js')}}"></script>


<script>
$(function(){
    setInterval(function () {
            if(screen.width<992){
                swal("Important!", "Recommended to use on Desktop.")
            }
        }, 5000)
        
        
    $('input[name="start"],input[name="end"]').each(function() {
        $(this).datepicker({format:' yyyy-m-dd',autoclose:true});
    });
    var table=$('.data-table').DataTable({
        "processing": true,
        "serverSide": true,
        'responsive': true,
        "pageLength": 10,
        "ajax": {
            "url": "",
            "type": "GET",
                //"data": $('form.form-filter').serializeArray(),//{'_token': "{!! csrf_token() !!}"},
                "data":function(data){
                        data._token="{!! csrf_token() !!}";
                        data.role="{{auth()->user()->roles->first()->name}}";
                        $('.start_date').each(function(){
                            data['start_date'] =$(this).val();
                        });
                        $('.end_date').each(function(){
                            data['end_date'] =$(this).val();
                        });
                        $('.card-filter select').each(function(){
                            if($(this).val()){
                                    data[$(this).attr('name')]=$(this).val();
                            }
                        });
                }
        },
        "aaSorting": [[ 0, "desc" ]],
        "columns": [
            { "data": 'farmer_plot_uniqueid',"name":'farmer_plot_uniqueid','width':'5%',"searchable": true,},
            { "data": "farmer_name","name":"farmer_name","defaultContent":""},//'visible':false,'width':'0%'},
            { "data": "plot_no","name":"plot_no"},
            { "data": "area_in_acers","name":"area_in_acers"},
            { "data": "mobile","name":"mobile","defaultContent":""},
            { "data": "state","name":"state"},
            { "data": "district","name":"district"},
            { "data": "taluka","name":"taluka"},
            { "data": "village","name":"village"},
            { "data": "final_status","name":"final_status","defaultContent":"",'orderable': false,},
            { "data": 'id',"name":'id','orderable': false, 'searchable': false,},
        ],
        "columnDefs": [
            {render: function (data, type, row, meta) {
                    var status_color='text-info';
                    if(row.final_status == 'Approved'){
                        status_color='text-success';
                    }
                    if(row.final_status == 'Rejected'){
                        status_color='text-danger';
                    }
                      // return '<span class="btn '+status_color+'" title="Show">'+row.farmer_plot_uniqueid+'</span>';
                    //   return '<a class="btn '+status_color+'" href="{{ url('admin/approved/farmers/show')}}/'+row.farmer_uniqueId+'" title="Show">'+row.farmer_plot_uniqueid+'</a>';
                      return '<span class="btn '+status_color+'" >'+row.farmer_plot_uniqueid+'</span>';
                },
                "targets": 0,
            },
            {render: function (data, type, row, meta) {
                    var status_color='text-info';
                    if(row.final_status_onboarding == 'Approved'){
                        status_color='text-success';
                    }
                    if(row.final_status_onboarding == 'Rejected'){
                        status_color='text-danger';
                    }
                    return '<a class="btn '+status_color+'" href="{{ url('admin/farmer/approved/plot')}}/'+row.farmer_plot_uniqueid+'" title="Show">'+data+'</a>';
                },
                "targets": 2,
            },
            {render: function (data, type, row, meta) {
                    if(data == 'Approved'){
                        return '<span class="badge badge-success">Approved</span>';
                    }
                    if(data == 'Pending'){
                            return '<span class="badge badge-info">Pending</span>';
                    }
                    if(data == 'Rejected'){
                        return '<span class="badge badge-danger">Rejected</span>';
                    }
                },
                "targets": -2,
            },
            {render: function (data, type, row, meta) {
                return '<div class="row-actions">'
                        +' <a class="btn btn-sm btn-success waves-effect waves-light" href="{{ url('admin/farmer/approved/plot')}}/'+row.farmer_plot_uniqueid+'" data-toggle="tooltip" title="Show"><i class="ti-eye"></i></a>'
                       +'</div>';
                },
                "targets": -1,
            },
        ]
    });
    $('.card-filter select').on('change',function(){
        table.draw();
      });

      $('.start_date').on('change',function(){
        table.draw();
      });
      $('.end_date').on('change',function(){
        table.draw();
      });

      $('.download-excel').on('click',function(e){
              e.preventDefault();
              var type=$(this).attr('data-type');
              var url=$(this).attr('href');
              var start_date=$('input[name="start"]').val();
              var end_date=$('input[name="end"]').val();
              // var season=$('select[name="seasons"]').val();
              var state=$('select[name="state"]').val();
              var district=$('select[name="district"]').val();
              var taluka=$('select[name="taluka"]').val();
              var panchayats=$('select[name="panchayats"]').val();
              var village=$('select[name="village"]').val();
              var farmer_status=$('select[name="farmer_status"]').val();
              var executive_onboarding =$('select[name="executive_onboarding"]').val();
              var modules = $('select[name="module"]').val();
              var file = 'excel';
              var status = '{{$status}}';
              var rolename = '{{\Auth::user()->roles->first()->name}}';
              var userid = '{{\Auth::user()->id}}';
              var l2_validator = $('select[name="l2_validator"]').val(); 
              var parms=$.param(clean({start_date,end_date,state,district,taluka,panchayats,village,farmer_status,executive_onboarding,file,type,status,modules,userid,rolename,l2_validator}));
              url+=(parms)?'?'+parms:'';

              // window.open(url, '_blank');

              if(type == 'PipeGeojson'){
                   window.open(url, '_blank');
                   return false;
              }

              $.ajax({
					url:url,
					success:function(data){
						//console.log(data);
						Swal.fire('Request Submitted!', data.message, 'success')
					}
				});
          });
          //code for count of data store in DB
          $.ajax({
          type:'get',
          url: "{{url('admin/fetch/appoved/counting')}}",
          dataType: 'Json',
          success: function(data) {
            //   document.getElementById("FarmerCount").innerHTML = data.farmer_count;
              document.getElementById("PlotCount").innerHTML = data.plot_count;
              document.getElementById("RecordApproved").innerHTML = data.approved;
              document.getElementById("RecordPending").innerHTML = data.pendings;
              document.getElementById("RecordRejected").innerHTML = data.rejected;
              document.getElementById("TotalArea").innerHTML = data.totalarea;
          },
          error: function (jqXHR, textStatus, errorThrown) {

          }
      });
});


$('.filter-remove').on('click',function(e){
		e.preventDefault();
		//$('.form-filter select').prop('selectedIndex',0);
		//$(".form-filter select").val('').trigger('change');
		//$(".form-filter select").val('').trigger('change.select2');
		$(".form-filter select").val("").trigger('change');
		$('#districts').append('<option value="">Districts</option>');
		$('#talukas').append('<option value="">Taluka</option>');
		$('#panchayats').append('<option value="">Select Panchayat</option>');
		$('#villages').append('<option value="">Village</option>');
		$(".start_date").val('').trigger('change');
		$(".end_date").val('').trigger('change');

});

function FetchDistrict(Id){
    var stateID = Id;//$(this).val();
    if(stateID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/district')}}/"+stateID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':stateID},
            success: function(data) {
                $('select[name="district"]').empty();
								$('select[name="district"]').append('<option value="">Select District</option>');
                $.each(data.district, function(key, value) {
                    $('select[name="district"]').append('<option value="'+ value.id +'">'+ value.district +'</option>');
                });
            }
        });
    }else{
        $('select[name="district"]').empty();
    }
}

function FetchBlock(Id){
    var districtID = Id;//$(this).val();
    if(districtID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/block')}}/"+districtID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':districtID},
            success: function(data) {
                $('select[name="taluka"]').empty();
								$('select[name="taluka"]').append('<option value="">Select Taluka</option>');
                $.each(data.Taluka, function(i, v) {
                    $('select[name="taluka"]').append('<option value="'+ v.id +'">'+ v.taluka +'</option>');
                });
            }
        });
    }else{
        $('select[name="taluka"]').empty();
    }
}

function FetchPanchayat(Id){
    var blockID = Id;//$(this).val();
    if(blockID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/panchayat')}}/"+blockID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':blockID},
            success: function(data) {
                $('select[name="panchayats"]').empty();
								$('select[name="panchayats"]').append('<option value="">Select Panchayat</option>');
                $.each(data.panchayat, function(i, v) {
                    $('select[name="panchayats"]').append('<option value="'+ v.id +'">'+ v.panchayat +'</option>');
                });
            }
        });
    }else{
        $('select[name="panchayats"]').empty();
    }
}

function FetchVillage(Id){
    var PanchayatID = Id;//$(this).val();
    if(PanchayatID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/village')}}/"+PanchayatID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':PanchayatID},
            success: function(data) {
                $('select[name="village"]').empty();
								$('select[name="village"]').append('<option value="">Select Village</option>');
                $.each(data.Village, function(i, v) {
                    $('select[name="village"]').append('<option value="'+ v.id +'">'+ v.village +'</option>');
                });
            }
        });
    }else{
        $('select[name="village"]').empty();
    }
}

function applyFilters(e) {
    var url = window.location.pathname;
    // var date_start  =  $('input[name="start"]').val();
    // var date_end    =  $('input[name="end"]').val();
    // var seasons      =  $('select[name="seasons"]').val();
    // var crop_name   =  $('select[name="crop_name"]').val();
    // var country     =  $('select[name="country"]').val();
    var state='Telangana';//$('select[name="state"]').val();
    // var district=$('select[name="district"]').val();
    // var type=$('select[name="type"]').val();

    var parms=$.param(clean({state}));
    url+=(parms)?'?'+parms:'';
		// ajax
    $.ajax({
	  type:'get',
	  url:url,
	  success:function(data){

	  }
  });
	// ajax end


}
function clean(obj){
	for (var propName in obj) {
	    if (!obj[propName]) {
	      delete obj[propName];
	    }
	  }
	  return obj;
}


	$('.delete-Farmer').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("data-id");
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value == 1) {
				$.ajax({
					type: 'delete',
					url: '{!! route("admin.farmers.destroy",' + id + ') !!}',
					data: {
						_token: '{{csrf_token()}}',
						id: $(this).attr("data-id")
					},
					success: function(data) {
						$('#farmer' + id).remove();
						Swal.fire('Deleted!', 'Your record been deleted.', 'success')
					},
					error: function(jqXHR, textStatus, errorThrown) {
						var data = $.parseJSON(jqXHR.responseText);
						Swal.fire('Error!', 'Failed', 'error')
					}
				});
			}
		})
	});


	$('#states').select2({
	  selectOnClose: true
	});
	$('#districts').select2({
	  selectOnClose: true
	});
	$('#talukas').select2({
	  selectOnClose: true
	});
	$('#panchayats').select2({
	  selectOnClose: true
	});
	$('#villages').select2({
	  selectOnClose: true
	});
	$('#farmer_status').select2({
		selectOnClose: true
	});
	$('#executive_onboarding').select2({
		selectOnClose: true
	});
	$('#module').select2({
		selectOnClose: true
	});
    $('#l2_validator').select2({
        selectOnClose: true
    }); 

</script>
@stop
