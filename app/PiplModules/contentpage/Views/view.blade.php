@extends(config("piplmodules.front-view-layout-location"))

@section('meta')
<title>{{$page_information->page_title}}</title>
<meta name="keywords" content="{{$page_information->page_meta_keywords}}" />
<meta name="description" content="{{$page_information->page_meta_descriptions}}" />
@endsection
@section("content")
@include("layouts.cms-image")
<div class="cms-banner-sec fullHt" style="background-image:url(img/bg1.jpg);">
     <div class="container">
         <h1><span>{!! $page_information->page_title !!}</span></h1>
         <div class="cms_contents">
             {!! $page_information->page_content !!}
         </div>    
     </div>
</div>

@endsection