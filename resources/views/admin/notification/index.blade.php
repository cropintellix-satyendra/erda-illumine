{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')

			<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Notification</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Notification</a></li>
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
	              							      <h4 class="card-title" style="margin-top: 15px">Notification</h4>
	              							    </div>
	              							    <div class="col">
	              										<a href="{{route('admin.notification.create')}}" class="btn light btn-info float-right">Add</a>
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
												                        <th>Title</th>
                                                <th>Body</th>
												                        <th>Type</th>
												                        <th>UserCount</th>
												                        <th>Date Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notifys as $notify)
                                            <tr id="company{{$notify->id}}">
                                                <td>{{$loop->index + 1}}</td>
                                                <td>{{$notify->title}}</td>
                                                <td>{{$notify->body}}</td>
                                                <td class="text-uppercase">{{$notify->type}}</td>
                                                <td>{{$notify->user_count}}</td>
                                                <td>{{$notify->created_at->toDayDateTimeString()}}</td>
                                                 {{--<td>
                                                    <div class="d-flex">
                                                        <a href="{{Route('admin.notification.edit',$notify->id)}}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
                                                        <button class="btn btn-danger shadow btn-xs sharp delete-Company" data-id="{{$notify->id}}"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </td>--}}
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
