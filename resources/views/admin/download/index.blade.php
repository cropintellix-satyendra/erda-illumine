@extends('layout.default')
@section('content')
{{-- Default box --}}
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Download</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Download</a></li>
            </ol>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
        <div class="card">
            <div class="container">
            <div class="card-header px-0 d-none">
                <button id="create-new-backup-button" href="{{ url(config('backpack.base.route_prefix', 'admin').'/backup/create') }}" class="btn btn-primary mb-2">
                    <i class="la la-spinner"></i>
                    <i class="la la-plus"></i>
                    <span>{{ trans('backpack::backup.create_a_new_backup') }}</span>
                </button>
            </div>
            <div class="card-body p-0">
                <table id="table" class="table table-hover pb-0 mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('file') }}</th>
                            <th>{{ trans('backpack::backup.date') }}</th>
                            <th>{{ trans('status') }}</th>
                            <th class="text-right">{{ trans('backpack::backup.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>


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
<script src="{!! asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') !!}"></script> 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function(){
    var searchable = [];
    var selectable = [];
    var table = $('#table').DataTable({
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        processing: true,
        responsive: false,
        serverSide: true,
        processing: true,
        language: {
        processing: '<i class="ace-icon fa fa-spinner fa-spin orange bigger-500" style="font-size:60px;margin-top:50px;"></i>'
        },
        scroller: {
            loadingIndicator: false
        },
        pagingType: "full_numbers",
        //dom: "<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>tipr",
        ajax: {
            url: '',
            type: "get",
            data:{type:'assessment'}
        },
        columns: [
            {data:'id', name: 'id', orderable: true, searchable: false,width:'10%'},
            {data:'filename', name: 'user_info.name',defaultContent:''},
            {data:'created_at', name: 'created_at',defaultContent:0},
            {data:'status', name: 'status',defaultContent:0},
            {data:'action', name: 'action',orderable: false, searchable: false}
        ],
        columnDefs:[
            {render: function (data, type, row, meta) {
                    return meta.row+1;
                },
                "targets":0,
            },
            {render: function (data, type, row, meta) {
                    return moment.unix(data).format('DD/MM/YYYY hh:mm A');
                },
                "targets":-3,
            },
        ],
        initComplete: function () {
            var api =  this.api();
            api.columns(searchable).every(function () {
                var column = this;
                var input = document.createElement("input");
                input.setAttribute('placeholder', $(column.header()).text());
                input.setAttribute('style', 'width: 140px; height:25px; border:1px solid whitesmoke;');

                $(input).appendTo($(column.header()).empty())
                .on('keyup', function () {
                    column.search($(this).val(), false, false, true).draw();
                });

                $('input', this.column(column).header()).on('click', function(e) {
                    e.stopPropagation();
                });
            });

            api.columns(selectable).every( function (i, x) {
                var column = this;

                var select = $('<select style="width: 140px; height:25px; border:1px solid whitesmoke; font-size: 12px; font-weight:bold;"><option value="">'+$(column.header()).text()+'</option></select>')
                    .appendTo($(column.header()).empty())
                    .on('change', function(e){
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
                        column.search(val ? '^'+val+'$' : '', true, false ).draw();
                        e.stopPropagation();
                    });

                $.each(dropdownList[i], function(j, v) {
                    select.append('<option value="'+v+'">'+v+'</option>')
                });
            });

        },
    });
    setInterval(function () {
      table.ajax.reload();
  }, 60000);

// href="'.url('admin/download/'.$job->id).'"
$('#table').on('click','a.btn-delete',function(e){
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
            url:'{!! url("delete/excel/download") !!}',
            data:{_token:'{{csrf_token()}}',_method:'delete',id:id},
            success:function(data){
              // $('#'+id).remove();
                location.reload();
                Swal.fire('Deleted!',data.message,'success')
            },
            error: function (jqXHR, textStatus, errorThrown) {
              var data=$.parseJSON(jqXHR.responseText);
              Swal.fire('Error!','Failed','error')
            }
        });
      }
    })
});

    // $('#table').on('click','a.btn-delete',function(e){
    //     e.preventDefault();
    //     var result = confirm("Are you sure you want to Remove?");
    //     if (!result) {
    //         return false;
    //     }
    //     var $row = $(this).closest('tr');
    //     var data = table.row($row).data();
    //     $.ajax({
    //         type: "POST",
    //         url: $(this).attr('href'),
    //         data: {_method:'DELETE',_token:'{{ csrf_token() }}',action:'delete'},
    //         dataType: 'json',
    //         success: function(data)
    //         {
    //             if(data.success){
    //                 table
    //                 .row( $row )
    //                 .remove()
    //                 .draw();
    //             }
    //         }
    //     });
    //
    // });

});
</script>
@endsection
