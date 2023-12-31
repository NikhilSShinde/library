@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update preferred language Info</title>

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
					<a href="{{url('admin/preferred-language/list')}}">Manage preferred language</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update preferred language</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Preferred language Info
                        </div>

             </div>
             <div class="portlet-body form">
              <form class="form-horizontal" role="form" id="frm_language_update" name="frm_language_update" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                     <div class="col-md-12">    
                      <div class="col-md-8">  
                           <div class="form-group">
                                <label class="col-md-6 control-label">Name<sup>*</sup></label>
                                  <div class="col-md-6">     
                                  <input name="name" type="text" class="form-control" id="name" value="{{old('name',$country_info->name)}}">
                                  @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>

                            </div>
                         
                          
  
                 </div>
                
                            
  
                 </div>     
                    <div class="col-md-8">
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
        </style>
 @endsection