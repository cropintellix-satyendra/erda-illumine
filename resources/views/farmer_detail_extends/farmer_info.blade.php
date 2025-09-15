

<table class="table table-bordered table-sm">
    <thead class="thead-primary">
        <tr>
            <th colspan="2" class="text-center">Farmer Info</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Farmer Unique Id</td><td>{{$plot->farmer_plot_uniqueid}}</td>
            {{-- {{dd($plot->farmer_plot_uniqueid)}} --}}
        </tr>
        <tr>
            <td>Farmer Name</td><td>{{$plot->farmer_name}}</td>
        </tr>
        <tr>
            <td>Mobile Access</td><td>{{$plot->mobile_access}}</td>
        </tr>
        <tr>
            <td>Guardian Name</td><td>{{$plot->guardian_name??'NA'}}</td>
        </tr>
        <tr>
            <td>{{$plot->documents->document_name??'NA'}}</td><td>{{$plot->document_no??'NA'}}</td>
        </tr>
        <tr>
        <tr>
            <td>Relationship owner</td><td>{{$plot->mobile_reln_owner}}</td>
        </tr>
        <tr>
            <td>Mobile</td><td>{{$plot->mobile}}</td>
        </tr>
        <tr>
            <td>Gender</td><td>{{$plot->gender??'NA'}}</td>
        </tr>
        <tr>
            <td>Organization</td><td>{{$plot->organization->company??'NA'}}</td>
        </tr>
        <tr>
            <td>Plot No.</td><td>{{$plot->plot_no}}</td>
        </tr>
        <tr>
            <td>Farmer photo</td>
            <td>
                <div class="plot-gallery d-flex">
                    @if($plot->farmer_photo)
                        <a class="btn btn-sm p-0 popup-gallery" href="{{$plot->farmer_photo}}">
                            <img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32" style="max-width: 55%;">
                        </a>
                    @else
                        NA                    
                    @endif                    
                </div>
            </td>
        </tr>
        <tr>
            <td>Aadhaar photo</td>
            <td>
                <div class="plot-gallery d-flex">
                    @if($plot->aadhaar_photo)
                        <a class="btn btn-sm p-0 popup-gallery" href="{{$plot->aadhaar_photo}}">
                            <img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32" style="max-width: 55%;">
                        </a>
                    @else
                        NA                    
                    @endif                    
                </div>
            </td>
        </tr>
        @if($plot->others_photo)
            <tr>
                <td>Other photo</td>
                <td>
                    <div class="plot-gallery d-flex">
                        @if($plot->others_photo)
                            <a class="btn btn-sm p-0 popup-gallery" href="{{$plot->others_photo}}">
                                <img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32" style="max-width: 55%;">
                            </a>
                        @endif                    
                    </div>
                </td>
            </tr>
        @endif        
    </tbody>
</table>

