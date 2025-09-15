{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')

			<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Organization</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Organization</a></li>
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
	              							      <h4 class="card-title" style="margin-top: 15px">Organization</h4>
	              							    </div>
	              							    <div class="col">
	              										<a href="{{route('admin.company.create')}}" class="btn light btn-info float-right">Add</a>
	              							    </div>
	              							  </div>
	              							</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
												<th>Name</th>
												<th>State</th>
                                                <th>Organization Code</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $company)
                                            <tr id="company{{$company->id}}">
                                                <td>{{$company->company}}</td>
                                                <td>{{$company->state->name??'NA'}}</td>
                                                <td id="copycode" >{{$company->company_code}} <button onclick="copyTextToClipboard('{{$company->company_code}}')"><i class="fa fa-copy"></i></button></td>
                                                <td><span class="badge light badge-{{ $company->status == 1 ? 'success' : 'danger' }}">{{ $company->status == 1 ? 'Enable' : 'Disable' }}</span></td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{Route('admin.company.edit',$company->user_id)}}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
                                                        <button class="btn btn-danger shadow btn-xs sharp delete-Company" data-id="{{$company->user_id}}"><i class="fa fa-trash"></i></button>
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
function copyTextToClipboard(code) {
  var copyText = document.getElementById("copycode");

  if (copyText) {
    var textToCopy = code;

    navigator.clipboard.writeText(textToCopy)
      .then(function() {
        toastr.success("","Coppied", {
            timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
            progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
            showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
        })
      })
      .catch(function(error) {
        toastr.error("", "Unable to Copy", {
            positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
            debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
            hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
            tapToDismiss: !1
        })
      });
  } else {
    console.log('Element with ID "copycode" not found');
  }
}


$('.delete-Company').click(function(e){
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
            url:'{{ url("admin/company/destroy")}}/'+id,
            data:{_token:'{{csrf_token()}}',_method:'delete',id:$(this).attr("data-id")},
            success:function(data){
							$('#company'+id).remove();
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
