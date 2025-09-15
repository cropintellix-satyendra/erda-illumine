<h5 class="card-title bg-primary text-white p-3 text-center">Polygon</h5>
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a href="#plot-{{$PipeInstallation->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">{{$PipeInstallation->farmer_plot_uniqueid}}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot unique Id</td><td>{{$PipeInstallation->farmer_plot_uniqueid}}</td></tr>
                                                <tr><td>Plot Area(Onboarding)</td><td>{{$PipeInstallation->area_in_acers}}</td></tr>
                                                <tr><td>Plot Area(Google map)</td><td>{{$PipeInstallation->plot_area}}</td></tr>
                                                <tr><td>Percentage Error(%)</td><td>{{ round($percent_error, 2) }} %</td></tr>
                                                <tr><td>No. of pipes Installed</td><td>{{$PipeInstallation->installed_pipe}}</td></tr>
                                                @if($PipesLocation)
                                                    @foreach($PipesLocation as $pipe)
                                                      <tr><td>Pipe {{$pipe->pipe_no}} Distance</td>
                                                        <td>
                                                          <div class="plot-gallery d-flex float-left">
                                                              <a class="btn btn-sm p-0 popup-gallery" href="{{$pipe->images}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                          </div>
                                                          <div class="d-inline float-right mt-2">{{$pipe->distance }}M</div>
                                                        </td>
                                                      </tr>
                                                      <tr>
                                                          <td>Date & Time of Installations</td>
                                                          <td>{{$pipe->date }}, {{$pipe->time }}</td>
                                                      </tr>
                                                    @endforeach
                                                @endif
                                                {{--<tr><td>Pipe 2 Distance</td><td>{{$PipeInstallation->no_pipe_req}} M</td></tr>
                                                <tr><td>Date & Time of Installations</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $PipeInstallation->date_time)->format('d-m-Y')}}</td></tr> --}}
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Name of Surveyor</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$PipeInstallation->surveyor_id)}}">{{$PipeInstallation->surveyor_name}}</a></td></tr>
                                                @else
                                                <tr><td>Name of Surveyor</td><td>{{$PipeInstallation->surveyor_name}}</td></tr>
                                                @endif
                                                <tr><td>DateTime</td><td>{{$PipeInstallation->polygon_date_time}}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
