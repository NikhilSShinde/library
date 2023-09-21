@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Employee Tracking</title>

@endsection

    
@section('content')

<!--<script src="http://maps.google.com/maps/api/js?v=3&key=AIzaSyDAIdAZtTw-KDj1qKEAm9ceFFV_SMKHf1k&libraries=visualization" type="text/javascript"></script>-->
<script src="http://jawj.github.io/OverlappingMarkerSpiderfier/bin/oms.min.js" type="text/javascript"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAIdAZtTw-KDj1qKEAm9ceFFV_SMKHf1k&sensor=false&libraries=geometry,places&ext=.js"></script> -->

 <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAJaSQL93o9brPgYRqMAam6KfG5kiBjo0g&libraries=geometry,places&ext=.js"></script> 

<script type="text/javascript" src="{{url('public/media/backend/js/amchart.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/funnelchart.js')}}"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<div class="page-content-wrapper">

    <div class="page-content">

        <div class="tab-content">
            <div role="tabpanel" id="ordersheatmap" class="tab-pane active">
                <input type="hidden" id="zoneText">
                <div id="map_wrapper">
                    <div id="map_canvas" class="mapping"></div>
                </div>
            </div>
        </div>
  

    </div>

</div>  

<script type="text/javascript">

var poly;
var path;
var poly_path = [];
var line = [];
var draw_cnt = 0;
   var map=null;
              function initMap() {
                   var latitude = [{!!$lat!!}];                 
             var longitude = [{!!$long!!}];

                  draw_cnt = 0;
                map = new google.maps.Map(document.getElementById('map_canvas'), {
                  zoom: 8,
                  center: new google.maps.LatLng(18.565836, 73.819413),
                  mapTypeId: google.maps.MapTypeId.ROADMAP
                });
              // Create the DIV to hold the control and call the CenterControl()
            
                var bounds = new google.maps.LatLngBounds();
  var routePoints = [];
   for (var i = 0; i < latitude.length; i++) {
var count = (latitude.length)-1;
    var myLatLong= {lat: latitude[0], lng: longitude[0]};
     var lastLatLong= {lat: latitude[count], lng: longitude[count]};   
  
   // alert(JSON.stringify(myLatLong));
    routePoints.push(new google.maps.LatLng(latitude[i],longitude[i]));
    bounds.extend(new google.maps.LatLng(latitude[i],longitude[i]));


      var marker = new google.maps.Marker({
              position: myLatLong,
              map: map,              
              title: latitude + ', ' + longitude 
            });
       var iconBase = '{{url("public/media/backend/images/")}}';
           var icons = {
          order: {
            icon: iconBase +'/'+ 'img_406159.jpg'
            }
          };

      var marker = new google.maps.Marker({
              position: lastLatLong,
              map: map, 
                 icon: icons['order'].icon,             
              title: latitude + ', ' + longitude 
            });



      
   }
//alert(latitude[3]);
 @foreach($store_info as $store) 

      var store_latitude= '{{ $store->getStoreInformation->latitude }}';
     var store_longitude= '{{ $store->getStoreInformation->longitude }}';

       var mylatlong= {lat: store_latitude, lng: store_longitude};
        //var latlong=JSON.stringify(mylatlong);
        var latlong = new google.maps.LatLng(parseFloat(store_latitude),parseFloat(store_longitude));
        // alert(latlong);
        //var icon_image='https://goo.gl/images/hiJbZz';
          var iconBase = '{{url("public/media/backend/images/")}}';
           var icons = {
          driver: {
            icon: iconBase +'/'+ 'download.jpg'
            }
          };
          //alert(icons['driver'].icon);
        var marker = new google.maps.Marker({
              position: latlong,
              map: map,           
                icon: icons['driver'].icon,
              title: latitude + ', ' + longitude 
            });

    @endforeach


   var route= new google.maps.Polyline({
    path: routePoints,
    strokeColor: "Red",
    strokeOpacity: 1.0,
    strokeWeight: 2
  });

  route.setMap(map);
  map.fitBounds(bounds);
}
google.maps.event.addDomListener(window, 'load', initMap);  
if({!!$lat!!} == ""){
    alert("User location not fount.");
}

</script>

<style>
    #map_wrapper {
        height: 500px;
    }

    #map_canvas {
        width: 100%;
        height: 100%;
    }
    #zoneid {display:none;}
</style>

@endsection
