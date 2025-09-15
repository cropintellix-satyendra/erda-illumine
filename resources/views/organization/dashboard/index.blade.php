{{-- Extends layout --}}
@extends('organization.layout.default')
{{-- Content --}}
@section('content')
<style>
</style>
            <!-- row -->
	<div class="container-fluid">
		<!-- row -->
                <div id="countBoxRow" class="row  ">

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


                    <div class=" bg-danger countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center t-font text-white" id="Benefit">0</h2>
                                <h6 class="text-uppercase-mb-3 t-font font-size-16 text-white text-center">Farmer
                                    Benefits</h6>
                            </div>
                        </div>
                    </div>



                    <div class=" bg-success cc countBox">
                        <div class="card-body mini-stat-img">
                            <div class="text-white">
                                <h2 class="mb-4s text-center tt-font text-white" id="TotalArea">0</h2>
                                <h6 class="text-uppercase-mb-3 tt-font text-white text-center">
                                    Total Area in Acres<span class="a-font">(Approved)</span></h6>
                            </div>
                        </div>
                    </div>
                </div>




                <div class="col-xl-12 col-xxl-12">
					<div class="row">
						<div class="col-xl-12 col-xxl-12 col-lg-12 col-md-12">
							<div class="card">
								<div class="card-header border-0 pb-0">
									<h4 class="card-title">Farmers</h4>
									<div class="dropdown ml-auto">
										<div class="btn-link" data-toggle="dropdown">
											<!-- <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg> -->
										</div>
									</div>
								</div>
								<!-- header -->
								<div class="card-body">
									<div id="map" style="width: 100%; height:700px;"></div>
								</div><!-- cardbody end  -->
								<div class="card-footer border-0 pt-0 text-center">
									<!-- <a href="#" class="btn-link">See More >></a> -->
								</div>
							</div>
						</div>
					</div>
				</div>
		 </div>
@endsection
@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{config('map.map_key')}}&libraries=geometry,places&amp;ext=.js"></script>

<script type="text/javascript" src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

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
            document.getElementById("Pipe").innerHTML = data.pipeinstall;
            document.getElementById("awd").innerHTML = data.awd;
            document.getElementById("Benefit").innerHTML = data.benefit;
            document.getElementById("TotalArea").innerHTML = data.totalarea;
        },
        error: function (jqXHR, textStatus, errorThrown) {

        }
    });
});
function initMap() {
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 5,
        center: { lat: 19.197325380448103, lng: 72.87174577008318 },
        mapTypeId: "hybrid",
				scrollwheel: true,
      });
      // Create an array of alphabetical characters used to label the markers.
      const labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      const markers = locations.map((location, i) => {
        return new google.maps.Marker({
          position: location,
          //label: labels[i % labels.length],
        });
      });
      // Add a marker clusterer to manage the markers.
      new MarkerClusterer(map, markers, {
        imagePath:"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
      });
}
const locations = [
  @if($FarmersLocation->count()>0)
      @foreach($FarmersLocation as $latlng)
      { lat: {!! $latlng['latitude'] !!}, lng: {!! $latlng['longitude'] !!} },
      @endforeach
  @endif
];
initMap();
</script>
@stop
