@extends(config("piplmodules.front-view-layout-location"))

@section('meta')
<title>{{$page_information->page_title}}</title>
<meta name="keywords" content="{{$page_information->page_meta_keywords}}" />
<meta name="description" content="{{$page_information->page_meta_descriptions}}" />
@endsection

@section("content")
<section class="container">

<h1>{{$page_information->page_title}}</h1>

<div>
{!! $page_information->page_content !!}
</div>

</section>
@endsection