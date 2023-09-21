@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Coupons Details</title>

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
                <a href="javascript:void(0);">View Coupons</a>
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
                <form class="form-horizontal" role="form" action="" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Coupon Code : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$arr_coupun_code_details->code}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Title:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$arr_coupun_code_details->title}}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Start Date:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                
                                                {{date('Y-m-d',strtotime($arr_coupun_code_details->start_date))}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">End Date:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{date('Y-m-d',strtotime($arr_coupun_code_details->end_date))}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Usage Time:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$arr_coupun_code_details->usage_time}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Country:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$arr_coupun_code_details->getCountry->name}}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('type')) has-error @endif">
                                        <label class="col-md-6 control-label">Type :</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                @if($arr_coupun_code_details->type == '0')
                                                Percentage
                                                @elseif($arr_coupun_code_details->type == '1')
                                                Fixed
                                                @else
                                                Conditional
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('amount')) has-error @endif">
                                        <label class="col-md-6 control-label">Amount:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{$arr_coupun_code_details->discount}}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Allow for multiuse:</label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{($arr_coupun_code_details->allow_user_for_multi_use == '0') ? 'No' : 'Yes'}}
                                            </span>
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
@endsection