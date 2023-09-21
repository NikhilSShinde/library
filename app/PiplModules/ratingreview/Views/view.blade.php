@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Rating And Review Details</title>

@endsection

@section('content')
<input type="hidden" id="is_readonly" value="true">
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('/admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
               @if($user_id!=0) 
                <a href="{{url('/admin/rating-review/list/'.$user_id)}}">Manage Rating And Review</a>
             @else   
                <a href="{{url('/admin/rating-review/list')}}">Manage Rating And Review</a>
             @endif   
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Rating And Review Details</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>View
                </div>
            </div>
            <div class="portlet-body form">
                {!! csrf_field() !!}
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">    
                            <div class="col-md-6">  
                                <!--<div class="col-md-4">-->  
                                <h4><b>Review Details : </b></h4>
                                <!--</div>-->
                                <div class="col-md-12">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Rating : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                <div id="hearts-existing" class="starrr" data-rating='{{$rating_details->rating}}'></div>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Unique Id : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$rating_details->getOrderDetails->order_unique_id}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">From User : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$rating_details->getFromUserDetails->first_name . ' ' . $rating_details->getFromUserDetails->last_name}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">To User : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$rating_details->getToUserDetails->first_name . ' ' . $rating_details->getToUserDetails->last_name}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Review : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$rating_details->review}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">  
                                <h4><b>Tags Details : </b></h4>
                                <div class="col-md-12">
                                    @if(count($question_data))
                                    @foreach($question_data as $tags)
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Title : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$tags->ques_title}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Description : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$tags->ques_desc}}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    Sorry! Tags not foundation
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<style>
    #hearts { color: #ee8b2d;}
    #hearts-existing { color: #ee8b2d;}

    .glyphicon{
        display: inline-block;
        font-size: 22px;
        line-height: 14px;
        margin-left: 5px;
    }

    .help-block {
        margin-bottom: 10px;
        margin-top: 10px;
    }
</style>
@endsection