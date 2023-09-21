@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update CITY GEO SETTING</title>

@endsection

@section('content')
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco&sensor=false&libraries=places&radius=20"></script>
<div class="page-content-wrapper">
		<div class="page-content">
                    <!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashbard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/city-geo-settings/list')}}">Manage CITY GEO SETTING</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update CITY GEO SETTING</a>
					
				</li>
                        </ul>    
           <div class="portlet box blue">
             <div class="portlet-title">
                <div class="caption">
                        <i class="fa fa-gift"></i>Update CITY GEO SETTING
                </div>
             </div>
             <div class="portlet-body form">
                 <form class="form-horizontal" id="frm_country_add" name="frm_country_add" role="form" action="" method="post" >
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group">
                          <label class="col-md-6 control-label">City<sup>*</sup></label>
                            <div class="col-md-6">     
                                <select name="city" id="city" class="form-control">
                                    <option value="">--Select City</option>
                                    @if(count($city_data)>0)
                                        
                                        @foreach($city_data as $city)
                                        <option value="{{$city->id}}" @if($city->id==$geo_limit_data->city_id) selected @endif>{{$city->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @if ($errors->has('location1'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('location1') }}</strong>
                              </span>
                              @endif
                          </div>
                        </div>
                         <div class="form-group">
                          <label class="col-md-6 control-label">Location1<sup>*</sup></label>
                            <div class="col-md-6">     
                            <input name="location1" type="text" class="form-control" id="location1" value="{{old('location1',$geo_limit_data->location1)}}">
                            @if ($errors->has('location1'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('location1') }}</strong>
                              </span>
                              @endif
                          </div>
                        </div>
                         <div class="form-group">
                          <label class="col-md-6 control-label">Location2<sup>*</sup></label>
                            <div class="col-md-6">     
                            <input name="location2" type="text" class="form-control" id="location2" value="{{old('location2',$geo_limit_data->location2)}}">
                            @if ($errors->has('location2'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('location2') }}</strong>
                              </span>
                              @endif
                          </div>
                        </div>
                        <div class="form-group">
                         <div class="col-md-12">   
                            <input  type="hidden" id="location1_lat" name="location1_lat" value='{{old('location1_lat',$geo_limit_data->location1_lat)}}'>
                            <input  type="hidden" id="location1_long" name="location1_long"  value='{{old('location1_long',$geo_limit_data->location1_long)}}'>
                            <input  type="hidden" id="location2_lat" name="location2_lat"  value='{{old('location2_lat',$geo_limit_data->location2_lat)}}'>
                            <input  type="hidden" id="location2_long" name="location2_long"  value='{{old('location2_long',$geo_limit_data->location2_long)}}'>
                            
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update</button>
                         </div>
                        </div>
                </div>
              </div>
            </div>
                
             </div>
    
            </form>
        </div>
    </div>
    </div>
    </div>
<script>
    function initialize() {

var input = document.getElementById('location1');
var input1 = document.getElementById('location2');
var autocomplete = new google.maps.places.Autocomplete(input);
autocomplete.setComponentRestrictions({'country': ['in']});
var autocomplete1 = new google.maps.places.Autocomplete(input1);
autocomplete1.setComponentRestrictions({'country': ['in']});
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
      console.log(autocomplete);
   $("#location1_lat").val(autocomplete.getPlace().geometry.location.lat());
   $("#location1_long").val(autocomplete.getPlace().geometry.location.lng());
  });
  google.maps.event.addListener(autocomplete1, 'place_changed', function() {
   $("#location2_lat").val(autocomplete1.getPlace().geometry.location.lat());
   $("#location2_long").val(autocomplete1.getPlace().geometry.location.lng());
  });

}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
        <style>
            .submit-btn{
                padding: 10px 0px 0px 18px;
            }
        </style>
 @endsection