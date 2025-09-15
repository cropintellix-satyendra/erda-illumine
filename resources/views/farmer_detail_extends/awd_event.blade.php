<h5 class="card-title bg-primary text-white p-3 text-center">AWD Events</h5>
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a href="#plot-{{$PipeInstallation->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">{{$PipeInstallation->farmer_plot_uniqueid}}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tr>
                                                <td>Plot Unique Id</td>
                                                <td>{{$PipeInstallation->farmer_plot_uniqueid}}</td>
                                                <td>Plot Area(Acres)</td>
                                                <td>{{$PipeInstallation->plot_area}}</td>
                                            </tr>
                                            <tr>
                                                <td>Event No.</td>
                                                <td>Pipe No.</td>
                                                <td>Date & Time</td>
                                                <td colspan="2">Photos</td>
                                            </tr>
                                            @foreach($awd as $items)
                                            <tr>
                                                <td>{{$items->aeration_no}}</td>
                                                <td>{{$items->pipe_no}}</td>
                                                <td>{{ $items->date_survey }}, {{\Carbon\Carbon::parse($items->time_survey)->format('h:i A')}}</td>
                                                <td colspan="2">
                                                    <div class="plot-gallery d-flex">
                                                        @forelse($items->AerationImages()->where('plot_no',$items->plot_no)->where('aeration_no',$items->aeration_no)->where('pipe_no',$items->pipe_no)->where('status','Approved')->get() as $imgpath)
                                                            <a class="btn btn-sm p-0 popup-gallery" href="{{$imgpath->path}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                        @empty
                                                        @endforelse
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
