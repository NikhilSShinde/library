@extends('layouts.app')

 @section('meta')
    <title>{{GlobalValues::get('site-title')}}</title>
 @endsection
 
@section('content')
<section class="banner-content-sec fullHt" style="background-image:url('{{url('public/media/front/images/bg.jpg')}}')" data-bottom-top="background-position: 0% -300px;" data-top-bottom="background-position: 0% 0px;" data-scroll-index="1">
	<div class="caption-center">
        <div class="container">
            <div class="row">
                <div class="col-sm-7">
                    <h1 class="wow bounceInLeft"  style="animation-delay:0.2s;">BAGGI APP IS LIVE <span>DOWNLOAD IT NOW !</span></h1>
                    <p class="wow bounceInLeft"  style="animation-delay:0.4s;">get the coolest app for free</p>
                    <div class="app-store wow bounceInLeft" style="animation-delay:0.6s;">
                        <a target="_blank" href="{{GlobalValues::get('android-play-store')}}"><img src="{{url('public/media/front/images/ggl-play.png')}}" alt="Play store"/></a>
                    	<a target="_blank" href="{{GlobalValues::get('ios-store')}}"><img src="{{url('public/media/front/images/iphone-btn.png')}}" alt="Apple store"/></a>
                    </div>
                </div>
                <div class="col-sm-5"><div class="screens-banner wow bounceInUp" style="animation-delay:0.8s;"><img src="{{url('public/media/front/images/iPhoneX-PSD-Mockup.png')}}" alt="screen"/></div></div>
            </div>
        </div>
    </div>
</section>
<section class="about-us" id="aboutus" data-scroll-index="2">
	<div class="fullHt">
        <div class="container">
            <div class="row">
                <div class="about-m col-sm-5">
                    <div class="ab-mob large-mb" data-bottom-top="left:0%;" data-top-bottom="left:0%;" data-top-top="left:35%;" data-anchor-target="#aboutus"><img src="{{url('public/media/front/images/mb-large.png')}}" alt=""></div>
                    <div class="ab-mob small-mb" data-bottom-top="opacity:0; left:0%;" data-top-bottom="opacity:0; left:0%;" data-top-top="opacity:1; left:7%;" data-anchor-target="#aboutus"><img src="{{url('public/media/front/images/mb-small.png')}}" alt=""></div>
                </div>
                <div class="about-content col-sm-7" data-bottom-top="opacity:0; top:20%;" data-top-bottom="opacity:0; top:20%;" data-top-top="opacity:1; top:0%;" data-anchor-target="#aboutus">
                    <h2 class="sub-head">About us</h2>
                    <p class="large-par">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolet quas molestias excepturi sint occaecati cupiditate non provide At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolet quas molestias 
    excepturi sint occaecati cupiditate non provide.</p>
                    <p class="large-par">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolet quas molestias excepturi sint occaecati cupiditate non provide At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolet quas molestias 
    excepturi sint occaecati cupiditate non provide.</p>
                    <a href="javascript:void(0)" class="btn btn-more">View more</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="app-features-sec fullHt" id="app-feature" style="background-image:url('{{url('public/media/front/images/feat-bg.png')}}')" id="App-feature" data-bottom-top="background-position: 0% -500px;" data-top-bottom="background-position: 0% 0px;" data-scroll-index="3">
	<div class="container">
    	<div class="text-center">
        	<div class="sub-head" data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#app-feature">App Features</div>
            <p  data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#app-feature">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti.</p>
        </div>
        <div class="app-infos">
        	<div class="row">
                <div class="col-md-4 text-right">
                	<div class="inner-app-feat wow bounceInLeft" style="animation-delay:0.2s;">
                    	<i class="icon-settings"></i>
                        <h3>Color Schemes</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                    <div class="inner-app-feat wow bounceInLeft" style="animation-delay:0.4s;">
                    	<i class="icon-photo"></i>
                        <h3>Responsive Media</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                    <div class="inner-app-feat wow bounceInLeft" style="animation-delay:0.6s;">
                    	<i class="icon-settings"></i>
                        <h3>Cross Browser Support</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="center-mob-div wow zoomIn" style="background-image:url('{{url('public/media/front/images/feature-iphone.png')}}')">
                        <div class="splash-light-left" style="background-image:url('{{url('public/media/front/images/light.png')}}')"></div>
                        <div class="splash-light-right" style="background-image:url('{{url('public/media/front/images/light.png')}}')"></div>
                    </div>
                </div>
                <div class="col-md-4 text-left">
                	<div class="inner-app-feat wow bounceInRight" style="animation-delay:0.2s;">
                    	<i class="icon-light-bulb"></i>
                        <h3>720+ Icon Fonts</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                    <div class="inner-app-feat wow bounceInRight" style="animation-delay:0.4s;">
                    	<i class="icon-diamond"></i>
                        <h3>Pure & Simple</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                    <div class="inner-app-feat wow bounceInRight" style="animation-delay:0.6s;">
                    	<i class="icon-gift2"></i>
                        <h3>More Features</h3>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="screen-sec" data-scroll-index="4">
	<div class="fullHt screenshots" id="screenshots">
        <div class="">
            <div class="text-center">
            	<div class="sub-head" data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#screenshots">Screenshots</div>
            </div>
            <div class="screen-box">
            	<div class="mobile-screen" data-bottom-top="opacity:0;" data-top-bottom="opacity:0;" data-top-top="opacity:1;" data-anchor-target="#screenshots"><img src="{{url('public/media/front/images/screen-mobile.png')}}" alt=""></div>
            	<div class="owl-carousel mobile-screen-slider" id="screens">
                	<div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen1.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen2.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen3.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen4.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen5.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen6.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen7.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen8.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen9.jpg')}}" alt=""></div></div>
                    <div class="item"><div class="screen-img"><img src="{{url('public/media/front/images/screen10.jpg')}}" alt=""></div></div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="faqs-sec" style="background-image:url('{{url('public/media/front/images/faq-bg.jpg')}}');" id="Faq-id" data-bottom-top="background-position: 0% -500px;" data-top-bottom="background-position: 0% 0px;" data-scroll-index="5">
    	<div class="container">
        	<div class="sub-head" data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#Faq-id">Faq's</div>
        	<div class="faq-content">
            	<div class="row">
                <div class="col-lg-12">
                   <div id="accordion" class="panel-group accordion">
                       @if(count($faqs)>0)  
                       @foreach($faqs as $faq)
                            <div class="panel panel-default wow bounceInUp" data-wow-delay="0.2s">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapse{{$faq->id}}" data-parent="#accordion" data-toggle="collapse">
                                            <i class="switch fa fa-plus"></i>
                                            {!!$faq-question!!}
                                        </a>
                                    </h4>
                                </div>
                                <div class="panel-collapse collapse" id="collapse{{$faq->id}}">
                                    <div class="panel-body">{!!$faq-answer!!}</div>
                                </div>
                            </div>
                        @endforeach
                       @endif
                       
		 </div>
                </div>
		 </div>
            </div>
        </div>
</section>
<section class="contact-us-sec fullHt" style="background-image:url('{{url('public/media/front/images/contact-bg.png')}}');" id="contact" data-center-top="background-position: 0% -200px;" data-center-bottom="background-position: 0% -0px;" data-scroll-index="6">
	<div class="container">
    	<div class="text-center">
        	<div class="sub-head" data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#contact">Stay Connected</div>
            <p class="wow fadeInUp" data-bottom-top="opacity:0; transform:scale(0);" data-top-bottom="opacity:0; transform:scale(0);" data-top-top="opacity:1; transform:scale(1);" data-anchor-target="#contact">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti.</p>
        </div>
        <div class="contact-form"  data-bottom-top="opacity:0; transform:translatey(100%) scale(0);" data-center="opacity:1; transform:translatey(0%) scale(1);" data-top-bottom="opacity:0; transform:translatey(100%) scale(0);" data-anchor-target="#contact">
        	<form>
            	<div class="form-group">
                	<div class="row">
                        <div class="col-xs-6"><input id="first-name" type="text" class="form-control" placeholder="First name"></div>
                        <div class="col-xs-6"><input id="last-name" type="text" class="form-control" placeholder="Last name"></div>
                    </div>
                </div>
                <div class="form-group">
                	<div class="row">
                        <div class="col-xs-12"><input id="e-mail" type="text" class="form-control" placeholder="E-mail"></div>
                    </div>
                </div>
                <div class="form-group">
                	<div class="row">
                        <div class="col-xs-12"><textarea id="message" class="form-control" placeholder="Message"></textarea></div>
                    </div>
                </div>
                <div class="form-group">
                	<a href="#" class="btn send-msg">Send message</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
