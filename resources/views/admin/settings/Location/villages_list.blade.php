{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<style>
    /* Basic Table Styling */
table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
}

th, td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #ccc;
}

th {
  background-color: #f2f2f2;
}

/* Add some hover effect */
tr:hover {
  background-color: #f5f5f5;
}

/* Alternating row colors */
tr:nth-child(even) {
  background-color: #f9f9f9;
}

/* Center align text in the first column (index column) */
td:first-child {
  text-align: center;
}

/* Pagination Styling */
.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination .page-item {
  margin: 0 5px;
}

.pagination .page-item .page-link {
  padding: 5px 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #333;
  text-decoration: none;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
}

.pagination .page-item.disabled .page-link {
  background-color: #f2f2f2;
  color: #ccc;
  pointer-events: none;
}

</style>
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Location</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Location</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <!--  four section of dictrict -->
        <div class="col-12" id="village">
            <div class="card">
                <div class="card-header">
                    <div class="container" style="margin-top: 2px;">
                        <div class="row">
                            <div class="col">
                                <h4 class="card-title" style="margin-top: 15px">Village</h4>
                            </div>
                            <div class="col">
                                <a href="{{url('admin/village/create')}}" class="btn light btn-info float-right">Add</a>
                            </div>
                        </div>
                    </div>
                </div>

              

                <div class="row mt-3">
                    <div class="col-md-4">
                        <form action="{{ url('admin/search/village') }}" method="get" class="form-inline">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                        <select name="limit" class="form-control">
                                            <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <input type="text" name="search" value="{{$request['search']??''}}" class="form-control" placeholder="Search Village">
                                    </div>                                    
                                </div>
                            </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Add your code for the checkbox inputs -->
                        @foreach($states as $state)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="state{{ $state->id }}" name="states[]" value="{{ $state->id }}" {{ (in_array($state->id,$request['states']??['69'] ))?'checked':'' }}>
                            <label class="form-check-label" for="state{{ $state->id }}">{{ $state->name }}</label>
                        </div>
                        @endforeach
                    </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>




                <div class="card-body">
                    <div class="table-responscive">
                        <table id="" class="displcay" style="min-width: 845px">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Village</th>
                                    <th>Panchayat</th>
                                    <th>Taluka</th>
                                    <th>District</th>
                                    <th>State</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($villages as $village)
                                <tr id="village{{$village->id}}">
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$village->village??"NA"}}</td>
                                    <td>{{$village->panchayat??"NA"}}</td>
                                    <td>{{$village->taluka??"NA"}}</td>
                                    <td>{{$village->district??"NA"}}</td>
                                    <td>{{$village->state_name??"NA"}}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{url('admin/village/edit/'.$village->id)}}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
                                            <button class="btn btn-danger shadow btn-xs sharp delete-Village" data-id="{{$village->id}}"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                           
                        </table>
                        
                        <div class="pagination">
                            <ul class="pagination">
                                {{-- Previous Page --}}
                                <li class="page-item {{ $villages->currentPage() == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $villages->appends(request()->query())->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true" style="color: #c3b0b0;">Previous</span>
                                    </a>
                                </li>
                        
                                {{-- First Page --}}
                                <li class="page-item">
                                    <a class="page-link" href="{{ $villages->appends(request()->query())->url(1) }}">1</a>
                                </li>
                        
                                {{-- Pages in the middle --}}
                                @php
                                    $start = max(1, $villages->currentPage() - 2);
                                    $end = min($start + 4, $villages->lastPage());
                                @endphp
                        
                                @for ($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $villages->currentPage() == $i ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $villages->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
                        
                                {{-- Last Page --}}
                                <li class="page-item">
                                    <a class="page-link" href="{{ $villages->appends(request()->query())->url($villages->lastPage()) }}">{{ $villages->lastPage() }}</a>
                                </li>
                        
                                {{-- Next Page --}}
                                <li class="page-item {{ $villages->currentPage() == $villages->lastPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $villages->appends(request()->query())->nextPageUrl() }}" aria-label="Next">
                                        <span aria-hidden="true" style="color: #c3b0b0;">Next</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        
                        <div class="pagination mt-3">
                            <p>
                                Showing {{ $villages->firstItem() }} to {{ $villages->lastItem() }} of {{ $villages->total() }} results
                            </p>
                            {{-- {{ $villages->links() }} --}}
                        </div>
                    </div>
                </div> <!-- card end -->
                
            </div>
        </div> <!--  Four section of dictrict -->

    </div>
</div>
@endsection
@section('scripts')
<script>

    $('.delete-Village').click(function(e) {
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
                    type: 'post',
                    url: '{!! url("admin/village/delete/' + id + '") !!}',
                    data: {
                        _token: '{{csrf_token()}}',
                        _method: 'delete',
                        id: $(this).attr("data-id")
                    },
                    success: function(data) {
                        $('#village' + id).remove();
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
</script>
@stop
