{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
			<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Years</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Targets</a></li>
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
							      <h4 class="card-title" style="margin-top: 15px">Targets</h4>
							    </div>
							    <div class="col">
										<a href="{{route('admin.daily_target.create')}}" class="btn light btn-info float-right">Add</a>
							    </div>
							  </div>
							</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>Module Name</th>
                                                <th>Daily Target</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($target as $targets)
                                            <tr id="{{$targets->id}}">

                                                <td>{{$targets->module_name }}</td>
                                                <td>{{$targets->daily_target }}</td>


                                                <td>
                        	                <div class="d-flex">
            <a href="{{route('admin.daily_target.edit',$targets->id)}}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
            <button class="btn btn-danger shadow btn-xs sharp delete-year" data-id="{{$targets->id}}"><i class="fa fa-trash"></i></button>
                        													</div>
                        												</td>
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
<script>
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
            url:'{!! route("admin.daily_target.destroy",'+id+') !!}',
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
</script>
@stop
