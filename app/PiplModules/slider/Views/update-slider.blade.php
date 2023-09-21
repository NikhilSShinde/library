@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Updated Tutorial Image</title>

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
                <a href="{{url('admin/tutorials-list')}}">Manage Tutorials Images</a>
                <i class="fa fa-circle"></i>

            </li>
            <li>
                <a href="javascript:void(0);">Updated Tutorials Image</a>

            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Updated A Tutorials Image
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" action="" method="post" >

                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Title<sup>*</sup></label>

                                        <div class="col-md-6">     
                                            <input name="title" type="text" class="form-control" id="title" value="{{old('title',$sliderData->title)}}">
                                            @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('title') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Type<sup>*</sup></label>
                                        <div class="col-md-6"> 
                                            <select class="form-control" name="type">
                                                <option value="">Choose Type</option>
                                                <option value="0" @if($sliderData->type=="0") selected="" @endif>Driver</option>
                                                <option value="1" @if($sliderData->type=="1") selected="" @endif>Customer</option>
                                            </select>                                            
                                            @if ($errors->has('type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Choose Language<sup>*</sup></label>
                                        <div class="col-md-6"> 
                                            <select class="form-control" name="locale">
                                                <option value="">Choose Language</option>
                                                @if(count(config("translatable.locales_to_display")))
                                                @foreach(config("translatable.locales_to_display") as $locale => $locale_full_name)
                                                <option value="{{$locale}}" @if($sliderData->locale==$locale) selected="" @endif>{{$locale_full_name}}</option>
                                                @endforeach
                                                @endif       
                                            </select>                                            
                                            @if ($errors->has('locale'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('locale') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Image</label>

                                        <div class="col-md-6">   
                                            <input name="value" type="file" class="form-control" id="value">                           
                                            @if ($errors->has('value'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('value') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                        <div class="img-display">
                                            @if($sliderData->image_path)
                                            <img width="260" src="{{asset('storageasset/slider-images/'.$sliderData->image_path)}}">
                                            @endif
                                        </div>
                                    </div>

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
<style>
    .submit-btn{
        padding: 10px 0px 0px 18px;
    }
    .img-display{
        text-align: center;
        margin: 100px 0px 0px 300px;
    }
</style>
@endsection