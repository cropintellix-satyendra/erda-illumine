<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="thead-primary">
            <tr>
                <th colspan="2" class="text-center">Executive Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Name</td>
                <td>
                @if(Auth::user()->hasRole('SuperAdmin'))
                    <a  target="_blank"  href="">{{$plot->users->name??'NA'}}</a>
                @else
                    {{$plot->users->name??'NA'}}
                @endif
                </td>
            </tr>
            <tr>
                <td>Mobile No</td><td>{{$plot->users->mobile??'NA'}}</td>
            </tr>
            <tr>
                <td>Email ID</td><td>{{$plot->users->email??"NA"}}</td>
            </tr>
            <tr>
                <td>Date of Survey</td><td>{{$plot->date_survey??'NA'}}</td>
            </tr>
            <tr>
                <td>Time of Survey</td><td>{{ $plot->time_survey??'Na' }} </td>
            </tr>
        </tbody>
    </table>
</div>
