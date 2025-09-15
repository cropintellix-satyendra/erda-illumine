{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')

            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>L-2 Validator</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">L-2 Validator User</a></li>
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
                                        <h4 class="card-title" style="margin-top: 15px">L-2 Validator</h4>
                                    </div>
                                    <div class="col">
                                        <a href="{{route('admin.verifier.create')}}" class="btn light btn-info float-right">Add</a>
                                        {{-- <a href="{{url('admin/assign/role')}}" class="btn light btn-info float-right">assighn</a> --}}
                                    </div>
                                  </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>Sr. NO</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($verifiers as $verifier)
                                            <tr id="vendor{{$verifier->id}}">
                                                <td>{{$loop->index+1}}</td>
                                                <td>{{$verifier->name}}</td>
                                                <td>{{$verifier->email}}</td>
                                                <td>{{$verifier->mobile}}</td>
                                                <td><span class="badge light badge-{{ $verifier->status == 1 ? 'success' : 'danger' }}">{{ $verifier->status == 1 ? 'Enable' : 'Disable' }}</span></td>
                                                <td>{{ Carbon\Carbon::parse($verifier->last_login)->toDayDateTimeString() }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{Route('admin.verifier.edit',$verifier->id)}}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
                                                        {{-- <button class="btn btn-danger shadow btn-xs sharp delete-Vendor" data-id="{{$verifier->id}}"><i class="fa fa-trash"></i></button> --}}
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
$('.delete-Vendor').click(function(e){
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
            url:'{!! route("admin.validator.destroy",'+id+') !!}',
            data:{_token:'{{csrf_token()}}',_method:'delete',id:$(this).attr("data-id")},
            success:function(data){
                            $('#vendor'+id).remove();
                                Swal.fire('Deleted!','Your record been deleted.','success')
            },
            error: function (jqXHR, textStatus, errorThrown) {
              var data=$.parseJSON(jqXHR.responseText);
              Swal.fire('Cannot Delete!',data.message,'error')
            }
        });
          }
        })
});
</script>
@stop
