{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
			<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Trash</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Trash</a></li>
                        </ol>
                    </div>
                </div>
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Trash : {{$farmers->count()}}</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                       <button class="btn btn-danger shadow btn-xs sharp delete-TrashRecordAll" style="width: 100px;">Delete All <i id="Rspinner" class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
                    </div>
                </div>
              <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                       <button class="btn btn-danger shadow btn-xs sharp Updt-uniqueplot" style="width: 100px;">Update id <i id="Updspinner" class="fa fa-spinner fa-spin Updspinner d-none"></i></button>
                    </div>  
                
                
                <!-- row -->
                <div class="row">
					<div class="col-12">
                        <div class="card">
                            <div class="card-header">
							<div class="container" style="margin-top: 2px;">
							  <div class="row">
							    <div class="col">
							      <h4 class="card-title" style="margin-top: 15px">Trash</h4>
							    </div>
							    <div class="col">
									
							    </div>
							  </div>
							</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>Unique</th>
                                                <th>farmer name</th>
                                                <th>Mobile</th>
                                                <th>Surveyor name</th>
                                                <th>surveyor email</th>
                                                <th>date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($farmers as $list)
                                            <tr id="{{$list->id}}">
                                                <td>{{$list->farmer_uniqueId}}</td>
                                                <td>{{$list->farmer_name}}</td>
                                                <td>{{$list->mobile}}</td>
                                                <td>{{$list->surveyor_name}}</td>
                                                <td>{{$list->surveyor_email}}</td>
                                                <td>{{$list->created_at}}</td>
                                                <td>
                        						<div class="d-flex">
                        		 <a href="{{ url('admin/farmers/trashshow').'/'.$list->id.'/'.$list->farmer_uniqueId}}"
                        		 class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-eye"></i></a>
                        		<button class="btn btn-danger shadow btn-xs sharp delete-TrashRecord" data-unique="{{$list->farmer_uniqueId}}" data-id="{{$list->id}}"><i class="fa fa-trash"></i></button>
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



$(document).on('click','.Updt-uniqueplot',function(e){
	  e.preventDefault();
	  $('#Updspinner').removeClass('d-none');
	  
	  
// 	  return false;
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, update it!'
		}).then((result) => {
		  if (result.value == 1) {
                $.ajax({
                    type:'get',
                    url:'{!! url("admin/farmers/updateunique/plot") !!}',
                    data:{_token:'{{csrf_token()}}'},
                    success:function(data){
                        // $('#Updspinner').addClass('d-none');
                        location.reload();
        								Swal.fire('updated!','Your record been updated.','success')
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#Updspinner').addClass('d-none');
                      var data=$.parseJSON(jqXHR.responseText);
        							Swal.fire('Error!','Failed','error')
                    }
                });
		  }
		  $('#Updspinner').addClass('d-none');
		})
});

$(document).on('click','.delete-TrashRecordAll',function(e){
	  e.preventDefault();
	  $('#Rspinner').removeClass('d-none');
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
                    type:'delete',
                    url:'{!! url("admin/farmers/trashshowall/delete") !!}',
                    data:{_token:'{{csrf_token()}}'},
                    success:function(data){
                        $('#Rspinner').addClass('d-none');
        								Swal.fire('Deleted!','Your record been deleted.','success')
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#Rspinner').addClass('d-none');
                      var data=$.parseJSON(jqXHR.responseText);
        							Swal.fire('Error!','Failed','error')
                    }
                });
		  }
		  $('#Rspinner').addClass('d-none');
		})
});
$(document).on('click','.delete-TrashRecord',function(e){
	  e.preventDefault();
	  var id = $(this).attr("data-id");
	  var unique = $(this).attr("data-unique");
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
            type:'delete',
            url:'{!! url("admin/farmers/trashshow/delete") !!}'+'/'+id,
            data:{_token:'{{csrf_token()}}',id:$(this).attr("data-id"),unique:unique},
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
