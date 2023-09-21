@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Vehicle</title>

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
                <a href="{{url('admin/vehicle-list')}}/{{$arr_vehicle->user_id}}">Manage Vehicle</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Update Vehicle</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Update Vehicle
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" name="frm_vehicle_update"  id="frm_vehicle_update" role="form" action="" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group @if ($errors->has('vehicle_name')) has-error @endif">
                                        <label class="col-md-6 control-label">Make/Model<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="vehicle_name" value="{{old('vehicle_name',$arr_vehicle->vehicle_name)}}" />
                                            @if ($errors->has('vehicle_name'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('vehicle_name') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('plate_number')) has-error @endif">
                                        <label class="col-md-6 control-label">Plate Number<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="plate_number" value="{{old('plate_number',$arr_vehicle->plate_number)}}"  />
                                            @if ($errors->has('plate_number'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('plate_number') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                     <div class="form-group @if ($errors->has('year_manufacture')) has-error @endif">
                                        <label class="col-md-6 control-label">Manufacture Year<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class='form-control' name='year_manufacture' id='year_manufacture'>
                                                <option value=''>--Select--</option>
                                                 <?php for($i=1980;$i<=date('Y');$i++)
                                                 {?>
                                                     <option value='{{$i}}' @if($arr_vehicle->year_manufacture==$i) selected @endif>{{$i}}</option>
                                                 <?php }?>    
                                            </select>
                                            @if ($errors->has('year_manufacture'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('year_manufacture') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('financial_type')) has-error @endif">
                                        <label class="col-md-6 control-label">Finance Type<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input checked="" name="financial_type" @if($arr_vehicle->financial_type=='0') checked @endif value="0" type='radio'>Owned
                                            <input name="financial_type" value="1"  @if($arr_vehicle->financial_type=='1') checked @endif type='radio'>Finance
                                            @if ($errors->has('financial_type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('financial_type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div> 
                                    <div class="form-group @if ($errors->has('vehicle_desc')) has-error @endif">
                                        <label class="col-md-6 control-label">Description<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <textarea class="form-control" name="vehicle_desc" >{{old('vehicle_desc',$arr_vehicle->vehicle_desc)}}</textarea>
                                            @if ($errors->has('vehicle_desc'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('vehicle_desc') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-grou">
                                        <label class="col-md-6 control-label">Vehicle Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="vehicle_image" id="vehicle_image">
                                            <div id="image-holder" class="thumb-image"><img src="{{asset("storageasset/vehicle-images/".$arr_vehicle->vehicle_image)}}" onerror=src="{!!asset("storageasset/1481886669.png")!!}" class="thumb-image" width="200" height="200"></div>
                                        </div>
                                    </div>
<!--                                    <div class="form-grou">
                                        <label class="col-md-6 control-label"> Number Plate Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="plate_number_image" id="plate_number_image">
                                            <div id="image-number-holder" class="thumb-number-image"><img src="{{asset("storageasset/vehicle-number-images/".$arr_vehicle->plate_number_image)}}" onerror=src="{!!asset("storageasset/1481886669.png")!!}" class="thumb-image" width="200" height="200"></div>
                                        </div>
                                    </div>-->
                                    <div class="form-group @if ($errors->has('status')) has-error @endif">
                                        <label class="col-md-6 control-label">Status<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="status">
                                                <option value="">-- Status -- </option>>
                                                <option value="1" @if(old('status',$arr_vehicle->status)==1) selected="selected" @endif>Active</option>
                                                <option value="0" @if(old('status',$arr_vehicle->status)==0) selected="selected" @endif>Inactive</option>
                                            </select>
                                            @if ($errors->has('status'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('status') }}</strong>
                                            </span>
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
</div>
<style>
    .submit-btn{
        padding: 10px 0px 0px 18px;
    }
    .thumb-image {
        margin: 10px;
    }
</style>
<script>
    $("#plate_number_image").change(function() {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(this).val('');
            alert("Only formats are allowed : " + fileExtension.join(', '));
        } else {
            if (typeof (FileReader) != "undefined") {
                var image_holder = $("#image-number-holder");
                image_holder.empty();
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("<img />", {
                        "src": e.target.result,
                        "width": '200',
                        "hight": '200',
                        "class": "thumb-number-image"
                    }).appendTo(image_holder);
                }
                image_holder.show();
                reader.readAsDataURL($(this)[0].files[0]);
                $(this).prev().css('display', 'none')
            } else {
                alert("This browser does not support FileReader.");
            }
        }
    });
       $("#vehicle_image").change(function() {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(this).val('');
            alert("Only formats are allowed : " + fileExtension.join(', '));
        } else {
            if (typeof (FileReader) != "undefined") {
                var image_holder = $("#image-holder");
                image_holder.empty();
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("<img />", {
                        "src": e.target.result,
                        "width": '200',
                        "hight": '200',
                        "class": "thumb-image"
                    }).appendTo(image_holder);
                }
                image_holder.show();
                reader.readAsDataURL($(this)[0].files[0]);
                $(this).prev().css('display', 'none')
            } else {
                alert("This browser does not support FileReader.");
            }
        }
    });
</script>
@endsection