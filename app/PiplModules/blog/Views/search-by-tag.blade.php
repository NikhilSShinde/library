@extends(config("piplmodules.front-view-layout-location"))

@section('meta')
<title>{{Lang::choice('website_keywords.tags',\App::getLocale())}}</title>
<style>
    .tree{list-style:none;padding:0;font-size: calc(100% - 2px);}
    .tree > li > a {font-weight:bold;}
    .subtree{list-style:none;padding-left:10px;}
    .subtree li:before{content:"-";width:5px;position:relative;left:-5px;}
</style>
@endsection

@section("content")
<section class="cms-banner"  style="background:url({{url('/public/media/front/images/howitbg.png')}});">
    <div class="container">
        <div class="cms-heading">
            <ul class="list-inline">
                <li>{{Lang::choice('website_keywords.tags',\App::getLocale())}}</li>
            </ul>
        </div>
    </div>
</section>
<section class="middle">
    <div class="blogs">
        <div class="container">
            @include('blog::left-section')
            <div>
                <span class="byhom-three pull-left"><a href="{{url('/blog')}}" class="btn btn-default follow-btn">Back</a> </span>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-10 col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body">

                        @if(count($posts) < 1)
                        <div class="well">{{Lang::choice('website_keywords.we_didnt_found_post',\App::getLocale())}} "{{$tag->name}}" {{Lang::choice('website_keywords.tags',\App::getLocale())}}.{{Lang::choice('website_keywords.please_try_other_tags',\App::getLocale())}} .</div>
                        @endif

                        @foreach($posts as $key => $post)
                        <div class="row">

                            @if($post->post_image)
                            <div class="col-md-1 text-center">
                                <img src="{{asset('storageasset/blog/thumbnails/'.$post->post_image)}}" class="img-responsive thumbnail" />
                            </div>
                            @endif
                            
                            <div class=" @if($post->post_image) col-md-11 @else col-md-12 @endif">
                                <h4><a href="{{ url('/blog/'.$post->post_url) }}" title="Click to view the post" target="new">{{$post->translateOrDefault(\App::getLocale())->title}}</a></h4>
                                {{$post->translateOrDefault(\App::getLocale())->short_description}}
                                <br /><br />
                                <div> <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($post->created_at))->diffForHumans() ?> &nbsp; <i class="fa fa-tag"></i> @foreach($post->tags as $tag) <a href="{{ url('/blog/tags/'.$tag->slug) }}"><i class="badge">{{$tag->name}}</i></a> @endforeach</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        </div>

</section>
@endsection