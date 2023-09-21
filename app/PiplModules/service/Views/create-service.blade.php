@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Service</title>

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
                <a href="{{url('admin/services-list')}}">Manage Services</a>
                <i class="fa fa-circle"></i>

            </li>
            <li>
                <a href="javascript:void(0);">Create Service</a>

            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Create A Service
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post"  enctype="multipart/form-data">

                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Category<sup>*</sup></label>                       
                                        <div class="col-md-6">    
                                            <select onchange='getAllServices(this.value)' class="form-control" name="category" id="category">
                                                @foreach($categories as $category)
                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Main Service</label>                       
                                        <div class="col-md-6" >    
                                            <select class="form-control" name="parent_id" id='services_list'>
                                              <option value="0">--No Parent--</option>
                                               
                                            </select>
                                          
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Name<sup>*</sup></label>

                                        <div class="col-md-6">     
                                            <input name="name" type="text" class="form-control" id="name" value="{{old('name')}}">
                                            @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Service Image<sup>*</sup></label>
                                        <div class="col-md-6"> 
                                            <input type="file" class="form-control" name="service_image" id="service_image">
                                            @if ($errors->has('service_image'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('service_image') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Service Selected Image<sup>*</sup></label>
                                        <div class="col-md-6"> 
                                            <input type="file" class="form-control" name="service_selected_image" id="service_selected_image">
                                            @if ($errors->has('service_selected_image'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('service_selected_image') }}</strong>
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
</style>
 <script>
            
        function getAllServices(category_id)
        {
            if(category_id!='' && category_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/service_details/get-service-by-category')}}/"+category_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#services_list").html(data);

                   }

                });
            }
        }
 </script>
        
@endsection