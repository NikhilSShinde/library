@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Blog Search</title>
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
                <li>{{Lang::choice('website_keywords.Blog',\App::getLocale())}}</li>
            </ul>
        </div>
    </div>
</section>
<section class="middle">
    <div class="blogs">
        <div class="container">
            @include('blog::left-section')
            <div class="blog-main-section">
                <div class="blog-deatil">
                    <div class="row">
                        @if(count($posts) < 1)
                        <div class="well">{{Lang::choice('website_keywords.we_didnt_found_any_post_yet',\App::getLocale())}}.</div>
                        @endif

                        @foreach($posts as $key => $post)
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            
                            <div class="thumbnail">@if($post->post_image) <img src="{{asset('storageasset/blog/'.$post->post_image)}}" alt="blog" class="img-responsive">@endif
                                <div class="caption">
                                    <h3><a href="{{ url('/blog/'.$post->post_url) }}" title="Click to view the post">{{$post->title}}</a></h3>
                                    <p class="blog-detail-p">{{$post->short_description}}</p>
                                    <p class="bywhom"><span class="byhom-one"><img src="{{url(('/public/media/front/images/splashicon.png'))}}" class="img-responsive" alt="splash"/></span> <span class="byhom-two" >{{Lang::choice('website_keywords.dlvr_all',\App::getLocale())}}</span> <span  class="byhom-three">
                                           <div> <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($post->created_at))->diffForHumans() ?> &nbsp; <i class="fa fa-tag"></i> @foreach($post->tags as $tag) <a href="{{ url('/blog/tags/'.$tag->slug) }}"><i class="badge">{{$tag->name}}</i></a> @endforeach</div>
                                        </span></p> 
                               </div>
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