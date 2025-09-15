<h5 class="card-title bg-primary text-white p-3 text-center">Pipe Installations</h5>
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a href="#plot-{{$PipeInstallation->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">{{$PipeInstallation->farmer_plot_uniqueid}}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot unique Id</td><td>{{$PipeInstallation->farmer_plot_uniqueid}}</td></tr>
                                                <tr><td>Total Plot Area in Acre(Onboarding) </td><td>{{$PipeInstallation->farmerapproved->area_in_acers}}</td></tr>
                                                <tr><td>Plot Area(Google map)</td><td>
                                                    {{$PipeInstallation->plot_area}}</td></tr>
                                                    @php
                                                    use App\Models\PipeInstallationPipeImg;
                                                
                                                    $pipeInstallationId = $PipeInstallation->farmer_plot_uniqueid;
                                                
                                                    // Retrieve the count of pipe installation images related to this specific installation
                                                    $pipeCount = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $pipeInstallationId)->count();
                                                @endphp
                                                <tr><td>No. of pipes Installed</td><td>{{$pipeCount}}</td></tr>
                                                @if($PipesLocation)
                                                    @foreach($PipesLocation as $pipe)
                                                        {{-- {{dd($PipesLocation)}} --}}
                                                      <tr><td>Pipe {{$pipe->pipe_no}} Distance</td>
                                                        <td>
                                                          <div class="plot-gallery d-flex float-left">
                                                              <a class="btn btn-sm p-0 popup-gallery" href="{{$pipe->images}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                          </div>
                                                          <div class="d-inline float-right mt-2">{{$pipe->distance}}M</div>
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
                                                <tr><td>Name of Surveyor</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$PipeInstallation->farmerapproved->surveyor_id??'1')}}">{{$PipeInstallation->surveyor->name??"-"}}</a></td></tr>
                                                @else
                                                <tr><td>Name of Surveyor</td><td>{{$PipeInstallation->farmerapproved->surveyor_name??'-'}}</td></tr>
                                                @endif
                                                <tr><td>DateTime</td><td>{{$PipeInstallation->created_at}}</td></tr>   {{-- //previosly has polygon_date_time --}}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
