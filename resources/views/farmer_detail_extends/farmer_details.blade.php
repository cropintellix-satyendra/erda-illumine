

@php
    // dd($plot->farmer_uniqueId);

    $data = \App\Models\FarmerFarmDetails::where('farmer_uniqueId', $plot->farmer_uniqueId)->latest()->first();
    

@endphp
<table class="table table-bordered table-sm">
    <thead class="thead-primary">
        <tr>
            <th colspan="2" class="text-center">Farmer Farm Details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Irigation source</td><td>{{$data->irigation_source  ?? 'NA'}}</td>
            {{-- {{dd($$data->farmer_$data_uniqueid)}} --}}
        </tr> 
        <tr>
            <td>Struble burning</td><td>{{$data->struble_burning   ?? 'NA'}}</td>
        </tr>
        <tr>
            <td>Double paddy status</td><td>{{$data->double_paddy_status   ?? 'NA'}}</td>
        </tr>
        <tr>
            <td>Soil Type</td><td>{{$data->soil_type??'NA'}}</td>
        </tr>
        <tr>
            <td>Variety</td><td>{{$data->variety??'NA'}}</td>
        </tr>
        <tr>
        <tr>
            <td>Flooding type</td><td>{{$data->flooding_type  ?? 'NA'}}</td>
        </tr>
        <tr>
            <td>Proper drainage</td><td>{{$data->proper_drainage  ?? 'NA'}}</td>
        </tr>
        <tr>
            <td>AWD previous</td><td>{{$data->awd_previous??'NA'}}</td>
        </tr>
        <tr>
            <td>AWD previous no </td><td>{{$data->awd_previous_no??'NA'}}</td>
        </tr>
        <tr>
            <td>Community benefit</td><td>{{$data->community_benefit  ?? 'NA'}}</td>
        </tr>
    </tbody>
</table>

