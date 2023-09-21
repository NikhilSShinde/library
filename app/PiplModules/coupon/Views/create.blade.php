@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Coupon</title>

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
                <a href="{{url('admin/coupons')}}">Manage Coupons</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Create Coupons</a>
            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Create Faq
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group @if ($errors->has('coupon_code')) has-error @endif">
                                        <label class="col-md-6 control-label">Coupon Code<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="coupon_code" value="{{$rand_string}}" readonly />
                                            @if ($errors->has('coupon_code'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('coupon_code') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('title')) has-error @endif">
                                        <label class="col-md-6 control-label">Title<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="title" value="{{old('title')}}" >
                                            @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('title') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('country_code')) has-error @endif">
                                        <label class="col-md-6 control-label">Country Code<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="country_code">
                                                <option value="">-- Selecte Type -- </option>
                                                @foreach($countires as $codes)
                                                <option value="{{$codes->id}}" @if(old('country_code',$codes->id)==$codes->id) selected="selected" @endif>{{$codes->name}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('country_code'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('country_code') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('start_date')) has-error @endif">
                                        <label class="col-md-6 control-label">Start Date<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="start_date" id="start_date" readonly value="{{old('start_date')}}">
                                            @if ($errors->has('start_date'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('start_date') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('end_date')) has-error @endif">
                                        <label class="col-md-6 control-label">End Date<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="end_date" id="end_date" readonly value="{{old('end_date')}}">
                                            @if ($errors->has('end_date'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('end_date') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('usage_time')) has-error @endif">
                                        <label class="col-md-6 control-label">Usage Time<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="usage_time" id="usage_time" value="{{old('usage_time')}}">
                                            @if ($errors->has('usage_time'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('usage_time') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('type')) has-error @endif">
                                        <label class="col-md-6 control-label">Type<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="type">
                                                <option value="">-- Selecte Type -- </option>
                                                <option value="1" @if (old('type','1')==1) selected="selected" @endif>Fixed</option>
                                                <option value="0" @if (old('type','0')==0) selected="selected" @endif>Percentage</option>
                                                <option value="2" @if (old('type','2')==2) selected="selected" @endif>Conditional</option>
                                            </select>
                                            @if ($errors->has('type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('amount')) has-error @endif">
                                        <label class="col-md-6 control-label">Amount<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="amount" value="{{old('amount')}}" >
                                            @if ($errors->has('amount'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('amount') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('allow_user_for_multi_use')) has-error @endif">
                                        <label class="col-md-6 control-label">Allow for multiuse<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="allow_user_for_multi_use">
                                                <option value="">-- Allow for multiuse -- </option>>
                                                <option value="1" @if(old('allow_user_for_multi_use','1')==1) selected="selected" @endif>Yes</option>
                                                <option value="0" @if(old('allow_user_for_multi_use','0')==0) selected="selected" @endif>No</option>
                                            </select>
                                            @if ($errors->has('allow_user_for_multi_use'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('allow_user_for_multi_use') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12">   
                                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Create</button>
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
</style>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
d = new Date();
$(function() {
    var dateFormat = "yy-mm-dd",
            from = $("#start_date").datepicker({
        minDate: new Date(d.getFullYear(), d.getMonth(), d.getDate()),
        dateFormat: 'yy-mm-dd',
        changeMonth: false,
        numberOfMonths: 1
    }).on("change", function() {
        to.datepicker("option", "minDate", getDate(this));
    }),
            to = $("#end_date").datepicker({
        minDate: new Date(d.getFullYear(), d.getMonth(), d.getDate()),
        changeMonth: false,
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1
    }).on("change", function() {
        from.datepicker("option", "maxDate", getDate(this));
    });

    function getDate(element) {
        var date;
        try {
            date = $.datepicker.parseDate(dateFormat, element.value);
        } catch (error) {
            date = null;
        }
        return date;
    }
});
</script>
@endsection