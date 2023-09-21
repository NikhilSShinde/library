@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Edit Rating Tags</title>

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
                <a href="javascript:void(0);">Edit Rating Tags</a>
            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Edit Rating Tags
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
                                                <div id="hearts-existing" class="starrr" data-rating='{{$rating_data->rating_star_no}}'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('title')) has-error @endif">
                                        <label class="col-md-6 control-label">Title<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="title" value="{{$rating_data_lang->ques_title}}"/>
                                            @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('title') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('description')) has-error @endif">
                                        <label class="col-md-6 control-label">Description<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <textarea class="form-control" name="description">{{$rating_data_lang->ques_desc}}</textarea>
                                            @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
<!--                                    <div class="form-group @if ($errors->has('status')) has-error @endif">
                                        <label class="col-md-6 control-label">Status<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="status">
                                                <option value="">-- Status -- </option>>
                                                <option value="1" @if(old('status',$rating_data->status)==1) selected="selected" @endif>Active</option>
                                                <option value="0" @if(old('status',$rating_data->status)==0) selected="selected" @endif>Inactive</option>
                                            </select>
                                            @if ($errors->has('status'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('status') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>-->

                                    <div class="form-group">
                                        <div class="col-md-12">   
                                            
                                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update</button>
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
<input type="hidden" id="is_readonly" value="true">
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<script>
$(document).ready(function() {
    $('#hearts-existing').on('starrr:change', function(e, value) {
        $('#star_counter').val(value);
    });
});
</script>
@endsection