<?php
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

?>

<?php $__env->startSection('content'); ?>
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
                                    <div id="farmerloader" class=""><img style="margin-top:50%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>

                                <div id="farmeronboarding" style="height: 370px; width: 100%;"></div>
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
                                    <div id="croploader" class=""><img  style="margin-top:20%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>

                                <div id="cropdatachart" style="height: 370px; width: 100%;"></div>
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
                                    <div id="polygonloader" class=""><img  style="margin-top:20%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>

                                <div id="polygondatachart" style="height: 370px; width: 100%;"></div>
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
                                    <div id="pipeloader" class=""><img  style="margin-top:20%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>
                              
                                <div id="pipeinstallationchart" style="height: 370px; width: 100%;"></div>
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
                                    <div id="aerationloader" class=""><img  style="margin-top:20%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>

                                <div id="aerationchart" style="height: 370px; width: 100%;"></div>
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
                                    <div id="aeration2loader" class=""><img  style="margin-top:20%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="150px" width="150px"></div>
                                </center>
                                <div id="aeration2chart" style="height: 370px; width: 100%;"></div>
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
                                                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                              <option value="<?php echo e($state->id); ?>" ><?php echo e($state->name); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>    
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
                        <div class="card-body">
                            <div class="col-xl-12 col-xxl-12 col-lg-12 col-md-12">
                                <div class="row">
                             
        
                                  
                            <div class="col-xl-6 col-xx-6 col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                    <h4>
                                    All Filter Chart
                                    </h4> 
                                    </div>
                                    <div class="card-body">
                                       
        
                                        <div id="farmeronboardingfilter" style="height: 370px; width: 100%;"></div>
                                        <center>
                                            <div id="farmerloaderfilter" class="d-none"><img style="margin-top:1%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="100px" width="100px"></div>
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
                                     
                                        <div id="organizationreport" style="height: 370px; width: 100%;"></div>
                                        <center>
                                            <div id="organizationdrop" class="d-none"><img style="margin-top:1%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="100px" width="100px"></div>
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
                                      <div id="chartContainer" style="height: 370px; width: 100%;"></div>

                                      <center>
                                        <div id="districtloader" class="d-none"><img style="margin-top:1%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="100px" width="100px"></div>
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
                                   
                <div id="talukafilter" style="height: 370px; width: 100%;"></div>
                <center>
                    <div id="talukaloader" class="d-none"><img style="margin-top:1%;" src="<?php echo e(asset('images/loaderr.gif')); ?>" alt="" height="100px" width="100px"></div>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script>
    $(function(){
    $.ajax({
        type:'get',
        url: "<?php echo e(url('admin/fetch/dashboard/counting')); ?>",
        dataType: 'Json',
        success: function(data) {
            document.getElementById("FarmerCount").innerHTML = data.farmercount;
            document.getElementById("FarmerPlot").innerHTML = data.farmerplot;
            document.getElementById("CropData").innerHTML = data.crop_data;
            document.getElementById("PolyData").innerHTML = data.poly_data;
            document.getElementById("Pipe").innerHTML = data.pipeinstall;
            document.getElementById("awd").innerHTML = data.awd;
            // document.getElementById("Benefit").innerHTML = data.benefit;
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
    //             url: "<?php echo e(url('admin/fetch/organization')); ?>",
    //             dataType: 'Json',
    //             data: { _token: '<?php echo e(csrf_token()); ?>', 'state_id': state_id },
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

<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<script type="text/javascript" src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>


<script type="text/javascript">

// function fetchorganization(id) {
//     var state_id = id; //$(this).val();
//     if (state_id) {
//         var xhr = new XMLHttpRequest();
//         xhr.open('POST', "<?php echo e(url('admin/fetch/organization')); ?>", true);
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
//         xhr.send(JSON.stringify({ _token: '<?php echo e(csrf_token()); ?>', 'state_id': state_id }));
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
//     var farmerassamcountapprove = JSON.parse('<?php echo e(json_encode($farmerassamcountapprove)); ?>');
// var farmerwestbengalcountapprove = JSON.parse('<?php echo e(json_encode($farmerwestbengalcountapprove)); ?>');
// var farmertelaganacountapprove = JSON.parse('<?php echo e(json_encode($farmertelaganacountapprove)); ?>');
// var farmerassamcountrejected = JSON.parse('<?php echo e(json_encode($farmerassamcountrejected)); ?>');
// var farmerwestbengalcountrejected = JSON.parse('<?php echo e(json_encode($farmerwestbengalcountrejected)); ?>');
// var farmertelaganacountrejected = JSON.parse('<?php echo e(json_encode($farmertelaganacountrejected)); ?>');
// var farmerothercountapproved = JSON.parse('<?php echo e(json_encode($farmerothercountapproved)); ?>');
// var farmerothercountrejected = JSON.parse('<?php echo e(json_encode($farmerothercountrejected)); ?>');

var states = <?php echo json_encode($states); ?>;
console.log("onbaording" ,states);
var approveCounts = <?php echo json_encode($approveCounts); ?>;
var rejectCounts = <?php echo json_encode($rejectCounts); ?>;
var pendingCounts = <?php echo json_encode($pendingCounts); ?>;

$('#farmerloader').removeClass('d-none');
$('#farmerloader').addClass('d-none');

var dataPoints = [];

// Loop through each state and construct data points for approved and rejected counts
for (var i = 0; i < states.length; i++) {
    var state = states[i];
    var stateId = state.id;
    var stateName = state.name;
    
    var approved = approveCounts[stateId] || 0; 
    var rejected = rejectCounts[stateId] || 0;
    var pending =  pendingCounts[stateId] || 0;

    dataPoints.push({
        label: stateName, // Use state name and ID as label
        y: approved,
        legendText: "Approved",
        color: "green"
    }, 
    {
        label: stateName ,
        y: pending,
        legendText: "Pending",
        color: "blue"
    },
    {
        label: stateName ,
        y: rejected,
        legendText: "Rejected",
        color: "red"
    });
}

var chart = new CanvasJS.Chart("farmeronboarding", {
    exportEnabled: true,
    animationEnabled: true,
    title: {
        text: "Farmer Onboarding",
        fontFamily: "Arial", 
        fontSize: 20 
        
    },
    // subtitles: [{
    //     text: "Approved and Rejected State Wise."
    // }],
    // axisX: {
    //     title: "States"
    // },
    toolTip: {
        shared: true
    },
    legend: {
        cursor: "pointer",
        itemclick: toggleDataSeries
    },
    data: [{
        type: "column",
        name: "Approved",
        showInLegend: true,
        color: "green",
        dataPoints: dataPoints.filter(point => point.legendText === "Approved")
    },
    {
        type: "column",
        name: "Pending",
        showInLegend: true,
        color: "blue",
        dataPoints: dataPoints.filter(point => point.legendText === "Pending")
    },
    {
        type: "column",
        name: "Rejected",
        showInLegend: true,
        color: "red",
        dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
    }]
});

chart.render();

function toggleDataSeries(e) {
    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
        e.dataSeries.visible = false;
    } else {
        e.dataSeries.visible = true;
    }
    e.chart.render();
}
    
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


croploader();
}, 1000); 
//------------------------------------Crop Data Chart ----------------------------------------//
function croploader(){
    $('#croploader').removeClass('d-none');
    
    $.ajax({
       type:'get',
       url: "<?php echo e(url('admin/crop/data/count')); ?>",
       dataType: 'Json',
       success: function(data) {

        // console.log(data);
        var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

        $('#croploader').addClass('d-none');
         polygonmapping();

        // Loop through each state and construct data points for approved and rejected counts
            for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

       var chart = new CanvasJS.Chart("cropdatachart", {
           exportEnabled: true,
           animationEnabled: true,
           title:{
               text: "Crop Data",
                fontFamily: "Arial", 
                fontSize: 20 
           },
        //    subtitles: [{
        //        text: "Pending Approved and Rejected State Wise."
        //    }], 
        //    axisX: {
        //        title: "States"
        //    },
           toolTip: {
               shared: true
           },
           legend: {
               cursor: "pointer",
               itemclick: toggleDataSeries
           },
           data: [{
               type: "column",
               name: "Approved",
               showInLegend: true,      
               yValueFormatString: "#,##0.# Data",
               color: "green",
            //    dataPoints: [
            //        { label: "Assam",  y: data.cropassamapprovedcount },
            //        { label: "West Bengal", y: data.cropwestbengalpprovedcount },
            //        { label: "Telangana", y: data.croptelanganapprovedcount },
            //        { label: "Other", y: data.cropotherpprovedcount },
            //    ]
            dataPoints: dataPoints.filter(point => point.legendText === "Approved")

           },
           {
               type: "column",
               name: "Pending",
               axisYType: "secondary",
               showInLegend: true,
               yValueFormatString: "#,##0.# Data",
               color: "Blue",
            //    dataPoints: [
            //        { label: "Assam", y: data.cropassampendingcount },
            //        { label: "West Bengal", y: data.cropwestbengalpendingcount },
            //        { label: "Telangana", y: data.croptelanganapendingcount },
            //        { label: "Other", y: data.cropotherpendingcount },
            //    ]
            dataPoints: dataPoints.filter(point => point.legendText === "Pending")
           },
           {
               type: "column",
               name: "Rejected",
               axisYType: "secondary",
               showInLegend: true,
               yValueFormatString: "#,##0.# Data",
               color: "Red",
            //    dataPoints: [
            //        { label: "Assam", y: data.cropassamrejectedcount},
            //        { label: "West Bengal", y: data.cropwestbengalrejectedcount },
            //        { label: "Telangana", y: data.croptelaganarejectedcount },
            //        { label: "Other", y: data.cropotherrejectedcount },
            //    ]
            dataPoints: dataPoints.filter(point => point.legendText === "Rejected")

           }]
       });
       chart.render();

       function toggleDataSeries(e) {
           if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
               e.dataSeries.visible = false;
           } else {
               e.dataSeries.visible = true;
           }
           e.chart.render();
       }
       },
       error: function (jqXHR, textStatus, errorThrown) {
          
       }
   });

}
    

    //------------------------------------Polygon Mapping Chart ----------------------------------------//
    function polygonmapping(){
$('#polygonloader').removeClass('d-none');
$.ajax({
    type:'get',
    url: "<?php echo e(url('admin/polygon/data/count')); ?>",
    dataType: 'Json',
    success: function(data) {

          // console.log(data);
        var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];
        
        $('#polygonloader').addClass('d-none');
        pipeinstallation();
         // Loop through each state and construct data points for approved and rejected counts
         for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

    var chart = new CanvasJS.Chart("polygondatachart", {
        exportEnabled: true,
        animationEnabled: true,
        title:{
            text: "Polygon Mapping",
            fontFamily: "Arial", 
        fontSize: 20 
        },
        // subtitles: [{
        //     text: "Pending And Approved State Wise."
        // }], 
        // axisX: {
        //     title: "States"
        // },
        toolTip: {
            shared: true
        },
        legend: {
            cursor: "pointer",
            itemclick: toggleDataSeries
        },
        data: [{
            type: "column",
            name: "Approved",
            showInLegend: true,      
            yValueFormatString: "#,##0.# Data",
            color: "green",
            // dataPoints: [
            //     { label: "Assam",  y: data.polygon_assam_approve_count },
            //     { label: "West Bengal", y: data.polygon_westbengal_approve_count },
            //     { label: "Telangana", y: data.polygon_telangana_approve_count },
            //     { label: "Other", y: data.polygon_other_approve_count },
            // ]
            dataPoints: dataPoints.filter(point => point.legendText === "Approved")
        },
        {
            type: "column",
            name: "Pending",
            axisYType: "secondary",
            showInLegend: true,
            yValueFormatString: "#,##0.# Data",
            color: "Blue",
            // dataPoints: [
            //     { label: "Assam", y: data.polygon_assam_pending_count },
            //     { label: "West Bengal", y: data.polygon_westbengal_pending_count },
            //     { label: "Telangana", y: data.polygon_telangana_pending_count },
            //     { label: "Other", y: data.polygon_other_pending_count },
            // ]
            dataPoints: dataPoints.filter(point => point.legendText === "Pending")
        },
        {
            type: "column",
            name: "Rejected",
            axisYType: "secondary",
            showInLegend: true,
            yValueFormatString: "#,##0.# Data",
            color: "Red",
            // dataPoints: [
            //     { label: "Assam", y: data.polygon_assam_rejected_count},
            //     { label: "West Bengal", y: data.polygon_westbengal_rejected_count },
            //     { label: "Telangana", y: data.polygon_telangana_rejected_count },
            //     { label: "Other", y: data.polygon_other_rejected_count },
            // ]
            dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
        }]
    });
    chart.render();

    function toggleDataSeries(e) {
        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        } else {
            e.dataSeries.visible = true;
        }
        e.chart.render();
    }
    },
    error: function (jqXHR, textStatus, errorThrown) {
       
    }
});

    }


//------------------------------------Pipe Installation Chart ----------------------------------------//
function pipeinstallation(){
    $('#pipeloader').removeClass('d-none');

$.ajax({
type:'get',
url: "<?php echo e(url('admin/pipeinstallation/data/count')); ?>",
dataType: 'Json',
success: function(data) {
       // console.log(data);
       var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

    $('#pipeloader').addClass('d-none');
    areation();

     // Loop through each state and construct data points for approved and rejected counts
     for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

var chart = new CanvasJS.Chart("pipeinstallationchart", {
    exportEnabled: true,
    animationEnabled: true,
    title:{
        text: "Pipe Installation",
        fontFamily: "Arial", 
        fontSize: 20 
    },
    // subtitles: [{
    //     text: "Approved Pending And Rejected State Wise."
    // }], 
    // axisX: {
    //     title: "States"
    // },
    toolTip: {
        shared: true
    },
    legend: {
        cursor: "pointer",
        itemclick: toggleDataSeries
    },
    data: [{
        type: "column",
        name: "Approved",
        showInLegend: true,      
        yValueFormatString: "#,##0.# Data",
        color: "green",
        // dataPoints: [
        //     { label: "Assam",  y: data.pipe_assam_approve_count },
        //     { label: "West Bengal", y: data.pipe_westbengal_approve_count },
        //     { label: "Telangana", y: data.pipe_telangana_approve_count },
        //     { label: "Other", y: data.pipe_other_approve_count },
        // ]
        dataPoints: dataPoints.filter(point => point.legendText === "Approved")
    },
    {
        type: "column",
        name: "Pending",
        axisYType: "secondary",
        showInLegend: true,
        yValueFormatString: "#,##0.# Data",
        color: "Blue",
        // dataPoints: [
        //     { label: "Assam", y: data.pipe_assam_pending_count },
        //     { label: "West Bengal", y: data.pipe_westbengal_pending_count },
        //     { label: "Telangana", y: data.pipe_telangana_pending_count },
        //     { label: "Other", y: data.pipe_other_pending_count },
        // ]
        dataPoints: dataPoints.filter(point => point.legendText === "Pending")
    },
    {
        type: "column",
        name: "Rejected",
        axisYType: "secondary",
        showInLegend: true,
        yValueFormatString: "#,##0.# Data",
        color: "Red",
        // dataPoints: [
        //     { label: "Assam", y: data.pipe_assam_rejected_count},
        //     { label: "West Bengal", y: data.pipe_westbengal_rejected_count },
        //     { label: "Telangana", y: data.pipe_telangana_rejected_count },
        //     { label: "Other", y: data.pipe_other_rejected_count },
        // ]
        dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
    }]
});
chart.render();

function toggleDataSeries(e) {
    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
        e.dataSeries.visible = false;
    } else {
        e.dataSeries.visible = true;
    }
    e.chart.render();
}
},
error: function (jqXHR, textStatus, errorThrown) {
   
}
});
}
   

//------------------------------------Aeration Chart ----------------------------------------//
function areation(){
    $('#aerationloader').removeClass('d-none');
       $.ajax({
        type:'get',
        url: "<?php echo e(url('admin/aeration/data/count')); ?>",
        dataType: 'Json',
        success: function(data) {

        var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

            $('#aerationloader').addClass('d-none');
            areation2();

            // Loop through each state and construct data points for approved and rejected counts
     for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

        var chart = new CanvasJS.Chart("aerationchart", {
            exportEnabled: true,
            animationEnabled: true,
            title:{
                text: "Aeration 1 Event",
                fontFamily: "Arial", 
                fontSize: 20 
            },
            // subtitles: [{
            //     text: "Approved Pending And Rejected State Wise."
            // }], 
            // axisX: {
            //     title: "States"
            // },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [{
                type: "column",
                name: "Approved",
                showInLegend: true,      
                yValueFormatString: "#,##0.# Data",
                color: "green",
                // dataPoints: [
                //     { label: "Assam",  y: data.areation1_assam_approve_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_approve_count },
                //     { label: "Telangana", y: data.areation1_telangana_approve_count },
                //     { label: "Other", y: data.areation1_other_approve_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Approved")
            },
            {
                type: "column",
                name: "Pending",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Blue",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_pending_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_pending_count },
                //     { label: "Telangana", y: data.areation1_telangana_pending_count },
                //     { label: "Other", y: data.areation1_other_pending_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Pending")
            },
            {
                type: "column",
                name: "Rejected",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Red",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_rejected_count},
                //     { label: "West Bengal", y: data.areation1_westbengal_rejected_count },
                //     { label: "Telangana", y: data.areation1_telangana_rejected_count },
                //     { label: "Other", y: data.areation1_other_rejected_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
            }]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }
        },
        error: function (jqXHR, textStatus, errorThrown) {
           
        }
    });

}

//------------------------------------Aeration 2 Chart ----------------------------------------//
 function areation2(){
    $('#aeration2loader').removeClass('d-none');
    $.ajax({
        type:'get',
        url: "<?php echo e(url('admin/aeration2/data/count')); ?>",
        dataType: 'Json',
        success: function(data) {

            var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

            $('#aeration2loader').addClass('d-none');

            // Loop through each state and construct data points for approved and rejected counts
     for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

        var chart = new CanvasJS.Chart("aeration2chart", {
            exportEnabled: true,
            animationEnabled: true,
            title:{
                text: "Aeration 2 Event",
                fontFamily: "Arial", 
                 fontSize: 20 
            },
            // subtitles: [{
            //     text: "Approved Pending And Rejected State Wise."
            // }], 
            // axisX: {
            //     title: "States"
            // },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [{
                type: "column",
                name: "Approved",
                showInLegend: true,      
                yValueFormatString: "#,##0.# Data",
                color: "green",
                // dataPoints: [
                //     { label: "Assam",  y: data.areation2_assam_approve_count },
                //     { label: "West Bengal", y: data.areation2_westbengal_approve_count },
                //     { label: "Telangana", y: data.areation2_telangana_approve_count },
                //     { label: "Other", y: data.areation2_other_approve_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Approved")
            },
            {
                type: "column",
                name: "Pending",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Blue",
                // dataPoints: [
                //     { label: "Assam", y: data.areation2_assam_pending_count },
                //     { label: "West Bengal", y: data.areation2_westbengal_pending_count },
                //     { label: "Telangana", y: data.areation2_telangana_pending_count },
                //     { label: "Other", y: data.areation2_other_pending_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Pending")
            },
            {
                type: "column",
                name: "Rejected",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Red",
                // dataPoints: [
                //     { label: "Assam", y: data.areation2_assam_rejected_count},
                //     { label: "West Bengal", y: data.areation2_westbengal_rejected_count },
                //     { label: "Telangana", y: data.areation2_telangana_rejected_count },
                //     { label: "Other", y: data.areation2_other_rejected_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
            }]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }
        },
        error: function (jqXHR, textStatus, errorThrown) {
           
        }
    });
 }
 


//------------------------------------Aeration 3 Chart ----------------------------------------//
function areation3(){
    $('#aeration3loader').removeClass('d-none');
       $.ajax({
        type:'get',
        url: "<?php echo e(url('admin/aeration3/data/count')); ?>",
        dataType: 'Json',
        success: function(data) {

        var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

            $('#aeration3loader').addClass('d-none');
            areation2();

            // Loop through each state and construct data points for approved and rejected counts
     for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

        var chart = new CanvasJS.Chart("aeration3chart", {
            exportEnabled: true,
            animationEnabled: true,
            title:{
                text: "Aeration 3 Event",
                fontFamily: "Arial", 
                fontSize: 20 
            },
            // subtitles: [{
            //     text: "Approved Pending And Rejected State Wise."
            // }], 
            // axisX: {
            //     title: "States"
            // },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [{
                type: "column",
                name: "Approved",
                showInLegend: true,      
                yValueFormatString: "#,##0.# Data",
                color: "green",
                // dataPoints: [
                //     { label: "Assam",  y: data.areation1_assam_approve_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_approve_count },
                //     { label: "Telangana", y: data.areation1_telangana_approve_count },
                //     { label: "Other", y: data.areation1_other_approve_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Approved")
            },
            {
                type: "column",
                name: "Pending",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Blue",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_pending_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_pending_count },
                //     { label: "Telangana", y: data.areation1_telangana_pending_count },
                //     { label: "Other", y: data.areation1_other_pending_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Pending")
            },
            {
                type: "column",
                name: "Rejected",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Red",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_rejected_count},
                //     { label: "West Bengal", y: data.areation1_westbengal_rejected_count },
                //     { label: "Telangana", y: data.areation1_telangana_rejected_count },
                //     { label: "Other", y: data.areation1_other_rejected_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
            }]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }
        },
        error: function (jqXHR, textStatus, errorThrown) {
           
        }
    });

}




//------------------------------------Aeration 4 Chart ----------------------------------------//
function areation4(){
    $('#aeration4loader').removeClass('d-none');
       $.ajax({
        type:'get',
        url: "<?php echo e(url('admin/aeration4/data/count')); ?>",
        dataType: 'Json',
        success: function(data) {

        var states = data.states;
        var approveCounts = data.approveCounts;
        var rejectCounts = data.rejectCounts;
        var pendingCounts = data.pendingCounts;
        // console.log("crop" , states , approveCounts) ;

        var dataPoints = [];

            $('#aeration4loader').addClass('d-none');
            areation2();

            // Loop through each state and construct data points for approved and rejected counts
     for (var i = 0; i < states.length; i++) {
                
                var state = states[i];
                var stateId = state.id;
                var stateName = state.name;
                var approved = approveCounts[stateId] || 0; 
                var rejected = rejectCounts[stateId] || 0; 
                var pending = pendingCounts[stateId] || 0 ;


                dataPoints.push({
                    label: stateName, 
                    y: approved,
                    legendText: "Approved",
                    color: "green"
                }, {
                    label: stateName ,
                    y: rejected,
                    legendText: "Rejected",
                    color: "red"
                },
                {
                    label: stateName ,
                    y: pending,
                    legendText: "Pending",
                    color: "blue"
                });
            }

        var chart = new CanvasJS.Chart("aeration4chart", {
            exportEnabled: true,
            animationEnabled: true,
            title:{
                text: "Aeration 1 Event",
                fontFamily: "Arial", 
                fontSize: 20 
            },
            // subtitles: [{
            //     text: "Approved Pending And Rejected State Wise."
            // }], 
            // axisX: {
            //     title: "States"
            // },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [{
                type: "column",
                name: "Approved",
                showInLegend: true,      
                yValueFormatString: "#,##0.# Data",
                color: "green",
                // dataPoints: [
                //     { label: "Assam",  y: data.areation1_assam_approve_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_approve_count },
                //     { label: "Telangana", y: data.areation1_telangana_approve_count },
                //     { label: "Other", y: data.areation1_other_approve_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Approved")
            },
            {
                type: "column",
                name: "Pending",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Blue",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_pending_count },
                //     { label: "West Bengal", y: data.areation1_westbengal_pending_count },
                //     { label: "Telangana", y: data.areation1_telangana_pending_count },
                //     { label: "Other", y: data.areation1_other_pending_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Pending")
            },
            {
                type: "column",
                name: "Rejected",
                axisYType: "secondary",
                showInLegend: true,
                yValueFormatString: "#,##0.# Data",
                color: "Red",
                // dataPoints: [
                //     { label: "Assam", y: data.areation1_assam_rejected_count},
                //     { label: "West Bengal", y: data.areation1_westbengal_rejected_count },
                //     { label: "Telangana", y: data.areation1_telangana_rejected_count },
                //     { label: "Other", y: data.areation1_other_rejected_count },
                // ]
                dataPoints: dataPoints.filter(point => point.legendText === "Rejected")
            }]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }
        },
        error: function (jqXHR, textStatus, errorThrown) {
           
        }
    });

}
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
    $('#farmerloaderfilter').removeClass('d-none');
    $.ajax({
        type: 'post',
        url: "<?php echo e(route('fetch.filter.graph')); ?>",
        dataType: 'Json',
        data: { _token: '<?php echo e(csrf_token()); ?>', 'forr': forr, 'to_date': to_date, 'from_date': from_date, 'state_id': stateid },
        success: function (data) {
    $('#farmerloaderfilter').addClass('d-none');
    var farmerfilter = new CanvasJS.Chart("farmeronboardingfilter", {
	exportEnabled: true,
	animationEnabled: true,
	title:{
		text: data.forr,
        fontFamily: "Arial", 
        fontSize: 20 
	},
	// subtitles: [{
	// 	// text: "Pending And Approved."
	// }], 
	// axisX: {
	// 	title: "State"
	// },
	toolTip: {
		shared: true
	},
	legend: {
		cursor: "pointer",
		itemclick: toggleDataSeries
	},
	data: [{
		type: "column",
		name: "Approved",
		showInLegend: true,      
		yValueFormatString: "#,##0.# Data",
        color: "green",
		dataPoints: [
			{ label: data.statename,  y: data.approved },
		]
	},
    {
		type: "column",
		name: "Pending",
		showInLegend: true,      
		yValueFormatString: "#,##0.# Data",
        color: "Blue",
		dataPoints: [
			{ label: data.statename,  y:  data.pending },
		]
	},
    {
		type: "column",
		name: "Rejected",
		showInLegend: true,      
		yValueFormatString: "#,##0.# Data",
        color: "Red",
		dataPoints: [
			{ label: data.statename,  y:  data.rejected },
		]
	}]
});
        farmerfilter.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.farmerfilter.render();
        }
        },
        error: function (xhr, status, error) {

        }
    });  


    $('#organizationdrop').removeClass('d-none');
    $.ajax({
    type: 'post',
    url: "<?php echo e(route('organization.fetch.filter.graph')); ?>",
    dataType: 'json',
    data: {
        _token: '<?php echo e(csrf_token()); ?>',
        'forr': forr,
        'to_date': to_date,
        'from_date': from_date,
        'state_id': stateid
    },
    success: function (response) {
        
        if (response.success) {
            $('#organizationdrop').addClass('d-none');
            var organizationData = response.organizationData;
            console.log(organizationData,organizationId);

            var dataPoints = [];
            for (var organizationId in organizationData) {
                var organizationCounts = organizationData[organizationId];
                var organizationName = organizationCounts['name'];
                var approvedCount = organizationCounts['Approved'] || 0;
                var pendingCount = organizationCounts['Pending'] || 0;
                var rejectedCount = organizationCounts['Rejected'] || 0;

                dataPoints.push({
                    label:organizationName,
                    approved: approvedCount,
                    pending: pendingCount,
                    rejected: rejectedCount
                });
            }

            var organizationfilter = new CanvasJS.Chart("organizationreport", {
                exportEnabled: true,
                animationEnabled: true,
                title: {
                    text: "Organization Report",
                    fontFamily: "Arial", 
                    fontSize: 20 ,
                },
                // subtitles: [{
                //     text: "Pending And Approved State Wise."
                // }],
                // axisX: {
                //     title: "Organizations"
                // },
                // axisY: {
                //     title: "Count"
                // },
                toolTip: {
                    shared: true
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries
                },
                data: [{
                    type: "stackedColumn",
                    name: "Approved",
                    showInLegend: true,
                    color: "green",
                    dataPoints: dataPoints.map(function(organization) {
                        return { label: organization.label, y: organization.approved };
                    })
                }, {
                    type: "stackedColumn",
                    name: "Pending",
                    showInLegend: true,
                    color: "yellow",
                    dataPoints: dataPoints.map(function(organization) {
                        return { label: organization.label, y: organization.pending };
                    })
                }, {
                    type: "stackedColumn",
                    name: "Rejected",
                    showInLegend: true,
                    color: "red",
                    dataPoints: dataPoints.map(function(organization) {
                        return { label: organization.label, y: organization.rejected };
                    })
                }]
            });

            organizationfilter.render();
            function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            e.organizationfilter.render();
        }
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
    url: "<?php echo e(route('district.fetch.filter.graph')); ?>",
    dataType: 'json',
    data: {
        _token: '<?php echo e(csrf_token()); ?>',
        'forr': forr,
        'to_date': to_date,
        'from_date': from_date,
        'state_id': stateid
    },
    success: function (response) {
        if (response.success) {
            $('#districtloader').addClass('d-none');
            var districtData = response.district_data;

            var chartData = [];
            var stateName = response.state_name; 

            for (var districtName in districtData) {
                var district = districtData[districtName];
                chartData.push({
                    label: districtName,
                    approved: district['Approved'] || 0,
                    pending: district['Pending'] || 0,
                    rejected: district['Rejected'] || 0
                });
            }

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: "District Report",
                    fontFamily: "Arial", 
                    fontSize: 20,
                },
                axisY: {
                    title: stateName,
                    includeZero: true
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries
                },
                toolTip: {
                    shared: true,
                    content: toolTipFormatter
                },
                data: [{
                        type: "bar",
                        showInLegend: true,
                        name: "Approved",
                        color: "green",
                        dataPoints: chartData.map(function (item) {
                            return {
                                y: item.approved,
                                label: item.label
                            };
                        })
                    },
                    {
                        type: "bar",
                        showInLegend: true,
                        name: "Pending",
                        color: "blue",
                        dataPoints: chartData.map(function (item) {
                            return {
                                y: item.pending,
                                label: item.label
                            };
                        })
                    },
                    {
                        type: "bar",
                        showInLegend: true,
                        name: "Rejected",
                        color: "red",
                        dataPoints: chartData.map(function (item) {
                            return {
                                y: item.rejected,
                                label: item.label
                            };
                        })
                    }
                ]
            });
            chart.render();

            function toolTipFormatter(e) {
                var str = "";
                var total = 0;
                var str3;
                var str2;
                for (var i = 0; i < e.entries.length; i++) {
                    var str1 = "<span style= \"color:" + e.entries[i].dataSeries.color + "\">" + e.entries[i].dataSeries.name + "</span>: <strong>" + e.entries[i].dataPoint.y + "</strong> <br/>";
                    total = e.entries[i].dataPoint.y + total;
                    str = str.concat(str1);
                }
                str2 = "<strong>" + e.entries[0].dataPoint.label + "</strong> <br/>";
                str3 = "<span style = \"color:Tomato\">Total: </span><strong>" + total + "</strong><br/>";
                return (str2.concat(str)).concat(str3);
            }

            function toggleDataSeries(e) {
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                chart.render();
            }
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
    url: "<?php echo e(route('taluka.fetch.filter.graph')); ?>",
    dataType: 'json',
    data: {
        _token: '<?php echo e(csrf_token()); ?>',
        'forr': forr,
        'to_date': to_date,
        'from_date': from_date,
        'state_id': stateid
    },
    success: function (response) {
        if (response.success) {
            $('#talukaloader').addClass('d-none');
            var talukaData = response.taluka_data;
            console.log(talukaData);

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

        //     var talukafilter = new CanvasJS.Chart("talukafilter", {
        //     exportEnabled: true,
        //     animationEnabled: true,
        //     title:{
        //         text: "Taluka Report"
        //     },
        //     subtitles: [{
        //         text: "Pending And Approved State Wise."
        //     }], 
        //     axisX: {
        //         title: "Taluka" // Adjust the title to represent Taluka
        //     },
        //     toolTip: {
        //         shared: true
        //     },
        //     legend: {
        //         cursor: "pointer",
        //         itemclick: toggleDataSeries
        //     },
        //     data: [{
        //         type: "column",
        //         name: "Approved",
        //         showInLegend: true,      
        //         yValueFormatString: "#,##0.# Data",
        //         color: "green",
        //         dataPoints: chartData.map(function (item) {
        //             return {
        //                 y: item.approved,
        //                 label: item.label
        //             };
        //         })
        //     },
        //     {
        //         type: "column",
        //         name: "Pending",
        //         showInLegend: true,      
        //         yValueFormatString: "#,##0.# Data",
        //         color: "blue",
        //         dataPoints: chartData.map(function (item) {
        //             return {
        //                 y: item.pending,
        //                 label: item.label
        //             };
        //         })
        //     },
        //     {
        //         type: "column",
        //         name: "Rejected",
        //         showInLegend: true,      
        //         yValueFormatString: "#,##0.# Data",
        //         color: "red",
        //         dataPoints: chartData.map(function (item) {
        //             return {
        //                 y: item.rejected,
        //                 label: item.label
        //             };
        //         })
        //     }]
        // });
        // talukafilter.render();

    var talukafilter = new CanvasJS.Chart("talukafilter", {
    exportEnabled: true,
    animationEnabled: true,
    dataPointMaxWidth: 30,
    // width:320,
    title:{
        text: "Taluka/Block Report",
        fontFamily: "Arial", 
        fontSize: 20 ,
    },
    // subtitles: [{
    //     text: "Pending And Approved State Wise."
    // }], 
    axisX: {
        title: stateName,
        labelAngle: -45, // Rotate the X-axis labels for better visibility
        interval: 1 // Show every label
    },
    toolTip: {
        shared: true
    },
    legend: {
        cursor: "pointer",
        itemclick: toggleDataSeries
    },
    data: [{
        type: "column",
        name: "Approved",
        showInLegend: true,      
        yValueFormatString: "#,##0.# Data",
        color: "green",
        dataPoints: chartData.map(function (item) {
            return {
                y: item.approved,
                label: item.label
            };
        })
    },
    {
        type: "column",
        name: "Pending",
        showInLegend: true,      
        yValueFormatString: "#,##0.# Data",
        color: "blue",
        dataPoints: chartData.map(function (item) {
            return {
                y: item.pending,
                label: item.label
            };
        })
    },
    {
        type: "column",
        name: "Rejected",
        showInLegend: true,      
        yValueFormatString: "#,##0.# Data",
        color: "red",
        dataPoints: chartData.map(function (item) {
            return {
                y: item.rejected,
                label: item.label
            };
        })
    }]
});
talukafilter.options.axisX.zoomEnabled = true;
talukafilter.options.axisX.zoomType = "xy";

talukafilter.render();

function toggleDataSeries(e) {
    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
        e.dataSeries.visible = false;
    } else {
        e.dataSeries.visible = true;
    }
    e.talukafilter.render();
}
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\erda-illumine\resources\views/dashboard/index.blade.php ENDPATH**/ ?>