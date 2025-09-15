<!--timeline of status of plots-->
<div class="row">
    <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Status Timeline</h4>
            <div class="dropdown ml-auto">

            </div>
          </div>
            <div class="card-body">
                <!-- end filter -->
                <div class="table-responsive">
                    <table id="example3" class="table table-bordered dt-responsive nowrap display data-table" style="min-width: 845px">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Unique ID</th>
                                <th>Plot No.</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Comment</th>
                                <th>Date/Time</th>
                                <th>Employee</th>
                                <th>Reasons</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status_timeline as $data)
                            @php
                                $status_color = 'text-info';
                                if($data->status == 'Approved'){
                                    $status_color='text-success';
                                }
                                if($data->status == 'Rejected'){
                                    $status_color='text-danger';
                                }
                            @endphp
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{$data->farmer_uniqueId}}</td>
                                <td>{{$data->plot_no}}</td>
                                <td>{{$data->level}}</td>
                                <td><a class="btn {{$status_color}}" title="Show">{{$data->status}}</a></td>
                                <td>{{$data->comment??"-"}}</td>
                                <td>{{$data->timestamp}}</td>
                                <td>
                                  @if($data->level == 'AppUser')
                                      {{$data->Surveyor->name}}
                                  @else
                                    {{$data->UserApprovedRejected->name??"-"}}
                                  @endif

                                </td>
                                <td>{{$data->Reasons->reasons??"NA"}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end of timeline of status of plots-->
