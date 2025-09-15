<h5 class="card-title bg-primary text-white p-3 text-center">Plot Info</h5>
                                <div class="row mb-3">
                                    <div class="" style="margin-left: 18px">
                                        <ul class="nav nav-pills" style="display: flex; align-items: center;">
                                            @foreach($farmerplots as $Croplotbtn)
                                            <li class="nav-item">
                                                <a href="#plot-{{$Croplotbtn->plot_no}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">Plot {{$Croplotbtn->plot_no}}</a>
                                            </li>
                                            &nbsp;
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="col">
                                    </div>
                                    <div class="col-4">
                                        <a href=""><span class="btn btn-warning" style="font-size: 11px; padding: 13px 0px;">Area Of all Plots(Hectare)</span></a>
                                    </div>
                                    {{-- {{dd($plot)}} --}}
                                    {{-- @php
                                        $farmer_plot_area = \App\Models\FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
                                        // dd($farmer_plot_area);
                                     @endphp --}}
                                    <div class="col-2" style="margin-right: 16px">
                                        <a href=""><span class="btn btn-warning" style="font-size: 14px;padding: 8px 11px;">{{ number_format($plot_areas_sum??'0.0', 2, '.', '')??'0.0'}}</span></a>
                                    </div>
                                </div>
                                <div class="tab-content">
                                    @foreach($farmerplots as $Croplot)
                                    <div id="plot-{{$Croplot->plot_no}}" class="tab-pane {{$loop->first?'active':''}} pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td>Plot Id</td>
                                                        <td>{{$Croplot->farmer_plot_uniqueid}}</td>
                                                        {{-- {{dd($Croplot->farmer_plot_uniqueid)}} --}}
                                                    </tr>
                                                    {{-- <tr>
                                                        <td>Total Area In Bigha</td>
                                                        <td>{{$plot->ApprvFarmerPlot->area_in_other??'-'}}</td>
                                                    </tr> --}}
                                                    <tr>
                                                        <td>Total Area In Hectare</td>
                                                        <td>{{$Croplot->area_in_acers??'-'}}</td>
                                                    </tr>
                                                    {{-- <tr>
                                                        <td>Own Area in Bigha</td>
                                                        <td>{{$Croplot->own_area_in_acres*3.025
                                                            ??'-'}}</td>
                                                    </tr> --}}
                                                    <tr>
                                                        <td>Own Area in Hectare</td>
                                                        <td>{{$Croplot->own_area_in_acres??'-'}}</td>
                                                    </tr>
                                                    {{-- <tr>
                                                        <td>Lease Area in Bigha</td>
                                                        <td>{{$Croplot->lease_area_in_acres*3.025??'-'}}</td>
                                                    </tr> --}}
                                                    <tr>
                                                        <td>Lease Area in Hectare</td>
                                                        <td>{{$Croplot->lease_area_in_acres??'-'}}</td>
                                                    </tr>
                                                    {{-- <tr>
                                                        <td>Area Chosen For AWD(Bigha)</td>
                                                        <td>{{$plot->ApprvFarmerPlot->area_other_awd??'-'}}</td>
                                                    </tr> --}}
                                                    <tr>
                                                        <td>Area Chosen For AWD(Hectare)</td>
                                                        <td>{{$plot->ApprvFarmerPlot->area_acre_awd??'-'}}</td>
                                                    </tr>
                                                    {{-- {{dd($plot->plot_area??"0")}} --}}
                                                    {{-- <tr>
                                                        <td>Plot Area (AWD Area in Bigha )</td>
                                                        <td>{{$plot->plot_area*3.025??'-'}}</td> 
                                                    </tr> --}}
                                                    <tr>
                                                        <td>Plot Area (AWD Area in Hectare)</td>
                                                        <td>{{$plot->plot_area??'-'}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Owner Name</td>
                                                        <td>{{$plot->farmer_name??'-'}}</td>
                                                    </tr>
                                                    @if ($plot->state_id == 29)
                                                    <tr>
                                                        <td>Patta Number</td>
                                                        <td>{{$plot->ApprvFarmerPlot->patta_number??'NA'}}</td>
                                                    </tr>
                                                    @endif
                                                    <tr>
                                                        <td>Daag Number</td>
                                                        <td>{{$plot->ApprvFarmerPlot->daag_number??'NA'}}</td>
                                                    </tr>
                                                    @if ($plot->state_id == 37)
                                                    <tr>
                                                        <td>Khatian Number</td>
                                                        <td>{{$plot->khatian_number??'NA'}}</td>
                                                    </tr>
                                                    @endif

                                                    <tr style="line-height: 0;">
                                                        <td style="padding: 2px 5px; font-size: 12px;">Photos</td>
                                                        <td>
                                                            <div class="plot-gallery d-flex">

                                                                @if($plot->ApprvFarmerPlotImages)

                                                                    @forelse($plot->ApprvFarmerPlotImages()->get() as $items)
                                                                    <a class="btn btn-sm p-0 popup-gallery" href="{{$items->path}}">
                                                                        <img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32" style="max-width: 55%;">
                                                                    </a>
                                                                    @empty
                                                                    NA
                                                                    @endforelse
                                                                @endif
                                                                
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 5px; vertical-align: middle;">Signed Agreement</td>

                                                        <td>
                                                            {{-- @if($plot->affidavit_tnc) --}}
                                                            <a class="btn btn-sm" href="{{url('admin/farmers/download/'.$plot->farmer_plot_uniqueid.'/'.'LEASED'.'/'.$plot->plot_no)}}">
                                                                <img src="{{asset('icons/icon_pdf.png')}}" class="w-32" style="max-width: 55%;">
                                                            </a>
                                                            {{-- @else
                                                            OWN
                                                            @endif --}}

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 5px; vertical-align: middle;">Excel Download</td>

                                                        <td>
                                                            <a class="btn text-success btn-sm" href="{{url('l2/download/file/'.'Individuals/'.$plot->farmer_uniqueId.'/'.$plot->plot_no.'/'.'Approved')}}">
                                                                <i class="fa fa-download" aria-hidden="true"></i> Download Excel
                                                            </a>
                                                        </td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
