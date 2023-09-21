@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Add Loan</title>

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
                <a href="{{url('admin/loan-list')}}">Manage Loan</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Add Loan</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Add Loan
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" name="frm_loan_add"  id="frm_loan_add" role="form" action="" method="post" enctype="multipart/form-data">
                    
                    {!! csrf_field() !!}
                     <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                   
                                    <div class="form-group @if ($errors->has('driver_id')) has-error @endif">
                                        <label class="col-md-6 control-label">Select Driver<sup>*</sup></label>
                                        <div class="col-md-6"> 
                                            <select id="driver_id" name="driver_id">
                                            @foreach($drivers as $driver)
                                            <option value="{{$driver->user_id}}">{{$driver->first_name}} {{$driver->last_name}}</option>
                                            @endforeach
                                            </select>
                                            @if ($errors->has('driver_id'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('driver_id') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group @if ($errors->has('receipt_bp_type')) has-error @endif">
                                        <label class="col-md-6 control-label">Receipt BP Type<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="receipt_bp_type" value="{{old('receipt_bp_type')}}" />
                                            @if ($errors->has('receipt_bp_type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('receipt_bp_type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                               
                                    
                                    <div class="form-group @if ($errors->has('loan_amount')) has-error @endif">
                                        <label class="col-md-6 control-label">Loan Amount<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="loan_amount" value="{{old('loan_amount')}}" />
                                            @if ($errors->has('loan_amount'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('loan_amount') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group @if ($errors->has('intrest')) has-error @endif">
                                        <label class="col-md-6 control-label">Intrest<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="intrest" value="{{old('intrest')}}" />
                                            @if ($errors->has('intrest'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('intrest') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group @if ($errors->has('terms')) has-error @endif">
                                        <label class="col-md-6 control-label">Terms<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select name="terms">
                                                <option value="12">1 year</option>
                                                <option value="24">2 years</option>
                                                <option value="36">3 years</option>
                                                <option value="48">4 years</option>
                                                <option value="60">5 years</option>
                                            </select>
                                            
                                            @if ($errors->has('terms'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('terms') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    
                                    
                                          
                                    <div class="form-group">
                                        <div class="col-md-12">   
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
    .thumb-image {
        margin: 10px;
    }
</style>
@endsection