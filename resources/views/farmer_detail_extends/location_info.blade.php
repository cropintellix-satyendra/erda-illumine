<table class="table table-bordered table-sm">
    <thead class="thead-primary">
        <tr>
            <th colspan="2" class="text-center">Location Info</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>State</td><td>{{$plot->state->name}}</td>
        </tr>
        <tr>
            <td>District</td><td>{{$plot->district->district??"-"}}</td>
        </tr>
        <tr>
            <td>Taluka</td><td>{{$plot->taluka->taluka??"-"}}</td>
        </tr>
        <tr>
            <td>Panchayat</td><td>{{$plot->panchayat->panchayat??"-"}}</td>
        </tr>
        <tr>
            <td>Village</td><td>{{$plot->village->village??"-"}}</td>
        </tr>
        <tr>
            <td>Latitude</td><td>{{$plot->latitude}}</td>
        </tr>
        <tr>
            <td>Logitude</td><td>{{$plot->longitude}}</td>
        </tr>
        <tr>
            <td class="align-top">Remarks</td><td>{{$plot->remarks}}</td>
        </tr>
    </tbody>
</table>
