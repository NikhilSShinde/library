@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Add Vehicle</title>

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
                <a href="{{url('admin/vehicle-list')}}/{{$user_id}}">Manage Vehicle</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Add Vehicle</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Add Vehicle
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" name="frm_vehicle_add"  id="frm_vehicle_add" role="form" action="" method="post" enctype="multipart/form-data">
                    <input type='hidden'  name='type' id='type' value='0'>
                    {!! csrf_field() !!}
                     <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group @if ($errors->has('vehicle_name')) has-error @endif" style='@if($user_id=='0') display:none @endif'>
                                        <div class="col-md-6"> 
                                            
                                             <button type="button" onclick="showHideAddNewExisting('add_new_row','select_from_existing')" id="submit" class="btn btn-primary  pull-right">Add New</button>
                                            </div>
                                            <div class="col-md-6">  
                                             <button type="button" onclick="showHideAddNewExisting('select_from_existing','add_new_row')" id="submit" class="btn btn-primary  pull-right">Select From Existing</button>
                                         </div>
                                        </div>
                                    <div id='add_new_row' style='@if (!($errors->has('vehicle_name')||$errors->has('plate_number')||$errors->has('vehicle_desc')||$errors->has('status'))) @if(isset($user_id) && $user_id!='0') display:none @endif  @endif'>
                                    <div class="form-group @if ($errors->has('vehicle_name')) has-error @endif">
                                        <label class="col-md-6 control-label">Make/Model<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="vehicle_name" value="{{old('vehicle_name')}}" />
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
                                            <input class="form-control" name="plate_number" value="{{old('plate_number')}}" />
                                            @if ($errors->has('plate_number'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('plate_number') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                      <div class="form-group @if ($errors->has('financial_type')) has-error @endif">
                                        <label class="col-md-6 control-label">Finance Type<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input checked="" name="financial_type" value="0" type='radio'>Owned
                                            <input name="financial_type" value="1" type='radio'>Finance
                                            @if ($errors->has('financial_type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('financial_type') }}</strong>
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
                                                     <option value='{{$i}}'>{{$i}}</option>
                                                 <?php }?>    
                                            </select>
                                            @if ($errors->has('year_manufacture'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('year_manufacture') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>     
                                    <div class="form-group @if ($errors->has('vehicle_desc')) has-error @endif">
                                        <label class="col-md-6 control-label">Description<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <textarea class="form-control" name="vehicle_desc" >{{old('vehicle_desc')}}</textarea>
                                            @if ($errors->has('vehicle_desc'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('vehicle_desc') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Vehicle Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="vehicle_image" id="vehicle_image">
                                            <div id="image-holder" class="thumb-image"></div>
                                        </div>
                                    </div>
<!--                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Plate Number Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="plate_number_image" id="plate_number_image">
                                            <div id="image-holder-number" class="thumb-image-number"></div>
                                        </div>
                                    </div>-->
                                    <div class="form-group @if ($errors->has('status')) has-error @endif">
                                        <label class="col-md-6 control-label">Status<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class="form-control" name="status">
                                                <option value="">-- Status -- </option>>
                                                <option value="1" @if(old('status')==1) selected="selected" @endif>Active</option>
                                                <option value="0" @if(old('status')==0) selected="selected" @endif>Inactive</option>
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
                                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Add</button>
                                        </div>
                                    </div>
                                  </div> 
                                    
                                    <div id='select_from_existing' style='@if (!$errors->has('vehicle_list'))  display:none @endif'>
                                       <div class="form-group @if ($errors->has('vehicle_list')) has-error @endif">
                                           @if(count($user_vehicles)>0)
                                                <label class="col-md-6 control-label">Select A vehicle<sup>*</sup></label>
                                                <div class="col-md-6">    

                                                 <select class="form-control" name='vehicle_list' id='vehicle_list'>
                                                     <option value=''>--Select--</option>
                                                        @foreach($user_vehicles as $vehicle_list)
                                                            <option value="{{$vehicle_list->id}}">{{$vehicle_list->vehicle_name}}/{{$vehicle_list->plate_number}}</option>
                                                        @endforeach
                                                 </select>
                                                 @if ($errors->has('vehicle_list'))
                                                  <span class="help-block">
                                                     <strong class="text-danger">{{ $errors->first('vehicle_list') }}</strong>
                                                  </span>
                                                 @endif
                                                </div>
                                        </div>
                                        <div class="form-group">
                                          <div class="col-md-12">   
                                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Add</button>
                                          </div>
                                         </div>
                                         @else
                                          No vehicle added yet by you!!
                                         @endif  
                                           
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
                var image_holder = $("#image-holder-number");
                image_holder.empty();
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("<img />", {
                        "src": e.target.result,
                        "width": '200',
                        "hight": '200',
                        "class": "thumb-image-number"
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
    
    function showHideAddNewExisting(id,id1)
    {
        $("#"+id).show();
        $("#"+id1).hide();
        
        if(id=='select_from_existing')
        {
            $("#type").val('1');
        }else{
             $("#type").val('0');
         }
    }
</script>
@endsection