@extends('layout.default')
@section('content')
<!--Import PhotoSwipe Styles -->
<!-- Import PhotoSwipe Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/default-skin/default-skin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" integrity="sha512-+EoPw+Fiwh6eSeRK7zwIKG2MA8i3rV/DGa3tdttQGgWyatG/SkncT53KHQaS5Jh9MNOT3dmFL0FjTY08And/Cw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-typeahead/2.11.2/jquery.typeahead.min.css" integrity="sha512-UKvJ8GWN7HSI41K3GUfcJInghVOhKi/w0pLNV/5lYluLW1IZPuXu0ANCFibdfp5SAY2CL0cZt6uYos8YqvV1/w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Core CSS file -->
<link href="{{asset('vendor/photoviewer/dist/photoviewer.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet-editable/1.5.4/Leaflet.Editable.css" /> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">

<style>

        #map {
            height: 400px;
            width: 100%;
        }
    </style>

<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-md-12 p-md-0">
            <div class="welcome-text">
                <h4>Farmer Plot Details</h4>
            </div>
        </div>

        <form id="map-form" action="">
            <div class="row">
                <div class="col-4">
                    <label for="">Enter Lattitude</label>
                    <input  class="form-control" id="lat" type="text" name="lat" value="">
                </div>
                <div class="col-4">
                    <label for="">Enter Longitutde</label>
                    <input class="form-control"  id="lng" type="text" name="lng" value="">
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-primary" id="search-button" style="margin-top:30px">Search</button>
                </div>  
            </div>
            &nbsp;
        </form>

            <div id="map">
            </div>
            <div id="polygon_details">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                          <h5 class="card-title">Polygon Details</h5>
                          <h6 class="card-subtitle mb-2 text-muted">gid : </h6>
                          <h6 class="card-subtitle mb-2 text-muted">fid : </h6>
                          <p class="card-text"><strong>Polygon cordinates :</strong> </p>
                        </div>
                      </div>
            </div>
            {{-- <script src="your-script.js"></script> --}}
    </div>
</div>
<!-- reject module end here -->

@stop
@section('scripts')
{{-- <script type="text/javascript" src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script src="{{asset('js/yepnope.min.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{config('map.map_key')}}&libraries=geometry,places&amp;ext=.js"></script> --}}
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-editable/1.5.4/Leaflet.Editable.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
    $(document).ready(function() {
    // var center = [7.2906, 80.6337];
    // var map = L.map('map').setView(center, 10);

    // L.tileLayer(
    //     'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //         attribution: 'Data © <a href="http://osm.org/copyright">OpenStreetMap</a>',
    //         maxZoom: 18
    //     }).addTo(map);
    
     var map = L.map('map').setView([-41.2858, 174.78682], 14);

        var mapLink = '<a href="http://www.esri.com/">Esri</a>';
        var wholink = 'i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';

        L.tileLayer(
            'http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; '+mapLink+', '+wholink,
            maxZoom: 18,
            }).addTo(map);


    // Initialize the Leaflet.Draw control
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Create a custom layer for displaying vertices
    var verticesLayer = new L.LayerGroup().addTo(map);

    var drawControl = new L.Control.Draw({
        position: 'topright',
        edit: {
            featureGroup: drawnItems
        },
        draw: {
            polygon: {
                shapeOptions: {
                    color: 'purple' //polygons being drawn will be purple color
                },
                allowIntersection: false,
                drawError: {
                    color: 'orange',
                    timeout: 1000
                },
                showArea: true, //the area of the polygon will be displayed as it is drawn.
                metric: false,
                repeatMode: true
            },
            polyline: {
                shapeOptions: {
                    color: 'red'
                },
            },
            circlemarker: false, //circlemarker type has been disabled.
            rect: {
                shapeOptions: {
                    color: 'green'
                },
            },
            circle: false,
        }
    });
    map.addControl(drawControl);
    var polygon;

    map.on('draw:created', function (e) {
                var type = e.layerType,
                    layer = e.layer;

                drawnItems.addLayer(layer);

                // Get the polygon's vertices
                var vertices = layer.getLatLngs();

                // Clear the vertices layer
                verticesLayer.clearLayers();

                // Add markers for each vertex
                vertices.forEach(function (vertex) {
                    L.circleMarker(vertex, {
                        color: 'red',
                        radius: 5,
                        fillOpacity: 1
                    }).addTo(verticesLayer);
                });

                // Update the polygon variable
                polygon = layer;
            });

    // Event listener for when a polygon is edited
    map.on('draw:edited', function (e) {
    var layers = e.layers;
    layers.eachLayer(function (layer) {
            if (layer instanceof L.Polygon) { // Check if it's a polygon layer
                // Access the edited polygon's coordinates here
                var polygonCoordinates = layer.getLatLngs();
                console.log('Edited Polygon Coordinates:', polygonCoordinates);
            }
        });
    });


    var searchButton = $("#search-button");
    searchButton.click(function() {
        // Get user input for latitude and longitude
        var lat = $("#lat").val();
        console.log(lat);
        var lng = $("#lng").val();
        // Validate user input (you can add more validation as needed)
        if (lat && lng) {
            // Create a LatLng object from user input
            var userLatLng = L.latLng(lat, lng);

            // Set the map view to the user's input coordinates
            map.setView(userLatLng, 13); // Use a zoom level of your choice

            // Perform your AJAX request and plot the polygon as before
            $.ajax({
                type: "post",
                url: "{{ url('api/V1/check/polyon/short_by')}}",
                data: {
                    _method: 'post',
                    _token: '{!! csrf_token() !!}',
                    lat: lat,
                    lng: lng
                },
                success: function (data) {
                   
                    // console.log(data);
                    // Clear any existing polygons on the map
                    map.eachLayer(function (layer) {
                        if (layer instanceof L.Polygon) {
                            map.removeLayer(layer);
                        }
                    }); 

                    // Define a reference to the polygon details container
                                var polygonDetailsContainer = document.getElementById('polygon_details');

                                // Function to update the polygon details
                                function updatePolygonDetails(gid, fid, coordinates) {
                                var cardTitle = polygonDetailsContainer.querySelector('.card-title');
                                var gidSubtitle = polygonDetailsContainer.querySelector('.card-subtitle:nth-child(2)');
                                var fidSubtitle = polygonDetailsContainer.querySelector('.card-subtitle:nth-child(3)');
                                var coordinatesText = polygonDetailsContainer.querySelector('.card-text');

                                cardTitle.textContent = 'Polygon Details';
                                gidSubtitle.textContent = 'gid: ' + gid;
                                fidSubtitle.textContent = 'fid: ' + fid;
                                coordinatesText.textContent = 'Polygon Coordinates: ' + coordinates;
                                }

                                // Function to hide the polygon details
                                function hidePolygonDetails() {
                                polygonDetailsContainer.style.display = 'none';
                                }

                                // Function to show the polygon details
                                function showPolygonDetails() {
                                polygonDetailsContainer.style.display = 'block';
                                }

                            

                    // Loop through the received data object properties (gid)
                    for (var key in data) {
                        if (data.hasOwnProperty(key)) {
                            var itemData = data[key];
                            // console.log("itemData:", itemData);

                            if (itemData && itemData.ranges) {
                                var polygonCoordinates = [];

                                // Loop through the ranges in each itemData
                                for (var i = 0; i < itemData.ranges.length; i++) {
                                    var lat = parseFloat(itemData.ranges[i].lat);
                                    var lng = parseFloat(itemData.ranges[i].lng);
                                    polygonCoordinates.push([lat, lng]);
                                }

                                // Create a polygon for this ID (gid) and add it to the map
                                var polygon = L.polygon(polygonCoordinates, {
                                    color: 'red',
                                    fillColor: 'green',
                                    fillOpacity: 0.3,
                                }).addTo(map);

                                // You can add additional properties or tooltips to the polygon here
                                polygon.bindPopup('gid:' + itemData.gid);

                                polygon.on('click', createPolygonClickHandler(itemData));
                            }
                        }
                    }

                    // Function to create a click handler for the polygon
                        function createPolygonClickHandler(itemData) {
                            console.log("item data",itemData);
                            return function (e) {
                                // Extract latitudes and longitudes from itemData.ranges
                                var latlngs = itemData.ranges.map(function (range) {
                                    return '(' + range.lat + ', ' + range.lng + ')';
                                });

                                // Combine latitudes and longitudes as a string
                                var latlngsStr = latlngs.join(', ');

                                // Log the latlngsStr for testing
                                console.log("Latitudes and Longitudes:", latlngsStr);
                               
                                updatePolygonDetails(itemData.gid, itemData.fid, latlngsStr);

                                // Show the polygon details
                                showPolygonDetails();
                            };
                        }

                    // Initially hide the polygon details container
                    // hidePolygonDetails();

                    var polygonLayers = [];
                    var markerLayers = [];
                    Object.values(map._layers).forEach(function (layer) {
                        if (layer instanceof L.Polygon) {
                            polygonLayers.push(layer);
                        } else if (layer instanceof L.Marker) {
                            markerLayers.push(layer);
                        }
                    });

                    // Create feature groups for polygons and markers
                    var polygonGroup = new L.featureGroup(polygonLayers);
                    var markerGroup = new L.featureGroup(markerLayers);

                    // Fit the map's bounds to both feature groups
                    map.fitBounds(polygonGroup.getBounds().extend(markerGroup.getBounds()));

                    // // Adjust the map bounds to fit all polygons
                    // var group = new L.featureGroup(Object.values(map._layers));
                    // map.fitBounds(group.getBounds());
                },


                error: function (xhr, jqXHR, status, error) {
                    var data = jqXHR.responseJSON.message;
                    alert("Error: " + data);
                }
            });
        } else {
            alert("Please enter valid latitude and longitude.");
        }
    });
});
</script>



<!-- AIzaSyAqvsVxUyfv5KJl0cDoyhEUPtGm5YcVEuM -->
{{-- <script>
$(document).ready(function() {
  // var test = $('#map-container').hasClass('mapit');
  var test = window.google != undefined;
  $('.OpenMap').click(function() {
    //   $('.OpenMap').addClass('d-none');
    $gmap = true;
    $mapit = false;
    yepnope({  
		    test : test,
		    yep: {
		    	"alreadyLoaded":"timeout=1!"
		      //   "googleMap": "https://maps.googleapis.com/maps/api/js?key="+'{{config('map.map_key')}}'+"&libraries=geometry,places&amp;ext=.js"
		      //"googleMap": "https://maps.googleapis.com/maps/api/js?key=AIzaSyAqvsVxUyfv5KJl0cDoyhEUPtGm5YcVEuM&libraries=geometry,places&amp;ext=.js"
		    },
		    nope: {
		  //  	"googleMap": "https://maps.googleapis.com/maps/api/js?key="+'{{config('map.map_key')}}'+"&libraries=geometry,places&amp;ext=.js"
		  //"googleMap": "https://maps.googleapis.com/maps/api/js?key=AIzaSyAqvsVxUyfv5KJl0cDoyhEUPtGm5YcVEuM&libraries=geometry,places&amp;ext=.js"
		    },
		    callback: {
		    	"alreadyLoaded": function() {
		    		initMap();
		    	}
		    },			
			complete : function(url, result, key){
			    
		    }
		});
	});
    
});


$(document).ready(function() {
    var polygon={!!json_encode($Polygon)!!}||[];
    console.log(polygon);
	var pipe_location={!! json_encode($PipesLocation) !!}||[];
    var updated_polygon = {!! $updated_polygon !!}||[]; 
    if(polygon.length>0){
        // console.log(polygon);
        var lat = polygon[0]['lat'];
        var lng =  polygon[0]['lng'];
        var farmer_plot_uniqueid = "{{$PipeInstallation->farmer_plot_uniqueid}}";
       
        // $.ajax({
        //     type:"post",
        //     url:"{{ url('api/V1/check/polyon/nearby')}}",
        //     data:{_method:'post',_token:'{!! csrf_token() !!}', farmer_plot_uniqueid:"{{$PipeInstallation->farmer_plot_uniqueid}}",lat:lat,lng:lng}, 
        //     success:function(data){  
        //         //now store data in local storage 
        //         localStorage.setItem("nearpolygon", "");
        //         var exp = JSON.stringify(data).split("[");
        //         localStorage.setItem("nearpolygon", JSON.stringify(data));                
        //     },
        //     error:function(xhr, jqXHR,status, error) {
        //         var data = jqXHR.responseJSON.message;            
        //     }
        // });

        $.ajax({
            type:"post",
            url:"{{ url('api/V1/check/polyon/short_by')}}",
            data:{_method:'post',_token:'{!! csrf_token() !!}',lat:lat,lng:lng}, 
            success:function(data){  
                //now store data in local storage 
                localStorage.setItem("nearpolygon", "");
                var exp = JSON.stringify(data).split("[");
                localStorage.setItem("nearpolygon", JSON.stringify(data));                
            },
            error:function(xhr, jqXHR,status, error) {
                var data = jqXHR.responseJSON.message;            
            }
        });


    }    
});


function roundToTwo(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}

function initMap() {
	var polygon={!!json_encode($Polygon)!!}||[];
	var pipe_location={!! json_encode($PipesLocation) !!}||[];

    var updated_polygon = {!! $updated_polygon !!}||[]; 
    // fetch data from localstorage for near by polygon 
    //which is set from ajax and api from above function
    var data = localStorage.getItem("nearpolygon");              
    var polygon_bynearby = JSON.parse(data);


	if(polygon.length>0){
		polygon.map(function(v,i){
			v.lat=parseFloat(v.lat);
			v.lng=parseFloat(v.lng);
			return v;
		});
	}
	const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 17,
        center: polygon[0]||{ lat: {!!$plot->latitude!!}, lng: {!! $plot->longitude!!} },
        mapTypeId: "hybrid",
        scrollwheel: true,
      });

      $(document).bind('webkitfullscreenchange mozfullscreenchange fullscreenchange', function() {
                var isFullScreen = document.fullScreen ||
                document.mozFullScreen ||
                document.webkitIsFullScreen;
                if (isFullScreen) {
                map.controls[google.maps.ControlPosition.TOP_LEFT].push($(".Map-detail").get(0));
                $(".Map-detail").attr("style", "width:auto; background:whitesmoke; position: absolute; left: 189px; top: 0px;");
            } else {
                var elem = map.controls[google.maps.ControlPosition.TOP_LEFT].pop();
                $(elem).removeAttr("style").prependTo(".Map-detail-sek");
            }
        });


 //   to display stored polygon in pipeinstallation table
      const path = new google.maps.Polygon({
            paths: polygon,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
            editable: true,
        });
        path.setMap(map);

         //this is to display old polygon
      if(updated_polygon.length > 0){//if this has updated polygon
        var color = ['#FFF2CC', '#E1679C', '#FFD700', '#4885B4', '#BD91E4'];
            if(updated_polygon.length>0){
                $.each(updated_polygon,function(i,v){
                        $.each(v,function(z,poly){
                            var poly_color = color[i];
                            var updatepaths= JSON.parse(poly);
                            updatepath=updatepaths.map(function(n,l){
                                return {lat:parseFloat(n.lat),lng:parseFloat(n.lng)};
                            });                            
                            const update_polygon = new google.maps.Polygon({
                                paths: updatepath,
                                strokeColor: "#FF0000",
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                fillColor: poly_color,//"#FF0000",
                                fillOpacity: 0.35,
                            });
                            update_polygon.setMap(map);
                        });
                });
            }        
      }

       //this will be used for near by polygon data fetch from ajax through api. the same api which also used for app to check near by polygon
       $.each(polygon_bynearby,function(i,v){
                // console.log(v);
                $.each(v,function(a,j){
                    $.each(j,function(a,z){ 
                            updatepath=z.map(function(n,l){
                                 return {lat:parseFloat(n.lat),lng:parseFloat(n.lng)};
                            });
                        const near_polygon = new google.maps.Polygon({
                            paths: z,
                            strokeColor: "#0000FF",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#0000FF",
                            fillOpacity: 0.35,
                        });
                        near_polygon.setMap(map);
                    });
                });
            });


	 //pipe marker
	if(pipe_location.length>0){
		for (var i = 0; i < pipe_location.length; i++) {
			var marker = new google.maps.Marker({
				position: { lat: parseFloat(pipe_location[i].lat), lng: parseFloat(pipe_location[i].lng) },
				map,
				title: 'Pipe No: '+pipe_location[i].pipe_no,
        icon: {
          url: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"
        }
			});
		}
	}

     /* Below code is for Updating new lat & long from a surveys*/
        //google.maps.event.addListener(polygon, "dragend", getPolygonCoords);
        google.maps.event.addListener(path.getPath(), "insert_at", getPolygonCoords);
        //google.maps.event.addListener(polygon.getPath(), "remove_at", getPolygonCoords);
        google.maps.event.addListener(path.getPath(), "set_at", getPolygonCoords);

        function getPolygonCoords(){
            var updated_poly_area = google.maps.geometry.spherical.computeArea(path.getPath().getArray());//calculate updated polygon area
            var updated_poly_area = updated_poly_area * 0.000247;//converting from sqmt to area in acers. 1 sq.mt = 0.000247 ac.
            // document.getElementById("update_plot_area").innerHTML = updated_poly_area.toFixed(2)+" Acers";//get updated polygon area
            var onboarding_area = parseFloat('{{$PipeInstallation->area_in_acers}}');
            var new_area = parseFloat(updated_poly_area.toFixed(2));
            
            var mod = Math.abs(onboarding_area  -  new_area);
            var denominator = onboarding_area//(onboarding_area + new_area)/2;
            //below percentage error between onboarding area and updated area
            var percent_error = roundToTwo(100 * mod/denominator);//need to fixed on two decimal place


            document.getElementById("update_plot_area").innerHTML = updated_poly_area.toFixed(2)+" Acers";//get updated polygon area
            document.getElementById("percent_error").innerHTML = percent_error+" %";//display percentage error

            var coordinates_poly = path.getPath().getArray();
            var newCoordinates_poly = [];
            for (var i = 0; i < coordinates_poly.length; i++){
                lat_poly = coordinates_poly[i].lat();
                lng_poly = coordinates_poly[i].lng();
                latlng_poly = [lat_poly, lng_poly];
                newCoordinates_poly.push(latlng_poly);
            }
            var str_coordinates_poly = JSON.stringify(newCoordinates_poly);            
            if (str_coordinates_poly !== null) {
                    var farmer_plot_uniqueid =    "{{$plot->farmer_plot_uniqueid}}";
                    $('.UpdtePoly').click(function(){
                    event.preventDefault();
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Update polygon it!'
                            }).then((result) => {
                            if (result.value == 1) {
                                $.ajax({
                                type:"post",
                                url:"{{ url('l1/pipe/polygon/update')}}/" + farmer_plot_uniqueid,
                                data:{_method:'post',_token:'{!! csrf_token() !!}', updatedpolygon:str_coordinates_poly, farmer_plot_uniqueid:farmer_plot_uniqueid,updated_poly_area:updated_poly_area},
                                success:function(data){                                            
                                    toastr.success("", data.message, {
                                        timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                                        progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                                        showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                                    });
                                    location.reload();
                                },
                                error:function(xhr, jqXHR,status, error) {
                                    var data = jqXHR.responseJSON.message;
                                    toastr.error("", "Something went wrong", {
                                        positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                                        debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                                        hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                                        tapToDismiss: !1
                                    })
                                }
                           });
                    		
                        }else{//if end of confirmation
                    }
                    });//swal end
                });
            }
        }//getPolygonCoords end
}

initMap();


(function(){
    'use strict';
    //search

        $('input[name="search"]').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            limit:10
        },{
            display: 'farmer_uniqueId',
            source:function (query, process) {
                return $.get("{!! url('') !!}"+'/{{$rolename}}/view/l2/pipeinstallation/search/'+'{{$PipeInstallation->l2_status}}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.l2_status
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    var rolename = '{{$rolename}}';
                    return '<div><a href="{{ url("/")}}/'+rolename+'/view/l2/pending/pipeinstallation/plot/'+data.farmer_uniqueId+'"><strong>' + data.farmer_uniqueId + '</strong> - ' + data.status + '</a></div>';
                }
            }
        });


    $('.plot-gallery a').click(function (e) {
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).index(),
                initModalPos:{right:1,top:0}
            };
        $(this).parent().find('a').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
    });
    $('.plotPipeImg .pipeImgclick').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
        $('#plotPipeImg').find('.pipeImgclick').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
    });
     $('.benefitsimg .benefitImgclick').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
        $('.benefitsimg').find('.benefitImgclick').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
      });

})($);



$("#approvepipe_no").click(function() {
    $(".SubmitApproval").prop('disabled', false);
});

$(".SubmitApproval").click(function() {

    // $('.Aspinner').removeClass('d-none');
    var pipes = [];
    $.each($("input[name='approvepipe_no']:checked"), function(){
        var ApproveComment  = $('#approve_comment'+$(this).val()).val();
        pipes.push({'pipe_no' : $(this).val(), 'pipe_id' : $('#pipe_id'+$(this).val()).val(),'ApproveComment' :ApproveComment});
    });
    Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, Approve it!'
        }).then((result) => {
          if (result.value == 1) {
                $.ajax({
                  type:'post',
                  url:"{{url('l2/pipeinstallation/status/')}}/"+'approve/{{$plot->farmer_plot_uniqueid}}',
                  data: {_token:'{{csrf_token()}}',method:'post',pipes:pipes},
                  success:function(data){
                    $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    $('.EditBtn').addClass('d-none');
                    //jQuery.noConflict(); //Furthermore, some plugins cause errors too, in this case add
                    $('#ApproveModal').modal('hide');
                    location.reload();
                    toastr.success("", data.message, {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                          progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,
                          onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                          showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                          hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                      $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    var data = jqXHR.responseJSON;
                    if(data.empty){
                        toastr.error("", data.empty, {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,
                          preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                          hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      });
                      return false;
                    }
                    toastr.error("", "Something went wrong", {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,
                          preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                          hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//   ajax end
          }else{
             $('.Aspinner').addClass('d-none');
             $(".SubmitApproval").prop('disabled', false);
          }//if end of confirmation
        })//swal end
});



$(".PipeReject").click(function() {

    var pipeno = $(this).attr("data-reject_pipe_no");
    var pipe_id = $('#pipe_id'+pipeno).val();
    var reasons = $('#reasons'+pipeno+' option:selected').val();
    var rejectcomment = $('#reject_comment'+pipeno).val();


    $(".PipeReject").prop('disablesd', true);


    $('#Rspinner'+pipeno).removeClass('d-none');


    if(!reasons.length > 0){
        $('#Rspinner'+plotno).addClass('d-none');
        $(".PipeReject").prop('disabled', false);
        return false;
    }
    if(!$('#pipeno' + pipeno).is(":checked")){

        $('#Rspinner'+pipeno).addClass('d-none');
        $(".PipeReject").prop('disabled', false);
        return false;
    }

    // console.log(pipeno, reasons, rejectcomment,'dsddfdfd');
    // return false;

    
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, Reject it!'
        }).then((result) => {
          if (result.value == 1) {

                $.ajax({
                  type:'post',
                  url:"{{url('l2/pipeinstallation/status/')}}/"+'reject'+'/'+'{{$plot->farmer_plot_uniqueid}}',
                  data: {_token:'{{csrf_token()}}',method:'post',pipeno:pipeno,reasons:reasons,rejectcomment:rejectcomment,pipe_id:pipe_id},
                  success:function(data){
                      $('#Rspinner'+pipeno).addClass('d-none');
                    location.reload();
                    toastr.success("", "Farmer rejected", {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                          progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,
                          onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                          showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                          hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                      $(".PipeReject").prop('disabled', false);
                      $('#Rspinner'+pipeno).addClass('d-none');
                    var data = jqXHR.responseJSON;
                    toastr.error("", data.message, {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,
                          preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                          hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//ajax end
          }else{
              $(".PipeReject").prop('disabled', false);
              $('#Rspinner'+plotno).addClass('d-none');
          }//if end of confirmation
        })//swal end
});

</script> --}}
@stop
