@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Assign A Driver user</title>

@endsection

@section('content')
<script src="http://maps.google.com/maps/api/js?v=3&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco" type="text/javascript"></script>
<script src="http://jawj.github.io/OverlappingMarkerSpiderfier/bin/oms.min.js" type="text/javascript"></script>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/order-list')}}">Manage Orders</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Assign A Driver user</a>
            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Assign A Driver user
                </div>

            </div>
           <div class="portlet-body form">
                          <form name="frm_assign_star" id="frm_assign_star" action="{{url('/admin/assign-star-to-order')}}" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                             <div class='divider'> <span>Following drivers are available for the trip to be assigned</span></div>  
                             @if(count($available_stars)>0)   
                              @foreach($available_stars as $star)
                                
                               <div class="col-md-3 col-md-border">
                                    
                                    <div class="form-group">
                                        <div class="col-md-5 text-center">     
                                            <span class="help-block">
                                                 <input   type="radio" name="assign_to" id="assign_to" value="{{$star['user_id']}}">
                                            </span>
                                            
                                        </div>
                                          
                                         <label class="col-md-7 control-label">{{$star['first_name'] ." ".$star['last_name']}} : ({{$star['distance']}}) Km </label>
                                        
                                    </div>
                                   <br>
                                   
                                    </div>
                               
                                   
                               @endforeach
                               @if ($errors->has('assign_to'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('assign_to') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                
                            <div class="col-md-12">
                                <div class="col-md-6 col-md-border">
                                    <div class="form-group">
                                      <input type="hidden" name="order_id" id="order_id" value="{{isset($order_details->id)?$order_details->id:'0'}}">  
                                      <button type="submit" class="btn btn-success">Assign Now</button>
                                  
                                 </div>
                                </div>
                            </div>
                             @else
                              No driver user available
                             @endif  
                        </div>
                       @if(count($available_stars)>0) 
                       
                            
                         <div class="row">
                           <div class="col-md-12"> 
                            <div class='divider'> <span> Map View- Following driver's are available for the trip to be assigned</span></div>  
                           
                             <div id="map_canvas" class="mapping"></div>
                                            
                           </div>
                         
                        </div>
                    
                     @endif  
                    </div>
                </form> 
               </div>
               </div>
               </div>
               </div>
          <style>
    #map_wrapper {
        height: 400px;
    }

    #map_canvas {
        width: 100%;
        height: 100%;
    }
</style>    
<script>
 $(document).ready(function()
 {
    var iconBase = 'https://maps.google.com/mapfiles/kml/pal4/';
    var iconBaseOrder = 'https://maps.google.com/mapfiles/kml/pal4/';
        var icons = {
          star: {
            icon: iconBase + 'icon58.png'
            },
          order: {
            icon: iconBaseOrder + 'icon55.png'
            },
        };
var locations = [['order Id:-{{$order_details->id}}<br> order Code/Number:-{{$order_details->order_unique_id}} </br>order Posted Date:-{{$order_details->order_place_date_time}}<br>order Date:-{{$order_details->created_at}}<br><a targe="_blank" href="order-view/{{$order_details->id}}">View More</a><br>',{{$order_details->getOrderTransInformation->selected_pickup_lat}},{{$order_details->getOrderTransInformation->selected_pickup_long}},{{$order_details->order_unique_id}}]];
var locations1 = [{!!$availablestarData!!}];
   var map = new google.maps.Map(document.getElementById("map_canvas"), {
    zoom: 14,
    center: new google.maps.LatLng({{$order_details->getOrderTransInformation->selected_pickup_lat}},{{$order_details->getOrderTransInformation->selected_pickup_long}}),
    mapTypeId: google.maps.MapTypeId.ROADMAP
   });
   var oms = new OverlappingMarkerSpiderfier(map, {markersWantMove:true, markersWontHide:true, keepSpiderfied:true, circleSpiralSwitchover:20});
   var infowindow = new google.maps.InfoWindow();
   var marker, i;
   for (i = 0; i < locations.length; i++) {
       marker = new google.maps.Marker({
       position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        icon: icons['order'].icon,
               map: map,
               title:'order- ' + locations[i][3],
               desc:'detail- ' + locations[i][3]
       });
   oms.addMarker(marker);
   google.maps.event.addListener(marker, 'click', (function(marker, i) {
   return function() {
   infowindow.setContent(locations[i][0]);
           infowindow.open(map, marker);
   }
   })(marker, i));
}
    for (i = 0; i < locations1.length; i++) {
          marker = new google.maps.Marker({
          position: new google.maps.LatLng(locations1[i][1], locations1[i][2]),
          icon: icons['star'].icon,
           map: map,
           title:locations1[i][3],
           desc:locations1[i][3]
          });
      oms.addMarker(marker);
      google.maps.event.addListener(marker, 'click', (function(marker, i) {
      return function() {
      infowindow.setContent(locations1[i][0]);
              infowindow.open(map, marker);
      }
      })(marker, i));
   }
});
 </script>
 <style>
    .divider{
       background-color: #ccc;
       border: 1px solid;
       width: 100%;
       height: 30px;
       color:#fff;
       text-align: center;
       font-size: 18px;
    }
    .col-md-border{
        margin-top: 10px;
    }
    .control-label{
        margin: 5px;
    }
</style>      
@endsection
