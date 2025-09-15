{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
			<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Stakeholder Form Details</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Stakeholder Form</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
					<div class="col-12">
                        <div class="card">
                            <div class="card-header">
							<div class="container" style="margin-top: 2px;">
							  <div class="row">
							    <div class="col">
							      <h4 class="card-title" style="margin-top: 15px">Stakeholder Form Details</h4>
							    </div>
							  </div>
							</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Form Number</th>
                                                <th>Name Of Stake Holder</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>District</th>
                                                <th>Taluka</th>
                                                <th>Panchayat</th>
                                                <th>Village</th>
                                                <th>Total land</th>
                                                <th>Surveyor Name</th>
                                                <th>Date Of Survey</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($farmer_details as $farmer)
                                                <tr id="{{ $farmer->id }}">
                                                    <td>{{ $farmer->index + 1 }}</td>
                                                    <td>
                                                        <a class="btn btn-outline-primary" href="{{ url('admin/stake-holder/show')}}/{{$farmer->id}}/{{ $farmer->form_number}}" title="Show"> {{ $farmer->form_number }}</a>
                                                    </td>
                                                    <td>{{ $farmer->farmer_name }}</td>
                                                    <td>{{ $farmer->mob_no }}</td>
                                                    <td>{{ $farmer->state }}</td>
                                                    <td>{{ $farmer->district }}</td>
                                                    <td>{{ $farmer->taluka }}</td>
                                                    <td>{{ $farmer->panchayat }}</td>
                                                    <td>{{ $farmer->village }}</td>
                                                    <td>{{ $farmer->total_land }}</td>
                                                    <td>{{ $farmer->surveyor->name }}</td>
                                                    <td>{{ $farmer->date_of_survey }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection
@section('scripts')
{{-- <script>
$('.delete-year').click(function(e){
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
            type:'post',
            url:'{!! route("admin.year.destroy",'+id+') !!}',
            data:{_token:'{{csrf_token()}}',_method:'delete',id:$(this).attr("data-id")},
            success:function(data){
							$('#'+id).remove();
								Swal.fire('Deleted!','Your record been deleted.','success')
            },
            error: function (jqXHR, textStatus, errorThrown) {
              var data=$.parseJSON(jqXHR.responseText);
							Swal.fire('Error!','Failed','error')
            }
        });
		  }
		})
});
</script> --}}
@stop
