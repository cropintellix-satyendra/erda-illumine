<div class="deznav">
            <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

                        <li><a href="{!! url('organization/dashboard') !!}" class="ai-icon" aria-expanded="false">
                                <i class="flaticon-381-layer"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>

                    <!-- dashboaard end -->

                      @if(auth()->user()->hasRole('L-1-Validator'))
                        @can('farmer')
                         {{--<li><a href="{!! url('l1/all-plot') !!}" class="ai-icon" aria-expanded="false">
                                  <i class="flaticon-381-user-7"></i>
                                  <span class="nav-text">All Farmers</span>
                              </a>
                          </li>--}}
                        @endcan
                      @elseif(auth()->user()->hasRole('L-2-Validator'))
                        @can('farmer')
                          {{--<li><a href="{!! url('l2/all-plot') !!}" class="ai-icon" aria-expanded="false">
                                  <i class="flaticon-381-user-7"></i>
                                  <span class="nav-text">All Farmers</span>
                              </a>
                          </li>--}}
                        @endcan
                      @else
                        {{-- @can('farmer')
                        <!-- for admin -->
                          <li><a href="{!! url('admin/farmers/all-plot') !!}" class="ai-icon" aria-expanded="false">
                                  <i class="flaticon-381-user-7"></i>
                                  <span class="nav-text">All Farmers</span>
                              </a>
                          </li>
                        @endcan --}}
                      @endif

                    <!-- for level 2 validator -->
                    @if(auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('Viewer'))
                      <!-- for admin l2 validation-->
                      @if(auth()->user()->can('L-2-Pending') || auth()->user()->can('L-2-Reject') || auth()->user()->can('L-2-Approval'))
                        <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                  <i class="flaticon-381-user-7"></i>
                                  <span class="nav-text">Validator</span>
                              </a>
                              <ul aria-expanded="true">
                                  @can('L-2-Approval')
                                    <!-- below approved farm is using  FarmerApprovController for all approved plot -->
                                    <li><a href="{!! url('admin/approved/farmer') !!}?filter=all&status=Approved&layout=approvedfarm">Approved</a></li>
                                  @endcan
                                  @can('L-2-Pending')
                                    <!-- list here will be displayed after l1 validator approved it -->
                                    <!-- below link for plot to be approve by level 2 validator. -->
                                    <li><a href="{!! url('admin/l2/plot') !!}?filter=all&status=Pending&layout=l2plot">Pending</a></li>
                                    <!-- <li><a href="{!! route('admin.farmers.index') !!}?filter=all&status=Pending&layout=plot">Pending</a></li>old -->
                                  @endcan
                                  @can('L-2-Reject')
                                    <!-- here list will be displayed after l2 validator will reject plot -->
                                    <!-- below link for rejected plot by level 2 validator and also rejected plot will be displayed in level 1 validator -->
                                    <li><a href="{!! url('admin/l2/plot') !!}?filter=all&status=Rejected&layout=l2plot">Rejected</a></li>
                                  @endcan
                              </ul>
                          </li>
                      @endif
                    @endif

                        @if(Auth::user()->hasRole('L-2-Validator'))
                          @if(auth()->user()->can('L-2-Pending') || auth()->user()->can('L-2-Reject') || auth()->user()->can('L-2-Approval'))
                              <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                    <i class="flaticon-381-user-7"></i>
                                    <span class="nav-text">Onboardingr</span>
                                </a>
                                <ul aria-expanded="true">
                                    @can('L-2-Approval')
                                      <!-- below approved farm is using  FarmerApprovController for all approved plot -->
                                      <!-- <li><a href="{!! url('admin/approved/farmer') !!}?filter=all&status=Approved&layout=approvedfarm">Approved</a></li> -->
                                      <li><a href="{!! url('l2/approved/plots') !!}">Approved</a></li>
                                    @endcan
                                    @can('L-2-Pending')
                                      <!-- list here will be displayed after l1 validator approved it -->
                                      <!-- below link for plot to be approve by level 2 validator. -->
                                      <li><a href="{!! url('l2/pendings/plots') !!}">Pending</a></li>
                                      <!-- <li><a href="{!! route('admin.farmers.index') !!}?filter=all&status=Pending&layout=plot">Pending</a></li>old -->
                                    @endcan
                                    @can('L-2-Reject')
                                      <!-- here list will be displayed after l2 validator will reject plot -->
                                      <!-- below link for rejected plot by level 2 validator and also rejected plot will be displayed in level 1 validator -->
                                      <li><a href="{!! url('l2/reject/plots') !!}">Rejected</a></li>
                                    @endcan
                                </ul>
                            </li>
                          @endif

                                @if(auth()->user()->can('L2-CropData-Approval') || auth()->user()->can('L2-CropData-Pending'))
                                   <li><a class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Cropdata</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L2-CropData-Approval')<li><a href="{!! url('l2/approved/cropdata') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L2-CropData-Pending')<li><a href="{!! url('l2/pendings/cropdata') !!}">Pending</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif

                                @if(auth()->user()->can('L2-Pipe-Approval') || auth()->user()->can('L2-Pipe-Pending') || auth()->user()->can('L2-Pipe-Reject'))
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Pipe Installation</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L2-Pipe-Approval')<li><a href="{!! url('l2/approved/pipe-installations') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L2-Pipe-Pending')<li><a href="{!! url('l2/pendings/pipe-installations') !!}">Pending</a></li>@endcan
                                            <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                            @can('L2-Pipe-Reject')<li><a href="{!! url('l2/rejected/pipe-installations') !!}">Rejected</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif

                                @if(auth()->user()->can('L2-Aeration-Approval') || auth()->user()->can('L2-Aeration-Pending') || auth()->user()->can('L2-Aeration-Reject'))
                                 <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Aeration</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L2-Pipe-Approval')<li><a href="{!! url('l2/approved/aeration') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L2-Pipe-Pending')<li><a href="{!! url('l2/pendings/aeration') !!}">Pending</a></li>@endcan
                                            <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                            @can('L2-Pipe-Reject')<li><a href="{!! url('l2/rejected/aeration') !!}">Rejected</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif
                                @if(auth()->user()->can('L2-Benefit-Approved') || auth()->user()->can('L2-Benefit-Pending') )
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Benefits</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L2-Benefit-Approved')<li><a href="{!! url('l2/approved/benefit') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L2-Benefit-Pending')<li><a href="{!! url('l2/pendings/benefit') !!}">Pending</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif
                                @can('Download Excel')
                                  <li>
                                    <a href="{{ url('l2/download') }}" class="ai-icon" aria-expanded="false">
                                      <i class="fa fa-download"></i>
                                      <span class="nav-text">Excel Download</span>
                                    </a>
                                  </li>
                                @endcan
                                <li>
                                  <a href="{{ url('l2/report') }}" class="ai-icon" aria-expanded="false">
                                    <i class="fa fa-download"></i>
                                    <span class="nav-text">Report</span>
                                  </a>
                                </li>
                        @endif
                    <!-- end for level 2 validator -->


                    <!-- for level 1 validator -->
                    @if(auth()->user()->hasRole('SuperAdmin')  || auth()->user()->hasRole('Viewer'))
                      <!-- for admin -->
                      @if(auth()->user()->can('L-1-Pending') || auth()->user()->can('L-1-Reject') || auth()->user()->can('L-1-Approval'))
                        {{--<li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                <i class="flaticon-381-user-7"></i>
                                <span class="nav-text">L1 Validator</span>
                            </a>
                            <ul aria-expanded="true">
                                  <!-- below approved farm is using display after approval from l1 validator-->
                                  @can('L-1-Approval')<li><a href="{!! route('admin.farmers.index') !!}?filter=all&status=Approved&layout=plot">Approved</a></li>@endcan
                                  <!-- list here will be displayed after onboarding from app -->
                                  <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                  @can('L-1-Pending')<li><a href="{!! route('admin.farmers.index') !!}?filter=all&status=Pending&layout=plot">Pending</a></li>@endcan
                                  <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                  @can('L-1-Reject')<li><a href="{!! route('admin.farmers.index') !!}?filter=all&status=Rejected&layout=plot">Rejected</a></li>@endcan
                            </ul>
                        </li>--}}
                      @endif
                    @endif

                      {{-- here level 1 validator has been commented sidebar
                          @if(Auth::user()->hasRole('L-1-Validator'))
                                @if(auth()->user()->can('L-1-Pending') || auth()->user()->can('L-1-Reject') || auth()->user()->can('L-1-Approval'))
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Onboarding</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L-1-Approval')<li><a href="{!! url('l1/approved/plots') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L-1-Pending')<li><a href="{!! url('l1/pendings/plots') !!}">Pending</a></li>@endcan
                                            <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                            @can('L-1-Reject')<li><a href="{!! url('l1/reject/plots') !!}">Rejected</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif
                                @if(auth()->user()->can('L1-CropData-Approval') || auth()->user()->can('L1-CropData-Pending'))
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                            <i class="flaticon-381-user-7"></i>
                                            <span class="nav-text">Cropdata</span>
                                        </a>
                                        <ul aria-expanded="true">
                                              <!-- below approved farm is using display after approval from l1 validator-->
                                              @can('L1-CropData-Approval')<li><a href="{!! url('l1/approved/cropdata') !!}">Approved</a></li>@endcan
                                              <!-- list here will be displayed after onboarding from app -->
                                              <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                              @can('L1-CropData-Pending')<li><a href="{!! url('l1/pendings/cropdata') !!}">Pending</a></li>@endcan
                                        </ul>
                                    </li>
                                @endif

                                @if(auth()->user()->can('L1-Pipe-Approval') || auth()->user()->can('L1-Pipe-Pending') || auth()->user()->can('L1-Pipe-Reject'))
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Pipe Installation</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L1-Pipe-Approval')<li><a href="{!! url('l1/approved/pipe-installations') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L1-Pipe-Pending')<li><a href="{!! url('l1/pendings/pipe-installations') !!}">Pending</a></li>@endcan
                                            <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                            @can('L1-Pipe-Reject')<li><a href="{!! url('l1/rejected/pipe-installations') !!}">Rejected</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif

                                @if(auth()->user()->can('L1-Aeration-Approval') || auth()->user()->can('L1-Aeration-Pending') || auth()->user()->can('L1-Aeration-Reject'))
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Aeration</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L1-Aeration-Approval')<li><a href="{!! url('l1/approved/aeration') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L1-Aeration-Pending')<li><a href="{!! url('l1/pendings/aeration') !!}">Pending</a></li>@endcan
                                            <!-- here plot will be displayed after rejection from l1 & l2 validator -->
                                            @can('L1-Aeration-Reject')<li><a href="{!! url('l1/rejected/aeration') !!}">Rejected</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif

                                @if(auth()->user()->can('L1-Benefit-Approved') || auth()->user()->can('L1-Benefit-Pending') )
                                  <li><a href="{!! route('admin.farmers.index') !!}" class="has-arrow ai-icon" aria-expanded="false">
                                          <i class="flaticon-381-user-7"></i>
                                          <span class="nav-text">Benefits</span>
                                      </a>
                                      <ul aria-expanded="true">
                                            <!-- below approved farm is using display after approval from l1 validator-->
                                            @can('L1-Benefit-Approved')<li><a href="{!! url('l1/approved/benefit') !!}">Approved</a></li>@endcan
                                            <!-- list here will be displayed after onboarding from app -->
                                            <!-- also when plot is edited from app and it's status changes from rejected to pending and it is displayed here -->
                                            @can('L1-Benefit-Pending')<li><a href="{!! url('l1/pendings/benefit') !!}">Pending</a></li>@endcan
                                      </ul>
                                  </li>
                                @endif

                                <li>
                                @can('Download Excel')
                                  <a href="{{ url('l1/download') }}" class="ai-icon" aria-expanded="false">
                                    <i class="fa fa-download"></i>
                                    <span class="nav-text">Excel Download</span>
                                  </a>
                                @endcan
                                </li>
                                <li>
                                  <a href="{{ url('l1/report') }}" class="ai-icon" aria-expanded="false">
                                    <i class="fa fa-download"></i>
                                    <span class="nav-text">Report</span>
                                  </a>
                                </li>
                        @endif --}}

                      @if(auth()->user()->hasRole('Viewer'))
                        @can('Download Excel')
                            <li>
                                <a href="{{ url('admin/download') }}" class="ai-icon" aria-expanded="false">
                                  <i class="fa fa-download"></i>
                                  <span class="nav-text">Excel Download</span>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="{{ url('admin/report') }}" class="ai-icon" aria-expanded="false">
                              <i class="fa fa-download"></i>
                              <span class="nav-text">Report</span>
                            </a>
                          </li>

                      @endif

                      <!-- for excel download -->
                    @if(auth()->user()->hasAnyRole('SuperAdmin'))
                          @can('Download Excel')
                              <li>
                                  <a href="{{ url('admin/download') }}" class="ai-icon" aria-expanded="false">
                                    <i class="fa fa-download"></i>
                                    <span class="nav-text">Excel Download</span>
                                  </a>
                              </li>
                          @endcan
                          <li>
                            <a href="{{ url('admin/report') }}" class="ai-icon" aria-expanded="false">
                              <i class="fa fa-download"></i>
                              <span class="nav-text">Report</span>
                            </a>
                          </li>

                        <li>
                            <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                            <i class="flaticon-381-settings-2"></i>
                            <span class="nav-text">Settings</span>
                            </a>
                            <ul aria-expanded="false">
                                <li><a href="{!! route('admin.users.index') !!}">Users</a></li>
                                <!-- <li><a href="{!! route('admin.validator.index') !!}">L-1 Validator</a></li> -->
                                <li><a href="{!! route('admin.verifier.index') !!}">Validator</a></li>
                                <li><a href="{!! route('admin.viewer.index') !!}">Viewers</a></li>
                                <li><a href="{!! route('admin.company.index') !!}">Organization</a></li>
                                <li><a href="{!! url('admin/list/admin') !!}">Admin</a></li>
                                <li><a href="{!! url('admin/pipe/setting'); !!}">Pipe Settings</a></li>
                                <li><a href="{!! route('admin.roles.index'); !!}">Roles</a></li>
                                <li><a href="{!! route('admin.permissions.index'); !!}">Permission</a></li>
                                <li><a href="{!! route('admin.landownership.index'); !!}">Land ownership</a></li>
                                <li><a href="{!! url('admin/location')!!}">Location</a></li>
                                <li><a href="{!! route('admin.relationshipowner.index'); !!}">Relationship Owner</a></li>
                                <li><a href="{!! route('admin.cropvariety.index'); !!}">Crop variety</a></li>
                                <li><a href="{!! route('admin.terms-and-conditions.index'); !!}">Terms & Conditions</a></li>
                                <li><a href="{!! route('admin.season.index'); !!}">Season</a></li>
                                <li><a href="{!! route('admin.gender.index'); !!}">Gender</a></li>
                                <li><a href="{!! route('admin.fertilizer.index'); !!}">Fertilizer</a></li>
                                {{-- NOT IN USE <li><a href="{!! route('admin.organization.index'); !!}">Organization</a></li> --}}
                                <li><a href="{!! route('admin.benefit.index'); !!}">Benefit</a></li>
                                <li><a href="{!! url('admin/minimum/value'); !!}">Minimum Value</a></li>
                                <li><a href="{!! url('admin/app/settings'); !!}">App Setting</a></li>
                                <li><a href="{!! url('admin/cropdata/settings'); !!}">CropData Setting</a></li>
                                <li><a href="{!! url('admin/pipe/threshold/settings'); !!}">Pipe Threshold Setting</a></li>
                                <li><a href="{!! url('admin/app/dashboard'); !!}">App Dashboard</a></li>
                                {{-- <li><a href="{!! url('admin/farmer/trash/record'); !!}">Trash data</a></li> --}}
                                <li class='nav-item'><a class='nav-link' href="{{ backpack_url('backup') }}">Backups</a></li>
                                {{--<li><a href="{!! url('admin/company/terms-and-conditions'); !!}">Term and Condition</a></li>--}}
                            </ul>
                        </li>
                    @endif
                </ul>
      <img class="logo-abbr biglogo d-md-block" style="max-width: 182px;margin-top: 144%;" src="{{ asset('images/brand.png') }}" alt="logo">

      </div>

        </div>
