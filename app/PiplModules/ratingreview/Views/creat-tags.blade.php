@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Rating Tags</title>

@endsection

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('/admin/rating-review/tags-list')}}">Manage Rating Tags</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Create Rating Tags</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Create Rating Tags
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Rating : </label>
                                        <div class="container">
                                            <div class="row lead">
                                                <div id="hearts-existing" class="starrr" data-rating='1'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('title')) has-error @endif">
                                        <label class="col-md-6 control-label">Title :<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="title" value="{{old('title')}}"/>
                                            @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('title') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('description')) has-error @endif">
                                        <label class="col-md-6 control-label">Description :<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <textarea class="form-control" name="description">{{old('description')}}</textarea>
                                            @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12"> 
                                            <input type="hidden" name="star_counter" id="star_counter" value="1">
                                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<style>
    .submit-btn{
        padding: 10px 0px 0px 18px;
    }

    #hearts { color: #ee8b2d;}
    #hearts-existing { color: #ee8b2d;}

    .glyphicon{
        display: inline-block;
        font-size: 22px;
        line-height: 14px;
        margin-left: 14px;
        margin-top: 12px;
        cursor: pointer;
    }
</style>
<input type="hidden" id="is_readonly" value="false">
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<script>
$(document).ready(function() {
    $('#hearts-existing').on('starrr:change', function(e, value) {
        $('#star_counter').val(value);
    });
});
</script>
@endsection