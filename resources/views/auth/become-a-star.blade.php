@extends('layouts.app')

 @section('meta')
    <title>{{Lang::choice('website_keywords.become_star',\App::getLocale())}}</title>
 @endsection
 
@section('content')
<section class="apply-box-outer">
  <div class="container">
  <div class="apply-box">
  <div class="apply-box-width">
    <h1 class="apply-head">{{Lang::choice('website_keywords.join_the_dlvr_all',\App::getLocale())}}</h1>
    <p class="apply-p">{{Lang::choice('website_keywords.join_the_dlvr_all_description',\App::getLocale())}}</p>
    <form name="become_a_star" method="post" id="become_a_star" action="">
       {!! csrf_field() !!}
       <div class="form-group">
        <label for="mobile">{{Lang::choice('website_keywords.your_mobile',\App::getLocale())}}</label>
        <div class="row">
          <div class="col-md-4 col-sm-4 col-xs-5">
            <div class="custom-arrow {{ $errors->has('country_code') ? 'has-error' : '' }}">
              <select name="country_code" id="country_code" class="form-control">
                   <option value="">--{{Lang::choice('website_keywords.select',\App::getLocale())}}--</option>
                    @if($countries)
                      @foreach($countries as $country)
                      <option value="{{$country->country_code}}" @if((old('country_code')==$country->country_code) || (strtoupper($country->iso)==strtoupper($country_iso))) selected @endif>{{$country->name}} ({{$country->country_code}})</option>
                      @endforeach
                    @endif
              </select>
                 @if ($errors->has('country_code'))
                            <span class="help-block">
                                <strong>{{ $errors->first('country_code') }}</strong>
                            </span>
                @endif
            </div>
          </div>
          <div class="col-md-8 col-sm-8 col-xs-7 {{ $errors->has('mobile') ? 'has-error' : '' }}">
            <input type="text" value="{{old('mobile')}}" class="form-control" id="mobile" name="mobile" placeholder="{{Lang::choice('website_keywords.your_mobile',\App::getLocale())}}">
           
             @if ($errors->has('mobile'))
                            <span class="help-block">
                                <strong>{{ $errors->first('mobile') }}</strong>
                            </span>
                @endif
          </div>
        </div>
      </div>
       <div class="form-group col-md-12">
         <button id="btn_register_first" type="submit" class="btn btn-default continue-btn">{{Lang::choice('website_keywords.continue',\App::getLocale())}}</button>
          @if(App::getLocale()=='ar')
          <img style="display:none;float:left;" id="btn_loader_first"  src="{{url('public/media/front/images/loader.gif')}}">
          @else
          <img style="float:right;display:none;" id="btn_loader_first"  src="{{url('public/media/front/images/loader.gif')}}">
          @endif
       </div>
    </form>
   
    <div class="clearfix">
    </div>
    <p class="applypara">{{Lang::choice('website_keywords.become_a_star_description',\App::getLocale())}}</p>
    </div>
  </div>
  <div class="applyhow-itworkssection">
  <div class="">
    <section class="how-it-works-become">
    <div class="row">
      <div class="">
        <h1 class="how-it-head">{{Lang::choice('website_keywords.how_it_works',\App::getLocale())}}</h1>
        <div class="how-it-main">
             <div class="col-md-4 col-xs-12 col-sm-4">
              <div class="how-it-main-in">
              <div class="hw-it-round"> <img src="{{url('/public/media/front/images/work.png')}}" class="img-responsive" alt="work"> </div>
              <p class="how-p">{{Lang::choice('website_keywords.work_when_you_want_heading',\App::getLocale())}}</p>
                  <p class="cust-para">{{Lang::choice('website_keywords.work_when_you_want',\App::getLocale())}}</p>
                  </div>
           </div>
            <div class="col-md-4 col-xs-12 col-sm-4">
            <div class="how-it-main-in">
             <div class="hw-it-round"> <img src="{{url('/public/media/front/images/choose.png')}}" class="img-responsive" alt="choose"> </div>
             <p class="how-p">{{Lang::choice('website_keywords.choose_your_ride_heading',\App::getLocale())}}</p>

             <p class="cust-para">{{Lang::choice('website_keywords.choose_your_ride',\App::getLocale())}}</p>
             </div>
           </div>
           <div class="col-md-4 col-xs-12 col-sm-4">
               <div class="how-it-main-in">
              <div class="hw-it-round"> <img src="{{url('/public/media/front/images/getpaid.png')}}" class="img-responsive" alt="get-paid"> </div>
              <p class="how-p">{{Lang::choice('website_keywords.earn_smart_heading',\App::getLocale())}}</p>
                  <p class="cust-para">{{Lang::choice('website_keywords.get_paid',\App::getLocale())}}</p>
                  </div>
            </div>
            <div class="clearfix">
            
        </div>
      </div>
    </div>
  </div>
</section>
</div>
</div>
</section>

@endsection
