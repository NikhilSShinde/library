@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>{{Lang::choice('website_keywords.contact_us',\App::getLocale())}}</title>
@endsection

@section("content")
<script>
    function initialize() {
        var mapProp = {
            center: new google.maps.LatLng(51.508742, -0.120850),
            zoom: 5,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>
<section class="cms-banner"  style="background:url({{url('/public/media/front/images/techsupport.jpg')}});">
    <div class="container">
        <div class="cms-heading">
            <ul class="list-inline">
                <li>{{Lang::choice('website_keywords.contact_us',\App::getLocale())}}</li>
            </ul>
        </div>
    </div>
</section>
<section class="middle">
    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif
    <div class="contact-us">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-xs-12 col-sm-8">
                    <div class="contact-us-form">
                        <h3 class="contact-heading"><strong>{{Lang::choice('website_keywords.get_in_touch',\App::getLocale())}}</strong></h3>
                        <p>{{Lang::choice('website_keywords.contact_form_msg',\App::getLocale())}}</p>
                        <form role="form" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group row">
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('name')) has-error @endif">
                                    <label for="exampleInputEmail1">{{Lang::choice('website_keywords.your_name',\App::getLocale())}}<sup>*</sup></label>
                                    <input type="text" class="form-control" name="name" value="{{old('name',$user_data['name'])}}"  placeholder="{{Lang::choice('website_keywords.your_name',\App::getLocale())}}">
                                    @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('email')) has-error @endif">
                                    <label for="for_email">{{Lang::choice('website_keywords.your_email',\App::getLocale())}} <sup>*</sup></label>
                                    <input type="email" class="form-control" name="email" value="{{old('email',$user_data['email'])}}" placeholder="{{Lang::choice('website_keywords.email',\App::getLocale())}}" >
                                    @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('phone')) has-error @endif">
                                    <label for="for_phone">{{Lang::choice('website_keywords.your_phone',\App::getLocale())}}</label>
                                    <input type="text" class="form-control" name="phone" value="{{old('phone')}}" placeholder="{{Lang::choice('website_keywords.Phone',\App::getLocale())}}">
                                    @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('phone') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                @if(count($contact_categories) > 0)
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('category')) has-error @endif">
                                    <label for="for_category">{{Lang::choice('website_keywords.choose_category',\App::getLocale())}}<sup>*</sup></label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">--{{Lang::choice('website_keywords.select',\App::getLocale())}}--</option>
                                        @foreach($contact_categories as $category)
                                        <option @if(old('category')==$category->id) selected="selected" @endif value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('category'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('category') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('subject')) has-error @endif">
                                    <label for="subject">{{Lang::choice('website_keywords.Subject',\App::getLocale())}} <sup>*</sup> </label>
                                    <input type="text" class="form-control" name="subject" value="{{old('subject')}}" placeholder="{{Lang::choice('website_keywords.Subject',\App::getLocale())}}">
                                    @if ($errors->has('subject'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-6 col-xs-12 col-sm-6 @if ($errors->has('attachment')) has-error @endif">
                                    <label for="email">{{Lang::choice('website_keywords.attach_file',\App::getLocale())}}</label>
                                    <input class="form-control remove-upload-details" name="attachment[]" multiple="multiple"  type="file" value="{{old('attachment')}}" >
                                    @if ($errors->has('attachment'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('attachment') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group @if ($errors->has('message')) has-error @endif">
                                <label for="exampleInputEmail1">{{Lang::choice('website_keywords.message',\App::getLocale())}}<sup>*</sup></label>
                                <textarea class="form-control" name="message" placeholder="{{Lang::choice('website_keywords.message',\App::getLocale())}}">{{old('message')}}</textarea>
                                @if ($errors->has('message'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                </span>
                                @endif
                            </div>
<!--                            <div class="form-group">
                                <label for="exampleInputEmail1">Are you human? Please type what is 3 plus 6 (word)?</label>
                                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="">
                            </div>-->
                            <button type="submit" class="btn btn-default send-message btn-lg">{{Lang::choice('website_keywords.send_message',\App::getLocale())}}</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-4 col-xs-12 col-sm-4">
                    <div class="contact-us-address">
                        <div class="addresss-section">
                            <h3 class="contact-heading"><strong>{{Lang::choice('website_keywords.Social',\App::getLocale())}}</strong></h3>
                            <p class="socila-links"><a target="_blank" href="{{GlobalValues::get('google-link')}}"><span class="g"><i class="fa fa-google-plus" aria-hidden="true"></i> </span></a> <a target="_blank" href="{{GlobalValues::get('facebook-link')}}"><span class="f"><i class="fa fa-facebook" aria-hidden="true"></i> </span></a> <a target="_blank" href="{{GlobalValues::get('twitter-link')}}"><span class="t" ><i class="fa fa-twitter" aria-hidden="true"></i> </span></a><a target="_blank" href="{{GlobalValues::get('pinterest-link')}}"><span class="pint" ><i class="fa fa-pinterest " aria-hidden="true"></i> </span></a><a target="_blank" href="{{GlobalValues::get('instagram-link')}}"><span class="inst" ><i class="fa fa-instagram" aria-hidden="true"></i> </span></a> </p>
                        </div>
                        <hr/>
<!--                        <div class="addresss-section">
                            <h3 class="contact-heading"><strong>{{Lang::choice('website_keywords.Phone',\App::getLocale())}}</strong></h3>
                            <div class="media">
                                <div class="media-left"><span> <i class="fa fa-phone" aria-hidden="true"></i> </span></div>
                                <div class="media-body">
                                    <p><span>{{GlobalValues::get('phone-no')}}</span></p>
                                    <p><span>{{GlobalValues::get('phone-no1')}}</span></p>
                                </div>
                            </div>
                        </div>-->
                    </div>
                   
                    <div class="addresss-section">
                        <h3 class="contact-heading"><strong>{{Lang::choice('website_keywords.Address',\App::getLocale())}}</strong></h3>
                        <div class="media">
                            <div class="media-left"><span> <i class="fa fa-map-marker" aria-hidden="true"></i> </span></div>
                            <div class="media-body">
                                <p> <span>{!! nl2br(e(GlobalValues::get('address'))) !!}</span></p>
                                <p><span></span></p>
                            </div>
                           
                        </div>
                        <div class="media">
                            <div class="media-left"><span> <i class="fa fa-phone" aria-hidden="true"></i> </span></div>
                           
                            <div class="media-body">
                                    <p><span>{{GlobalValues::get('phone-no')}}</span></p>
                             </div>
                        </div>
                         <hr/>
                          <div class="media">
                            <div class="media-left"><span> <i class="fa fa-map-marker" aria-hidden="true"></i> </span></div>
                            <div class="media-body">
                                <p> <span>{!! nl2br(e(GlobalValues::get('address1'))) !!}</span></p>
                                <p><span></span></p>
                            </div>
                           
                        </div>
                        <div class="media">
                            <div class="media-left"><span> <i class="fa fa-phone" aria-hidden="true"></i> </span></div>
                           
                            <div class="media-body">
                                    <p><span>{{GlobalValues::get('phone-no1')}}</span></p>
                             </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="addresss-section">
                     
                        <div class="addresss-section">
                            <h3 class="contact-heading"><strong>{{Lang::choice('website_keywords.email',\App::getLocale())}}</strong></h3>
                            <div class="media">
                                <div class="media-left"> <a href="mailto:{{GlobalValues::get('contact-email')}}"><span><i class="fa fa-envelope" aria-hidden="true"></i> </span> </a> </div>
                                <div class="media-body">
                                    <p><span>{{GlobalValues::get('contact-email')}}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
    </div>
</section>

@endsection