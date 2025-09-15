<h5 class="card-title bg-primary text-white p-3 text-center">Crop Data</h5>
                            <div class="row mb-3">
                                <div class="col">
                                    <ul class="nav nav-pills" style="display: flex; align-items: center;">
                                        @foreach($plot->PlotCropData as $Croplotbtn)
                                        <li class="nav-item"><a href="#plot-{{$Croplotbtn->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">Plot {{$Croplotbtn->plot_no}}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col">
                                </div>
                                <div class="col-4">
                                    <a href=""><span class="btn btn-warning" style="font-size: 11px; padding: 13px 0px;">Area Of all Plots(Hectare)</span></a>
                                </div>

                                <div class="col-2" style="margin-top: 1px;margin-left: -34px;">
                                    {{-- <a href=""><span class="btn btn-warning" style="font-size: 17px;padding: 8px 11px;">{{ number_format($plot->ApprvFarmerPlot->area_acre_awd??'0.0', 2, '.', '')??'0.0'}}</span></a> --}}
                                    {{-- {{dd($plot_areas_sum)}} --}}
                                    <a href=""><span class="btn btn-warning" style="font-size: 14px;padding: 8px 11px;">{{ number_format($plot_areas_sum??'0.0', 2, '.', '')??'0.0'}}</span></a>

                                </div>

                            </div>
                            <div class="tab-content">
                                @foreach($cropdata as $Croplot)
                                <div id="plot-{{$Croplot->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Plot Id</th>
                                                    {{-- {{dd($Croplot->farmer_plot_uniqueid)}} --}}
                                                    <th colspan="2">{{$Croplot->farmer_plot_uniqueid}}</th>
                                                </tr>
                                                {{-- <tr>
                                                    <th>Total Plot Area In Bigha</th>
                                                    <th colspan="2">{{$Croplot->farmerplot_details->area_in_other??'0.0'}}</th>
                                                </tr> --}}
                                                <tr>
                                                    <th>Total Plot Area In Hectare</th>
                                                    <th colspan="2">{{$Croplot->farmerplot_details->area_in_acers??'0.0'}}</th>

                                                </tr>
                                                <tr>
                                                    <th>Dates of Transplanting</th>
                                                    <th colspan="2">{{ \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->dt_transplanting)->format('d-m-Y')}}</th>

                                                </tr>
                                                <tr>
                                                    <th>Dates of Nursery</th>
                                                    <th colspan="2">
                                                        <input type="date" class="datepicker d-none nursery"  id="nursery">
                                                        
                                                        <span id="nursery_span"> {{ $Croplot->PlotCropDetails ? \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->PlotCropDetails->nursery)->format('d-m-Y')  : '' }} </span>
                                                        
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>Date of Land Preparation</th>
                                                    <th colspan="2">{{ \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->dt_ploughing)->format('d-m-Y')}}</th>

                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Season & Variety</td>
                                                    <td>last Year</td>
                                                    <td>Current Year</td>

                                                </tr>

                                                <tr>
                                                    <td>Crop Season</td>
                                                    <td>{{ $Croplot->PlotCropDetails ? $Croplot->PlotCropDetails->crop_season_lastyrs  : '' }} </td>
                                                    <td>{{$Croplot->PlotCropDetails->crop_season_currentyrs  }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Crop Variety</td>
                                                    <td>{{$Croplot->PlotCropDetails->crop_variety_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->crop_variety_currentyrs}}</td>
                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Fertilizer Management</td>
                                                    <td>Last Year</td>
                                                    <td>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_1_name}}(Kg/Ha)</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_1_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_1_currentyrs}}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_2_name}}(Kg/Ha)</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_2_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_2_currentyrs}}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_3_name}}(Kg/Ha)</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_3_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->fertilizer_3_currentyrs}}</td>
                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Water Management</td>
                                                    <td>Last Year</td>
                                                    <td>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>Irrigation</td>
                                                    <td>{{$Croplot->PlotCropDetails->water_mng_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->water_mng_currentyrs}}</td>
                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Yield Information</td>
                                                    <td>Last Year</td>
                                                    <td>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>Yield(Kg/Ha)</td>
                                                    <td>{{$Croplot->PlotCropDetails->yeild_lastyrs}}</td>
                                                    <td>{{$Croplot->PlotCropDetails->yeild_currentyrs}}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
