<section class="banner" style="background:url({{url('/public/media/front/images/banner.jpg')}});">
  <div class="container">
    <div class="banner-view">
      <div class="row">
        <div class="col-md-7 col-xs-12">
          <div class="banner-text">
          <div class="table-view">
<!--              <h1 class="dlv-h">{{Lang::choice('website_keywords.dlvr_all',\App::getLocale())}}</h1>
              <p class="dlv-p">{{Lang::choice('website_keywords.instant_delivery_from_your_place',\App::getLocale())}}</p>-->
 <!--             <a href="javascript:void(0)" id="download-app" class="btn btn-default download-app">{{Lang::choice('website_keywords.download_app',\App::getLocale())}}</a>-->
              
                <div class="app-buttons"> <a target="_blank" href="{{GlobalValues::get('ios-store')}}"> <img src="{{url('/public/media/front/images/App-Store.png')}}" class="img-responsive" alt="app"/></a> <a href="{{GlobalValues::get('android-play-store')}}"> <img src="{{url('/public/media/front/images/googleplay.png')}}" class="img-responsive" alt="play"/></a> </div>
              
            </div>
          </div>
        </div>
        <div class="col-md-5 col-xs-12 hidden-sm hidden-xs">
          <div class="banner-img">
            <div class="table-view"> <img src="{{url('/public/media/front/images/iphones_')}}{{\App::getLocale()}}.png" class="img-responsive" alt="iphone"/> </div>
          </div>
        </div>
      </div>
    </div>
    <!--banner-view complated--> 
  </div>
</section>

