@extends(config("piplmodules.front-view-layout-location"))

@section('meta')
<title>{{$page_information->seo_title}}</title>
<meta name="keywords" content="{{$page_information->seo_keywords}}" />
<meta name="description" content="{{$page_information->seo_description}}" />
@endsection

@section("content")
<section class="cms-banner"  style="background:url({{url('/public/media/front/images/howitbg.png')}});">
  <div class="container">
    <div class="cms-heading">
      <ul class="list-inline">{{Lang::choice('website_keywords.Blog',\App::getLocale())}}
        <li>{{Lang::choice('website_keywords.blog_detail',\App::getLocale())}}</li>
      </ul>
    </div>
  </div>
</section>
<section class="middle">
  <div class="blogs-detail">
    <div class="container">
      <div class="blog-main-section"> 
         <div class="row">
              <div class="col-md-10 col-md-offset-1 col-xs-offset-0 col-sm-offset-0">
                  <div class="Blog-detail-main">
                      <p class="bywhom clearfix"><span class="byhom-one"><img src="{{url('/public/media/front/images/splashicon.png')}}" class="img-responsive" alt="splash"></span> <span class="byhom-two">{{Lang::choice('website_keywords.dlvr_all',\App::getLocale())}}</span><span class="byhom-three pull-right"><a href="{{url('/blog')}}" class="btn btn-default follow-btn">Back</a> </span></p>
                      <p class="blog-head-para">{!! $page_information->short_description !!}</p>
                      <p class="blog-head-para"><div> <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($page->created_at))->diffForHumans() ?> &nbsp; <i class="fa fa-tag"></i> @foreach($page->tags as $tag) <a href="{{ url('/blog/tags/'.$tag->slug) }}"><i class="badge">{{$tag->name}}</i></a> @endforeach</div></p>
                      
                       <div class="Blog-detail-main-image">
                         <img src="{{asset('storageasset/blog/'.$page->post_image)}}" class="img-responsive" alt="blog">
                        </div>
                      <p class="blog-bottom-para">{{strip_tags($page_information->description)}}</p>
                   </div>
              </div>
         </div>
      </div>
    </div>
  </div>

    @if($page->post_attachments)
    {{Lang::choice('website_keywords.attachments',\App::getLocale())}}:
    <ul class="list-inline">
        @foreach($page->post_attachments as $key=>$attachment)
        <li><a target="new" href="{{asset('storage/blog/'.$attachment['original_name'])}}"><i class="fa fa-download"></i> {{$attachment['display_name']}}</a></li>
        @endforeach
    </ul>
    @endif

    @if(Auth::check() && $page->allow_comments)

    <hr />

    <div class="row">
        <div class="col-md-12">
            <form method="post" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <legend>{{Lang::choice('website_keyword.post_comment',\App::getLocale())}}</legend>
                <div class="form-group @if ($errors->has('comment')) has-error @endif">
                    <label>{{Lang::choice('website_keyword.enter_your_comments_here',\App::getLocale())}}</label>
                    <textarea class="form-control" name="comment"></textarea>
                    @if ($errors->has('comment'))
                    <span class="help-block">
                        <strong class="text-danger">{{ $errors->first('comment') }}</strong>
                    </span>
                    @endif
                </div>
                @if($page->allow_attachments_in_comments)
                <div class="form-group @if ($errors->has('attachments')) has-error @endif">
                    <label>{{Lang::choice('website_keyword.select_attachments',\App::getLocale())}}</label>
                    <input class="form-control" type="file" multiple="multiple" name="attachments[]" />
                    @if ($errors->has('attachments'))
                    <span class="help-block">
                        <strong class="text-danger">{{ $errors->first('attachments') }}</strong>
                    </span>
                    @endif
                </div>
                @endif
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-primary">{{Lang::choice('website_keyword.post',\App::getLocale())}}</button>
                </div>
            </form>
        </div>
    </div>


    <h2>{{Lang::choice('website_keyword.Comments',\App::getLocale())}}</h2>

    @foreach($page->comments()->get() as $comment)
    <div class="row">
        <div class="col-md-1 col-sm-6">
            @if(!empty($comment->commentUser->userInformation->profile_picture))
            <img src="{{asset('storage/avatars/thumbnails/'.$comment->commentUser->userInformation->profile_picture)}}" height="50"  />
            @endif

        </div>
        <div class="col-md-11 col-sm-6">
            <strong>{{ $comment->commentUser->userInformation->first_name}}</strong> &nbsp; {{$comment->comment}}

            @if(count($comment->comment_attachments))
            <br /><br />
            <ul class="list-inline">
                <li> {{Lang::choice('website_keywords.attachments',\App::getLocale())}}: </li>
                @foreach($comment->comment_attachments as $key=>$attachment)
                <li><a target="new" href="{{asset('storage/blog/'.$attachment['original_name'])}}"><i class="fa fa-download"></i> {{$attachment['display_name']}}</a></li>
                @endforeach
            </ul>

            @else
            <br /><br />
            @endif

            - <i>{{$comment->created_at->format('M d \a\t h:i a')}}</i>
        </div>
    </div>
    <br />
    @endforeach
    @endif

</section>
@endsection