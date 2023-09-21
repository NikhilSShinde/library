@extends('layouts.app')

 @section('meta')
    <title>{{Lang::choice('website_keywords.become_a_star',\App::getLocale())}}</title>
 @endsection 
@section('content')
<section class="registration-process board-lg-opt">
  <div class="container">
    <div class="row">
      <div class="board"> 
        <div class="board-inner">
          <ul class="nav nav-tabs" id="myTab">
            <div class="liner"></div>
            <li class="active"> <a href="javascript:void(0);" data-toggle="tab" title="{{Lang::choice('website_keywords.personal_details',\App::getLocale())}}"> <span class="round-tabs one"> <i class="fa fa-user" aria-hidden="true"></i> </span> </a></li>
            <li><a href="javascript:void(0);" data-toggle="tab" title="{{Lang::choice('website_keywords.completed',\App::getLocale())}}"> <span class="round-tabs three"> <i class="fa fa-file-text-o" aria-hidden="true"></i> </span> </a> </li>
          </ul>
        </div>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="home">
               @if (session('update-country-status'))
                    <div class="alert alert-warning">
                          {{ session('star-error') }}
                    </div>
              @endif
            <h3 class="head text-center">{{Lang::choice('website_keywords.provide_more_details',\App::getLocale())}}</h3>
            <div class="become-a-star-form">
              <form  name="register_normal" id="register_normal" enctype="multipart/form-data" role="form" method="POST" action="{{url('/become-a-star-personal-info')}}">
                   {!! csrf_field() !!}
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('first_name') ? ' has-error' : '' }}">
                      <input autofocus="true" tabindex="1" type="text" class="form-control" value="{{old('first_name')}}" name="first_name" id="first_name" placeholder="{{Lang::choice('website_keywords.first_name',\App::getLocale())}}">
                      @if ($errors->has('first_name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('first_name') }}</strong>
                            </span>
                        @endif
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('last_name') ? ' has-error' : '' }}">
                    <input tabindex="2" type="text" class="form-control" value="{{old('last_name')}}" id="last_name" name="last_name" placeholder="{{Lang::choice('website_keywords.last_name',\App::getLocale())}}">
                        @if ($errors->has('last_name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('last_name') }}</strong>
                            </span>
                        @endif
                  </div>
                </div>
                 <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('email') ? ' has-error' : '' }}">
                    <input tabindex="3" type="text" class="form-control" value="{{old('email')}}" name="email" id="email" placeholder="{{Lang::choice('website_keywords.email_become_star',\App::getLocale())}}">
                      @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('prefer_language') ? ' has-error' : '' }}">
                       <div class="custom-arrow">
                            <select tabindex="4" class="form-control" id='prefer_language' name='prefer_language'>
                                    <option value="0">{{Lang::choice('website_keywords.prefer_language',\App::getLocale())}}</option>
                                    @if(count($spoken_languages))
                                       @foreach($spoken_languages as $prefer_lang)
                                       <option value="{{$prefer_lang->id}}" @if(old('prefer_language')==$prefer_lang->id) selected @endif>{{$prefer_lang->name}}</option>
                                        @endforeach
                                    @endif
                                  
                                </select>
                       </div>  
                      @if ($errors->has('prefer_language'))
                             <span class="help-block">
                                <strong>{{ $errors->first('prefer_language') }}</strong>
                            </span>
                        @endif
                  </div>
                </div>   
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('nationality') ? ' has-error' : '' }}">
                    <div class="custom-arrow">
                      <select tabindex="5" class="form-control" id='nationality' name='nationality'>
                          <option value='' selected="">--{{Lang::choice('website_keywords.nationality',\App::getLocale())}}--</option>
                          @if($nationality)
                            @foreach($nationality as $national)
                             @if(App::getLocale()=='ar')
                                <option value="{{$national->id}}" @if(old('nationality')==$national->id) selected @endif>{{$national->country_name_arabic}}</option>
                             @else
                                <option value="{{$national->id}}" @if(old('nationality')==$national->id) selected @endif>{{$national->country_name}}</option>
                             @endif
                            @endforeach
                          @endif
                      </select>
                        @if ($errors->has('nationality'))
                            <span class="help-block">
                                <strong>{{ $errors->first('nationality') }}</strong>
                            </span>
                        @endif
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('country') ? ' has-error' : '' }}">
                    <div class="custom-arrow">
                      <select tabindex="5" class="form-control" id='country' name='country' onchange="getAllStates(this.value)" >
                        <option value=''>--{{Lang::choice('website_keywords.country',\App::getLocale())}}--</option>
                          @if($countries)
                            @foreach($countries as $country)
                            <option value="{{$country->id}}" @if(old('country')==$country->id) selected @endif>{{$country->name}}</option>
                            @endforeach
                          @endif
                      </select>
                        @if ($errors->has('country'))
                            <span class="help-block">
                                <strong>{{ $errors->first('country') }}</strong>
                            </span>
                        @endif
                    </div>
                  </div>
                </div>
                <div class="form-group">   
                   <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('region') ? ' has-error' : '' }}">
                    <div class="custom-arrow">
                        <select  tabindex="6" onchange="getAllCities(this.value)" class="form-control" id='state' name='state'>
                           <option value="">--{{Lang::choice('website_keywords.region',\App::getLocale())}}--</option>
                       
                         </select>
                        <input type='hidden' id='state_old' name='state_old' value='{{old('state')}}'>
                         @if ($errors->has('state'))
                            <span class="help-block">
                                <strong>{{ $errors->first('state') }}</strong>
                            </span>
                        @endif
                    </div>
                  </div>
                    
                  <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('city') ? ' has-error' : '' }}">
                      <div class="custom-arrow">
                      <select  tabindex="6" class="form-control" id='city' name='city'>
                        <option value="">--{{Lang::choice('website_keywords.City',\App::getLocale())}}--</option>
                       
                      </select>
                            <input type='hidden' id='city_old' name='city_old' value='{{old('city')}}'>
                         @if ($errors->has('city'))
                            <span class="help-block">
                                <strong>{{ $errors->first('city') }}</strong>
                            </span>
                        @endif
                    </div>
                  </div>
                </div>
           <div class="form-group">
               <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('device') ? ' has-error' : '' }}">
                     <div class="custom-arrow">
                        <select  tabindex="7" class="form-control" id='device' name='device'>
                        <option value="">{{Lang::choice('website_keywords.select_device',\App::getLocale())}}</option>
                        <option value="0" @if(old('device')=='0') selected @endif>{{Lang::choice('website_keywords.android',\App::getLocale())}}</option>
                        <option value="1" @if(old('device')=='1') selected @endif>{{Lang::choice('website_keywords.ios',\App::getLocale())}}</option>
                      </select>
                 
                    </div>
                    </div>
               
               <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('working_time') ? ' has-error' : '' }}">
                    <div class="custom-arrow">
                      <select  tabindex="8" class="form-control" id='working_time' name='working_time'>
                        <option value="">{{Lang::choice('website_keywords.working_time',\App::getLocale())}}</option>
                        <option value="0"  @if(old('working_time')=='0') selected @endif>{{Lang::choice('website_keywords.part_time',\App::getLocale())}}</option>
                        <option value="1"  @if(old('working_time')=='1') selected @endif>{{Lang::choice('website_keywords.full_time',\App::getLocale())}}</option>
                      </select>
                        @if ($errors->has('working_time'))
                            <span class="help-block">
                                <strong>{{ $errors->first('working_time') }}</strong>
                            </span>
                        @endif
                    </div>
                  </div>
              </div>
                <div class="form-group">  
                    <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('address') ? ' has-error' : '' }}">
                    
                        <textarea  tabindex="9" placeholder="{{Lang::choice('website_keywords.type_your_address',\App::getLocale())}}" class="form-control" name="address" id="address">{{old('address')}}</textarea>
                         @if ($errors->has('address'))
                            <span class="help-block">
                                <strong>{{ $errors->first('address') }}</strong>
                            </span>
                        @endif
                      
                   </div> 
                
                    <div class="col-md-6 col-sm-6 col-xs-12 {{ $errors->has('driver_license') ? ' has-error' : '' }}">
                      <input name="driver_license" dir="ltr" type="file" class="" id="driver_license" value="" size="80"  autocomplete="off">
                      {{Lang::choice('website_keywords.upload_driver_license',\App::getLocale())}}
                       @if ($errors->has('driver_license'))
                        <span class="help-block">
                            <strong>{{ $errors->first('driver_license') }}</strong>
                        </span>
                        @endif
                      
                   </div> 
                </div>    
               
                <div class="col-md-12">
                   <div class="continue-btnouter pull-right"> <button type="submit" id="btn_register" class="btn btn-default continue-btn">{{Lang::choice('website_keywords.submit',\App::getLocale())}}</button>
                     <img id="btn_loader" style="display:none;" src="{{url('public/media/front/images/loader.gif')}}">
                   </div>
                </div>
              </form>
            </div>
          </div>
         
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
 $(document).ready(function()
 {
     if($("#country").val()!='')
     {
        $.ajax({
                   url:"{{url('/admin/states/getAllStatesRegistration')}}/"+$("#country").val(),
                   method:'get',
                   success:function(data)
                   {

                        $("#state").html(data);
                         $("#state").val($("#state_old").val());

                   }

             });
     }
     if($("#state_old").val()!='')
     {
        $.ajax({
                   url:"{{url('/admin/cities/getAllCitiesRegistration')}}/"+$("#country").val()+"/"+$("#state_old").val(),
                   method:'get',
                   success:function(data)
                   {

                        $("#city").html(data);
                        $("#city").val($("#city_old").val());
                   }

                });
     }
 });
        function getAllStates(country_id)
        {
            if(country_id!='' && country_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/states/getAllStatesRegistration')}}/"+country_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#state").html(data);
                        // $("#city").html("");

                   }

                });
            }
        }
        function getAllCities(state_id)
        {
            if(state_id!='' && state_id!=0)
            {
               var country_id=$("#country").val();
                $.ajax({
                   url:"{{url('/admin/cities/getAllCitiesRegistration')}}/"+country_id+"/"+state_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#city").html(data);

                   }

                });
            }
        }
 </script>
            
@endsection
