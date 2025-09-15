{{-- Extends layout --}}
@php
$states=\App\Models\State::all();
$farmerassamcountapprove=\App\Models\FinalFarmer::where('state_id',37)->where('final_status_onboarding','Approved')->count();
$farmerwestbengalcountapprove=\App\Models\FinalFarmer::where('state_id',38)->where('final_status_onboarding','Approved')->count();

$farmertelaganacountapprove=\App\Models\FinalFarmer::where('state_id',38)->where('final_status_onboarding','Approved')->count();
$farmerassamcountrejected=\App\Models\FinalFarmer::where('state_id',38)->where('final_status_onboarding','Rejected')->count();

$farmerwestbengalcountrejected=\App\Models\FinalFarmer::where('state_id',38)->where('final_status_onboarding','Rejected')->count();
$farmertelaganacountrejected=\App\Models\FinalFarmer::where('state_id',38)->where('final_status_onboarding','Rejected')->count();

$farmerothercountapproved=\App\Models\FinalFarmer::where('state_id',Null)->where('final_status_onboarding','Approved')->count();
$farmerothercountrejected=\App\Models\FinalFarmer::where('state_id',Null)->where('final_status_onboarding','Rejected')->count();

$organization=\App\Models\Company::all();
$districts=\App\Models\District::all();

@endphp
@extends('layout.default')
@section('content')
<style>
    .accordion-item {
  border: 1px solid #450b5a;
  margin-bottom: 5px;
}

.accordion-title {
  background-color: #450b5a ;
  padding: 10px;
  cursor: pointer;
}

.accordion-content {
  display: none;
  padding: 10px;
}

.active {
  display: block;
}
</style>
            <!-- row -->
	<div class="container-fluid">
		<!-- row -->
                <div id="countBoxRow" class="row">

                    <div class=" countBox countBox1" style="background-color: #450b5a !important;">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="FarmerCount">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Total Farmer
                                </h6>
                            </div>
                        </div>
                    </div>



                    <div class=" bg-info countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="FarmerPlot">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Total Plot
                                </h6>
                            </div>
                        </div>
                    </div>



                    <div class=" bg-success countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="CropData">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Crop Data
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class=" bg-primary countBox" style="background-color: #3b8ce7 !important;">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center pi-font text-white" id="PolyData">0</h2>
                                <h6 class="text-uppercase-mb-3 pin-font font-size-16 text-white text-center">Polygon
                                    </h6>
                            </div>
                        </div>
                    </div>

                    <div class=" bg-info countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center pi-font text-white" id="Pipe">0</h2>
                                <h6 class="text-uppercase-mb-3 pin-font font-size-16 text-white text-center">Pipes
                                    installation</h6>
                            </div>
                        </div>
                    </div>


                    <div class=" bg-warning countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="awd">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Awd Events
                                </h6>
                            </div>
                        </div>
                    </div>


                    {{-- <div class=" bg-danger countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="Benefit">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Farmer
                                    Benefits</h6>
                            </div>
                        </div>
                    </div> --}}



                    <div class=" bg-success cc countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center tt-font text-white" id="TotalArea">0</h2>
                                <h6 class="text-uppercase-mb-3 tt-font text-white text-center">
                                    Total Area in Hectare<span class="a-font">(Approved)</span></h6>
                            </div>
                        </div>
                    </div>
                </div>



            <div class="accordion my-4">
                <div class="accordion-item" id="accordian1">
                <div class="accordion-title">Click to View Charts</div>
                <div class="accordion-content">
                    <div class="col-xl-12 col-xxl-12 my-3">
                    <div class="row">



                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                             Farmer Onboarding Chart
                            </h4>
                            </div>
                            <div class="card-body">
                                <center>
                                    <div id="farmerloader" class=""><img style="margin-top:50%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>

                                {{-- <div id="farmeronboarding" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="farmeronboarding" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                            Crop Data Chart
                            </h4>
                            </div>
                            <div class="card-body">
                                <center>
                                    <div id="croploader" class=""><img  style="margin-top:20%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>

                                {{-- <div id="cropdatachart" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="cropdatachart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                            Polygon Data Chart
                            </h4>
                            </div>
                            <div class="card-body">
                                <center>
                                    <div id="polygonloader" class=""><img  style="margin-top:20%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>

                                {{-- <div id="polygondatachart" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="polygondatachart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                                Pipeinstallation Data Chart
                            </h4>
                            </div>
                            <div class="card-body">
                                <center>
                                    <div id="pipeloader" class=""><img  style="margin-top:20%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>

                                {{-- <div id="pipeinstallationchart" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="pipeinstallationchart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                                Aeration Event 1 Data Chart
                            </h4>
                            </div>

                            <div class="card-body" >
                                <center>
                                    <div id="aerationloader" class=""><img  style="margin-top:20%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>

                                {{-- <div id="aerationchart" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="aerationchart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>



                    <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                            <h4>
                                Aeration Event 2 Data Chart
                            </h4>
                            </div>
                            <div class="card-body">
                                <center>
                                    <div id="aeration2loader" class=""><img  style="margin-top:20%;" src="{{asset('images/loaderr.gif')}}" alt="" height="150px" width="150px"></div>
                                </center>
                                {{-- <div id="aeration2chart" style="height: 370px; width: 100%;"></div> --}}
                                <canvas id="aeration2chart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>


                    </div>
                </div>
            </div>
                </div>
                <div class="accordion-item">
                <div class="accordion-title">Click Here to View Filter Wise Charts.</div>
                <div class="accordion-content">
                    <div class="card">
                        <div class="card-header">
                            <div class="col-md-12 col-xl-12 col-xxl-12 col-lg-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                              <h3>Graphs</h3>
                                            </div>
                                            <div class="card-body">
                                            <div class="row">


                                                <div class="col-md-3 col-xl-3 col-xxl-3 mb-3 form-group">
                                                    <label for="name">To <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="date" class="form-control" id="to_date" name="to_date" >
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-xl-3 col-xxl-3 mb-3 form-group">
                                                    <label for="name">From <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="date" class="form-control" id="from_date" name="from_date" >
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-xl-3 col-xxl-3 mb-3">
                                                    <label>Select For <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                          <select class="form-control" name="for" id="for" required>
                                                              <option value="farmers" >Farmers</option>
                                                              <option value="cropData" >CropData</option>
                                                              <option value="polygon" >Polygon</option>
                                                              <option value="pipeInstallation" >PipeInstallation</option>
                                                              <option value="areation" >Areation</option>
                                                              <option value="benifit">Benifit</option>
                                                          </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-xl-3 col-xxl-3 mb-3">
                                                    <label>State<span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                          <select class="form-control" name="state_id" id="state_id" required>
                                                            <option value="" selected>----select----</option>
                                                            @foreach($states as $state)
                                                              <option value="{{$state->id}}" >{{$state->name}}</option>
                                                            @endforeach
                                                          </select>
                                                    </div>
                                                </div>



                                                <div class="col-12">
                                                    <button type="button" id="filtersubmit" class="btn btn-primary float-right">Submit</button>
                                                </div>
                                            </div>
                                            </div>
                                           </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-body  " id="chartDisplay">
                            <div class="col-xl-12 col-xxl-12 col-lg-12 col-md-12 " id="filterSub">
                                <div class="row">



                            <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                    <h4>
                                    All Filter Chart
                                    </h4>
                                    </div>
                                    <div class="card-body">


                                        {{-- <div id="farmeronboardingfilter" style="height: 370px; width: 100%;"></div> --}}
                                        <canvas id="farmeronboardingfilter" style="width:100%;max-width:700px"></canvas>

                                        <center>
                                            <div id="farmerloaderfilter" class="d-none"><img style="margin-top:1%;" src="{{asset('images/loaderr.gif')}}" alt="" height="100px" width="100px"></div>
                                        </center>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                    <h4>
                                    Organization Report
                                    </h4>
                                    </div>
                                    <div class="card-body">

                                        {{-- <div id="organizationreport" style="height: 370px; width: 100%;"></div> --}}
                                        <canvas id="organizationreport" style="width:100%;max-width:700px"></canvas>
                                        <center>
                                            <div id="organizationdrop" class="d-none"><img style="margin-top:1%;" src="{{asset('images/loaderr.gif')}}" alt="" height="100px" width="100px"></div>
                                        </center>

                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-xxl-6 col-lg-6 col-md-6">
                                <div class="card">
                                  <div class="card-header">
                                  <h4>
                                      District Report
                                  </h4>
                                  </div>
                                  <div class="card-body">
                                      {{-- <div id="chartContainer" style="height: 370px; width: 100%;"></div> --}}
                                      <canvas id="chartContainer" style="width:100%;max-width:700px"></canvas>

                                      <center>
                                        <div id="districtloader" class="d-none"><img style="margin-top:1%;" src="{{asset('images/loaderr.gif')}}" alt="" height="100px" width="100px"></div>
                                    </center>

                                  </div>
                              </div>
                          </div>
                          <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                            <div class="card">
                                <div class="card-header">
                                <h4>
                             Taluka Report
                                </h4>
                                </div>
                                <div class="card-body">

                {{-- <div id="talukafilter" style="height: 370px; width: 100%;"></div> --}}
                <canvas id="talukafilter" style="width:100%;max-width:700px"></canvas>

                <center>
                    <div id="talukaloader" class="d-none"><img style="margin-top:1%;" src="{{asset('images/loaderr.gif')}}" alt="" height="100px" width="100px"></div>
                </center>
                                </div>
                            </div>
                        </div>
                                </div>
                            </div>



                        </div>
                    </div>

                </div>
                </div>
            </div>


                <div class="col-xl-12 col-xxl-12 d-none">
					<div class="row">
						<div class="col-xl-12 col-xxl-12 col-lg-12 col-md-12">
							<div class="card">
								<div class="card-header border-0 pb-0">
									<h4 class="card-title">Farmers</h4>
									<div class="dropdown ml-auto">
										<div class="btn-link" data-toggle="dropdown">

										</div>
									</div>
								</div>
								<div class="card-body d-none">
									<div id="map" style="width: 100%; height:700px;"></div>
								</div>
								<div class="card-footer border-0 pt-0 text-center">
								</div>
							</div>
						</div>
					</div>
				</div>


		 </div>
@endsection
@section('scripts')
<script>
    $(function(){
    $.ajax({
        type:'get',
        url: "{{url('admin/fetch/dashboard/counting')}}",
        dataType: 'Json',
        success: function(data) {
            document.getElementById("FarmerCount").innerHTML = data.farmercount;
            document.getElementById("FarmerPlot").innerHTML = data.farmerplot;
            document.getElementById("CropData").innerHTML = data.crop_data;
            document.getElementById("PolyData").innerHTML = data.poly_data;
            document.getElementById("Pipe").innerHTML = data.pipeinstall;
            document.getElementById("awd").innerHTML = data.awd;
            document.getElementById("TotalArea").innerHTML = data.totalarea;
        },
        error: function (jqXHR, textStatus, errorThrown) {

        }
    });
});

    // function fetchorganization(id){
    //     var state_id = id; //$(this).val();
    //     if (state_id) {
    //         $.ajax({
    //             type: 'post',
    //             url: "{{url('admin/fetch/organization')}}",
    //             dataType: 'Json',
    //             data: { _token: '{{csrf_token()}}', 'state_id': state_id },
    //             success: function (data) {
    //                 console.log(data.data,'dataaa');
    //                 $('#organization').empty();
    //                 $('#organization').append('<option selected disabled value="">Select Organization</option>');
    //                 $.each(data.data, function (i, v) {
    //                     console.log(i,v,'checking');
    //                     $('#organization').append('<option value="' + v.id + '">' + v.company + '</option>');
    //                 });
    //             },
    //             error: function (xhr, status, error) {
    //                 console.log(error); // Check for any errors in the console
    //             }
    //         });
    //     } else {
    //         $('#organization').empty();
    //     }
    // }
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"> --}}
{{-- <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"> --}}
{{-- <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
</script>


<script type="text/javascript">

// function fetchorganization(id) {
//     var state_id = id; //$(this).val();
//     if (state_id) {
//         var xhr = new XMLHttpRequest();
//         xhr.open('POST', "{{url('admin/fetch/organization')}}", true);
//         xhr.setRequestHeader('Content-Type', 'application/json');
//         xhr.onreadystatechange = function () {
//             $('#organization').addClass('d-none');
//             if (xhr.readyState === XMLHttpRequest.DONE) {
//                 if (xhr.status === 200) {
//                     var data = JSON.parse(xhr.responseText);
//                     console.log(data.data, 'dataaa');
//                     var select = document.getElementById("organization");
//                     select.innerHTML = ''; // Clear existing options
//                     var defaultOption = document.createElement('option');
//                     defaultOption.value = '';
//                     defaultOption.text = 'Select Organization';
//                     defaultOption.disabled = true;
//                     defaultOption.selected = true;
//                     select.appendChild(defaultOption);
//                     data.data.forEach(function (v) {
//                         var option = document.createElement('option');
//                         option.value = v.id;
//                         option.text = v.company;
//                         select.appendChild(option);
//                     });
//                 } else {
//                     console.error('Error fetching data:', xhr.status);
//                 }
//             }
//         };
//         xhr.send(JSON.stringify({ _token: '{{csrf_token()}}', 'state_id': state_id }));
//     } else {
//         var select = document.getElementById("organization");
//         select.innerHTML = ''; // Clear options if state_id is empty
//     }
// }



document.addEventListener("DOMContentLoaded", function() {
  const accordionItems = document.querySelectorAll('.accordion-item');

  accordionItems.forEach(item => {
    const title = item.querySelector('.accordion-title');

    title.addEventListener('click', () => {
      accordionItems.forEach(accItem => {
        if (accItem !== item) {
          accItem.querySelector('.accordion-content').classList.remove('active');
        }
      });

      const content = item.querySelector('.accordion-content');
      content.classList.toggle('active');
    });
  });

});


setTimeout(function() {
//     var farmerassamcountapprove = JSON.parse('{{ json_encode($farmerassamcountapprove) }}');
// var farmerwestbengalcountapprove = JSON.parse('{{ json_encode($farmerwestbengalcountapprove) }}');
// var farmertelaganacountapprove = JSON.parse('{{ json_encode($farmertelaganacountapprove) }}');
// var farmerassamcountrejected = JSON.parse('{{ json_encode($farmerassamcountrejected) }}');
// var farmerwestbengalcountrejected = JSON.parse('{{ json_encode($farmerwestbengalcountrejected) }}');
// var farmertelaganacountrejected = JSON.parse('{{ json_encode($farmertelaganacountrejected) }}');
// var farmerothercountapproved = JSON.parse('{{ json_encode($farmerothercountapproved) }}');
// var farmerothercountrejected = JSON.parse('{{ json_encode($farmerothercountrejected) }}');

var states = {!! json_encode($states) !!};
console.log("onbaording" ,states);
var approveCounts = {!! json_encode($approveCounts) !!};
var rejectCounts = {!! json_encode($rejectCounts) !!};
var pendingCounts = {!! json_encode($pendingCounts) !!};


$('#farmerloader').removeClass('d-none');

var dataPoints = [];

// Loop through each state and construct data points for approved, rejected, and pending counts
for (var i = 0; i < states.length; i++) {
    var state = states[i];
    var stateId = state.id;
    var stateName = state.name;

    var approved = approveCounts[stateId] || 0;
    var rejected = rejectCounts[stateId] || 0;
    var pending =  pendingCounts[stateId] || 0;

    dataPoints.push({
        label: stateName, // Use state name and ID as label
        approved: approved,
        pending: pending,
        rejected: rejected
    });
}


$('#farmerloader').addClass('d-none');
var ctx = document.getElementById('farmeronboarding').getContext('2d');
var chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: dataPoints.map(function(point) { return point.label; }),
        datasets: [{
            label: 'Approved',
            data: dataPoints.map(function(point) { return point.approved; }),
            backgroundColor: 'green',
            barPercentage: 0.8, // Adjust the width of the bars
            categoryPercentage: 0.7 // Adjust the space between bars
        }, {
            label: 'Pending',
            data: dataPoints.map(function(point) { return point.pending; }),
            backgroundColor: '#2781d5',
            barPercentage: 0.8,
            categoryPercentage: 0.7
        }, {
            label: 'Rejected',
            data: dataPoints.map(function(point) { return point.rejected; }),
            backgroundColor: 'red',
            barPercentage: 0.8, // Adjust the width of the bars
            categoryPercentage: 0.7 // Adjust the space between bars
        }]
    },
    options: {
        title: {
            display: true,
            text: 'Farmer Onboarding',
            fontFamily: 'Arial',
            fontSize: 20
        },
        legend: {
            display: true
        },
        tooltips: {
            mode: 'index',
            intersect: false
        },
        responsive: true,
        scales: {
            xAxes: [{
                stacked: false, // Set stacked to false
                scaleLabel: {
                    display: true,
                    labelString: 'States'
                }
            }],
            yAxes: [{
                stacked: false, // Set stacked to false
                scaleLabel: {
                    display: true,
                    labelString: 'Data'
                }
            }]
        }
    }
});


//     var chart = new CanvasJS.Chart("farmeronboarding", {
// 	exportEnabled: true,
// 	animationEnabled: true,
// 	title:{
// 		text: "Farmer Onboarding"
// 	},
// 	subtitles: [{
// 		text: "Pending And Approved State Wise."
// 	}],
// 	axisX: {
// 		title: "States"
// 	},
// 	toolTip: {
// 		shared: true
// 	},
// 	legend: {
// 		cursor: "pointer",
// 		itemclick: toggleDataSeries
// 	},

// 	data: [{
// 		type: "column",
// 		name: "Approved",
// 		showInLegend: true,
// 		yValueFormatString: "#,##0.# Data",
//         color: "green",
// 		dataPoints: [
// 			{ label: "Assam",  y: farmerassamcountapprove },
// 			{ label: "West Bengal", y: farmerwestbengalcountapprove },
// 			{ label: "Telangana", y: farmertelaganacountapprove },
//             { label: "Other", y: farmerothercountapproved },
// 		]
// 	},

// 	{
// 		type: "column",
// 		name: "Rejected",
// 		axisYType: "secondary",
// 		showInLegend: true,
// 		yValueFormatString: "#,##0.# Data",
//         color: "Red",
// 		dataPoints: [
// 			{ label: "Assam", y: farmerassamcountrejected },
// 			{ label: "West Bengal", y: farmerwestbengalcountrejected },
// 			{ label: "Telangana", y: farmertelaganacountrejected },
//             { label: "Other", y: farmerothercountrejected },
// 		]
// 	}]
// });
// chart.render();

// function toggleDataSeries(e) {
// 	if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
// 		e.dataSeries.visible = false;
// 	} else {
// 		e.dataSeries.visible = true;
// 	}
// 	e.chart.render();
// }


//croploader();
}, 1000);
//------------------------------------Crop Data Chart ----------------------------------------//
// Function to load crop data asynchronously
function loadCropData() {
    // Show loading indicator
    $('#croploader').removeClass('d-none');

    // Fetch crop data from server
    $.ajax({
        type: 'get',
        url: "{{url('admin/crop/data/count')}}",
        dataType: 'Json',
        success: function (data) {
            // Extract data from response
            var states = data.states;
            var approveCounts = data.approveCounts;
            var rejectCounts = data.rejectCounts;
            var pendingCounts = data.pendingCounts;

            var dataPoints = [];

            // Iterate over states to construct data points
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0;
                var rejected = rejectCounts[stateId] || 0;
                var pending = pendingCounts[stateId] || 0;

                // Push data point for each state
                dataPoints.push({
                    label: stateName,
                    approved: approved,
                    rejected: rejected,
                    pending: pending
                });
            }

            // Hide loading indicator
            $('#croploader').addClass('d-none');

            // Draw chart using fetched data
            drawChart(dataPoints);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error loading crop data:", errorThrown);
        }
    });
}

// Function to draw chart using provided data
function drawChart(dataPoints) {
    var ctx = document.getElementById('cropdatachart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPoints.map(function (point) { return point.label; }),
            datasets: [
                {
                    label: 'Approved',
                    data: dataPoints.map(function (point) { return point.approved; }),
                    backgroundColor: 'green',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                },
                {
                    label: 'Pending',
                    data: dataPoints.map(function (point) { return point.pending; }),
                    backgroundColor: '#2781d5',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                },
                {
                    label: 'Rejected',
                    data: dataPoints.map(function (point) { return point.rejected; }),
                    backgroundColor: 'red',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                }
            ]
        },
        options: {
            title: {
                display: true,
                text: 'Crop Data',
                fontSize: 20
            },
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'States'
                    }
                }],
                yAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Data'
                    }
                }]
            }
        }
    });
}

// Call the function to load crop data when needed
loadCropData();


    //------------------------------------Polygon Mapping Chart ----------------------------------------//
// Function to load polygon data asynchronously
function loadPolygonData() {
    // Show loading indicator
    $('#polygonloader').removeClass('d-none');

    // Fetch polygon data from server
    $.ajax({
        type: 'get',
        url: "{{url('admin/polygon/data/count')}}",
        dataType: 'json',
        success: function (data) {
            // Extract data from response
            var states = data.states;
            var approveCounts = data.approveCounts;
            var rejectCounts = data.rejectCounts;
            var pendingCounts = data.pendingCounts;

            var dataPoints = [];

            // Iterate over states to construct data points
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0;
                var rejected = rejectCounts[stateId] || 0;
                var pending = pendingCounts[stateId] || 0;

                // Push data point for each state
                dataPoints.push({
                    label: stateName,
                    approved: approved,
                    rejected: rejected,
                    pending: pending
                });
            }

            // Hide loading indicator
            $('#polygonloader').addClass('d-none');

            // Draw chart using fetched data
            drawPolygonChart(dataPoints);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error loading polygon data:", errorThrown);
        }
    });
}

// Function to draw chart using provided data
function drawPolygonChart(dataPoints) {
    var ctx = document.getElementById('polygondatachart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPoints.map(function (point) { return point.label; }),
            datasets: [
                {
                    label: 'Approved',
                    data: dataPoints.map(function (point) { return point.approved; }),
                    backgroundColor: 'green',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                },
                {
                    label: 'Pending',
                    data: dataPoints.map(function (point) { return point.pending; }),
                    backgroundColor: '#2781d5',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                },
                {
                    label: 'Rejected',
                    data: dataPoints.map(function (point) { return point.rejected; }),
                    backgroundColor: 'red',
                    barPercentage: 0.7, // Adjust the width of the bars
                    categoryPercentage: 0.5 // Adjust the space between bars
                }
            ]
        },
        options: {
            title: {
                display: true,
                text: 'Polygon Data',
                fontSize: 20
            },
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'States'
                    }
                }],
                yAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Data'
                    }
                }]
            }
        }
    });
}

// Call the function to load polygon data when needed
loadPolygonData();



//------------------------------------Pipe Installation Chart ----------------------------------------//
// Function to load pipe installation data asynchronously
function loadPipeInstallationData() {
    // Show loading indicator
    $('#pipeloader').removeClass('d-none');

    // Fetch pipe installation data from server
    $.ajax({
        type: 'get',
        url: "{{url('admin/pipeinstallation/data/count')}}",
        dataType: 'json',
        success: function (data) {
            // Extract data from response
            var states = data.states;
            var approveCounts = data.approveCounts;
            var rejectCounts = data.rejectCounts;
            var pendingCounts = data.pendingCounts;

            var dataPoints = [];

            // Loop through each state and construct data points for approved, rejected, and pending counts
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0;
                var rejected = rejectCounts[stateId] || 0;
                var pending = pendingCounts[stateId] || 0;

                dataPoints.push({
                    label: stateName,
                    approved: approved,
                    rejected: rejected,
                    pending: pending
                });
            }

            // Hide loading indicator
            $('#pipeloader').addClass('d-none');

            // Draw chart using fetched data
            drawPipeInstallationChart(dataPoints);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error loading pipe installation data:", errorThrown);
        }
    });
}

// Function to draw chart using provided data
function drawPipeInstallationChart(dataPoints) {
    var ctx = document.getElementById('pipeinstallationchart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPoints.map(function (point) { return point.label; }),
            datasets: [
                {
                    label: 'Approved',
                    data: dataPoints.map(function (point) { return point.approved; }),
                    backgroundColor: 'green'
                },
                {
                    label: 'Pending',
                    data: dataPoints.map(function (point) { return point.pending; }),
                    backgroundColor: '#2781d5'
                },
                {
                    label: 'Rejected',
                    data: dataPoints.map(function (point) { return point.rejected; }),
                    backgroundColor: 'red'
                }
            ]
        },
        options: {
            title: {
                display: true,
                text: 'Pipe Installation',
                fontSize: 20
            },
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    stacked: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'States'
                    }
                }],
                yAxes: [{
                    stacked: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'Data'
                    }
                }]
            }
        }
    });
}

// Call the function to load pipe installation data when needed
loadPipeInstallationData();


// //------------------------------------Aeration Chart ----------------------------------------//
// Function to load aeration data asynchronously
function loadAerationData() {
    // Show loading indicator
    $('#aerationloader').removeClass('d-none');

    // Fetch aeration data from server
    $.ajax({
        type: 'get',
        url: "{{url('admin/aeration/data/count')}}",
        dataType: 'json',
        success: function (data) {
            // Extract data from response
            var states = data.states;
            var approveCounts = data.approveCounts;
            var rejectCounts = data.rejectCounts;
            var pendingCounts = data.pendingCounts;

            var dataPoints = [];

            // Loop through each state and construct data points for approved, rejected, and pending counts
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0;
                var rejected = rejectCounts[stateId] || 0;
                var pending = pendingCounts[stateId] || 0;

                dataPoints.push({
                    label: stateName,
                    approved: approved,
                    rejected: rejected,
                    pending: pending
                });
            }

            // Hide loading indicator
            $('#aerationloader').addClass('d-none');

            // Draw chart using fetched data
            drawAerationChart(dataPoints);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error loading aeration data:", errorThrown);
        }
    });
}

// Function to draw chart using provided data
function drawAerationChart(dataPoints) {
    var ctx = document.getElementById('aerationchart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPoints.map(function (point) { return point.label; }),
            datasets: [
                {
                    label: 'Approved',
                    data: dataPoints.map(function (point) { return point.approved; }),
                    backgroundColor: 'green',
                    hoverBackgroundColor: "lightgreen",
                },
                {
                    label: 'Rejected',
                    data: dataPoints.map(function (point) { return point.rejected; }),
                    backgroundColor: 'red',
                    hoverBackgroundColor: "lightred",
                },
                {
                    label: 'Pending',
                    data: dataPoints.map(function (point) { return point.pending; }),
                    backgroundColor: '#2781d5',
                    hoverBackgroundColor: "#2f98fa",
                }
            ]
        },
        options: {
            title: {
                display: true,
                text: 'Aeration Event',
                fontSize: 20
            },
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'States'
                    }
                }],
                yAxes: [{
                    stacked: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'Data'
                    }
                }]
            }
        }
    });
}

// Call the function to load aeration data when needed
loadAerationData();


// //------------------------------------Aeration 2 Chart ----------------------------------------//
// Function to load aeration 2 data asynchronously
function loadAeration2Data() {
    // Show loading indicator
    $('#aeration2loader').removeClass('d-none');

    // Fetch aeration 2 data from server
    $.ajax({
        type: 'get',
        url: "{{url('admin/aeration2/data/count')}}",
        dataType: 'json',
        success: function (data) {
            // Extract data from response
            var states = data.states;
            var approveCounts = data.approveCounts;
            var rejectCounts = data.rejectCounts;
            var pendingCounts = data.pendingCounts;

            var dataPoints = [];

            // Loop through each state and construct data points for approved, rejected, and pending counts
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0;
                var rejected = rejectCounts[stateId] || 0;
                var pending = pendingCounts[stateId] || 0;

                dataPoints.push({
                    label: stateName,
                    approved: approved,
                    rejected: rejected,
                    pending: pending
                });
            }

            // Hide loading indicator
            $('#aeration2loader').addClass('d-none');

            // Draw chart using fetched data
            drawAeration2Chart(dataPoints);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error loading aeration 2 data:", errorThrown);
        }
    });
}

// Function to draw chart using provided data
function drawAeration2Chart(dataPoints) {
    var ctx = document.getElementById('aeration2chart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataPoints.map(function (point) { return point.label; }),
            datasets: [
                {
                    label: 'Approved',
                    data: dataPoints.map(function (point) { return point.approved; }),
                    backgroundColor: 'green'
                },
                {
                    label: 'Rejected',
                    data: dataPoints.map(function (point) { return point.rejected; }),
                    backgroundColor: 'red'
                },
                {
                    label: 'Pending',
                    data: dataPoints.map(function (point) { return point.pending; }),
                    backgroundColor: '#2781d5'
                }
            ]
        },
        options: {
            title: {
                display: true,
                text: 'Aeration 2 Event',
                fontSize: 20
            },
            legend: {
                display: true
            },
            scales: {
                xAxes: [{
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'States'
                    }
                }],
                yAxes: [{
                    stacked: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'Data'
                    }
                }]
            }
        }
    });
}

// Call the function to load aeration 2 data when needed
loadAeration2Data();


// }, "3000");

// $('#accordian1').on('click',function(){


//------------------------------------Farmer Onboarding Chart ----------------------------------------//
// });

//--------------------------------------------------FILTER----------------------------------------------------------------//

$('#filtersubmit').on('click', function () {
    // alert('raj');
    var forr = $('#for').val();
    var to_date = $('#to_date').val();
    var from_date = $('#from_date').val();
    var stateid = $('#state_id').val();

    $('#for').prop('required', true);
    $('#to_date').prop('required', true);
    $('#from_date').prop('required', true);
    $('#state_id').prop('required', true);

    if (forr === '' || to_date === '' || from_date === '' || stateid === '') {
        alert('Please fill  all  fields.');
        return false;
    }
    $('#chartDisplay').removeClass('d-none');
    $('#filtersub').removeClass('d-none');
  $('#farmerloaderfilter').removeClass('d-none');

$.ajax({
    type: 'post',
    url: "{{route('fetch.filter.graph')}}",
    dataType: 'Json',
    data: { _token: '{{csrf_token()}}', 'forr': forr, 'to_date': to_date, 'from_date': from_date, 'state_id': stateid },
    success: function (data) {
        $('#farmerloaderfilter').addClass('d-none');

        // Prepare data for Chart.js
        var chartData = {
            labels: [data.statename],
            datasets: [
                {
                    label: "Approved",
                    backgroundColor: "green",
                    data: [data.approved]
                },
                {
                    label: "Pending",
                    backgroundColor: "#2781d5 ",
                    data: [data.pending]
                },
                {
                    label: "Rejected",
                    backgroundColor: "red",
                    data: [data.rejected]
                }
            ]
        };

        // Adjust data for side-by-side bars
        var labels = chartData.labels;
        var datasets = chartData.datasets.map(function(dataset) {
            return {
                label: dataset.label,
                backgroundColor: dataset.backgroundColor,
                data: [dataset.data[0]],
                barThickness: 50
            };
        });

        var ctx = document.getElementById('farmeronboardingfilter').getContext('2d');
        var farmerfilter = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                title: {
                    display: true,
                    text: data.forr,
                    fontSize: 20
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                responsive: true,
                legend: {
                    position: 'top',
                },
                scales: {
                    xAxes: [{
                        stacked: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'State'
                        }
                    }],
                    yAxes: [{
                        stacked: false, // Change to false for side-by-side bars
                        scaleLabel: {
                            display: true,
                            labelString: 'Count'
                        },
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                }
            }
        });
    },
    error: function (xhr, status, error) {
        // Handle error
    }
});
$('#organizationdrop').removeClass('d-none');
    $('#organizationdrop').removeClass('d-none');

    // Clear cache
    $.ajax({
        type: 'post',
        url: "{{ route('cache.clear') }}",
        dataType: 'json',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.success) {
                // After clearing cache, fetch data
                $.ajax({
                    type: 'post',
                    url: "{{ route('organization.fetch.filter.graph') }}",
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}',
                        'forr': forr,
                        'to_date': to_date,
                        'from_date': from_date,
                        'state_id': stateid
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#organizationdrop').addClass('d-none');
                            var organizationData = response.organizationData;
                            var dataPoints = [];

                            for (var organizationId in organizationData) {
                                if (organizationData.hasOwnProperty(organizationId)) {
                                    var organizationCounts = organizationData[organizationId];
                                    var organizationName = organizationCounts['name'];
                                    var approvedCount = organizationCounts['Approved'] || 0;
                                    var pendingCount = organizationCounts['Pending'] || 0;
                                    var rejectedCount = organizationCounts['Rejected'] || 0;

                                    dataPoints.push({
                                        label: organizationName,
                                        approved: approvedCount,
                                        pending: pendingCount,
                                        rejected: rejectedCount
                                    });
                                }
                            }

                            var labels = dataPoints.map(function (organization) {
                                return organization.label;
                            });

                            var datasets = [
                                {
                                    label: "Approved",
                                    backgroundColor: "green",
                                    data: dataPoints.map(function (organization) {
                                        return organization.approved;
                                    }),
                                    barThickness: 50
                                },
                                {
                                    label: "Pending",
                                    backgroundColor: "#2781d5",
                                    data: dataPoints.map(function (organization) {
                                        return organization.pending;
                                    }),
                                    barThickness: 50
                                },
                                {
                                    label: "Rejected",
                                    backgroundColor: "red",
                                    data: dataPoints.map(function (organization) {
                                        return organization.rejected;
                                    }),
                                    barThickness: 50
                                }
                            ];

                            var ctx = document.getElementById('organizationreport').getContext('2d');
                            var organizationfilter = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: datasets
                                },
                                options: {
                                    title: {
                                        display: true,
                                        text: "Organization Report",
                                        fontSize: 20
                                    },
                                    tooltips: {
                                        mode: 'index',
                                        intersect: false
                                    },
                                    responsive: true,
                                    legend: {
                                        position: 'top',
                                        onClick: function (event, legendItem) {
                                            var index = legendItem.datasetIndex;
                                            var meta = this.chart.getDatasetMeta(index);
                                            meta.hidden = meta.hidden === null ? !this.chart.data.datasets[index].hidden : null;
                                            this.chart.update();
                                        }
                                    },
                                    scales: {
                                        xAxes: [{
                                            stacked: false,
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Organizations'
                                            }
                                        }],
                                        yAxes: [{
                                            stacked: false,
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Count'
                                            },
                                            ticks: {
                                                beginAtZero: true
                                            }
                                        }]
                                    }
                                }
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                console.error('Failed to clear cache');
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });

//-----------------------------------------------------------District Data--------------------------------------------------------------//
$('#districtloader').removeClass('d-none');
$.ajax({
    type: 'post',
    url: "{{ route('district.fetch.filter.graph') }}",
    dataType: 'json',
    data: {
        _token: '{{ csrf_token() }}',
        'forr': forr,
        'to_date': to_date,
        'from_date': from_date,
        'state_id': stateid
    },
    success: function (response) {
        if (response.success) {
            $('#districtloader').addClass('d-none');
            var districtData = response.district_data;
            var stateName = response.state_name;

            var labels = [];
            var approvedData = [];
            var pendingData = [];
            var rejectedData = [];

            for (var districtName in districtData) {
                var district = districtData[districtName];
                labels.push(districtName);
                approvedData.push(district['Approved'] || 0);
                pendingData.push(district['Pending'] || 0);
                rejectedData.push(district['Rejected'] || 0);
            }

            var datasets = [
                {
                    label: "Approved",
                    backgroundColor: "green",
                    data: approvedData,
                    barThickness: 20 // Adjust bar thickness as needed
                },
                {
                    label: "Pending",
                    backgroundColor: "blue",
                    data: pendingData,
                    barThickness: 20 // Adjust bar thickness as needed
                },
                {
                    label: "Rejected",
                    backgroundColor: "red",
                    data: rejectedData,
                    barThickness: 20 // Adjust bar thickness as needed
                }
            ];

            var ctx = document.getElementById('chartContainer').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'horizontalBar', // Change chart type to horizontalBar
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    title: {
                        display: true,
                        text: "District Report - " + stateName, // Display state name in the title
                        fontSize: 20
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                var count = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                return datasetLabel + ': ' + count;
                            }
                        }
                    },
                    responsive: true,
                    legend: {
                        position: 'top',
                        onClick: function(event, legendItem) {
                            var index = legendItem.datasetIndex;
                            var meta = this.chart.getDatasetMeta(index);
                            meta.hidden = meta.hidden === null ? !this.chart.data.datasets[index].hidden : null;
                            this.chart.update();
                        }
                    },
                    scales: {
                        xAxes: [{
                            stacked: false,
                            scaleLabel: {
                                display: true,
                                labelString: 'Count' // Adjust x-axis label
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                        yAxes: [{
                            stacked: false,
                            scaleLabel: {
                                display: true,
                                labelString: 'Districts' // Adjust y-axis label
                            }
                        }]
                    }
                }
            });
        }
    },
    error: function (xhr, status, error) {
        console.error(error);
    }
});


//------------------------------------------------------------------Taluka Data---------------------------------------------------------//
$('#talukaloader').removeClass('d-none');
$.ajax({
    type: 'post',
    url: "{{ route('taluka.fetch.filter.graph') }}",
    dataType: 'json',
    data: {
        _token: '{{ csrf_token() }}',
        'forr': forr,
        'to_date': to_date,
        'from_date': from_date,
        'state_id': stateid
    },
    success: function (response) {
        if (response.success) {
            $('#talukaloader').addClass('d-none');
            var talukaData = response.taluka_data;
            var chartData = [];
            var stateName = response.state_name;

            for (var talukaName in talukaData) {
                var taluka = talukaData[talukaName];
                chartData.push({
                    label: talukaName,
                    approved: taluka['Approved'] || 0,
                    pending: taluka['Pending'] || 0,
                    rejected: taluka['Rejected'] || 0
                });
            }

            var labels = chartData.map(function(item) {
                return item.label;
            });

            var datasets = [
                {
                    label: "Approved",
                    backgroundColor: "green",
                    data: chartData.map(function(item) {
                        return item.approved;
                    }),
                    barThickness: 10
                },
                {
                    label: "Pending",
                    backgroundColor: "#2781d5 ",
                    data: chartData.map(function(item) {
                        return item.pending;
                    }),
                    barThickness: 10
                },
                {
                    label: "Rejected",
                    backgroundColor: "red",
                    data: chartData.map(function(item) {
                        return item.rejected;
                    }),
                    barThickness: 10
                }
            ];

            var ctx = document.getElementById('talukafilter').getContext('2d');
            var talukafilter = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    title: {
                        display: true,
                        text: "Taluka/Block Report",
                        fontSize: 20
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': ' + tooltipItem.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    legend: {
                        position: 'top',
                        onClick: function(event, legendItem) {
                            var index = legendItem.datasetIndex;
                            var meta = this.chart.getDatasetMeta(index);
                            meta.hidden = meta.hidden === null ? !this.chart.data.datasets[index].hidden : null;
                            this.chart.update();
                        }
                    },
                    scales: {
                        xAxes: [{
                            stacked: false,
                            scaleLabel: {
                                display: true,
                                labelString: 'Taluka/Block'
                            }
                        }],
                        yAxes: [{
                            stacked: false,
                            scaleLabel: {
                                display: true,
                                labelString: 'Count'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }
    },
    error: function (xhr, status, error) {
        console.error(error);
    }
});

});






window.onload =function(){
//--------------------------------------------------Organization Report-------------------------------------------//


//-----------------------------------------------District Report----------------------------------------//

//---------------------------------------------------------------------Taluka/Block Report---------------------------------------------------------------------//
// $('#talukaloader').removeClass('d-none');
//     $('#talukafilter').addClass('d-none');

}






</script>
@stop
