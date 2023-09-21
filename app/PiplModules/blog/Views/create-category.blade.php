@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Category</title>

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
					<a href="{{url('admin/blog-categories')}}">Manage Categories</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Create Blog Category</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Create A Category
                        </div>

             </div>
             <div class="portlet-body form">
                 <form class="form-horizontal" role="form" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
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
                          <label class="col-md-6 control-label">URL<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                            <input name="slug" type="text" class="form-control" id="slug" value="{{old('slug')}}">
                            @if ($errors->has('slug'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('slug') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                            
                       <div class="form-group">
                          <label class="col-md-6 control-label">Parent Category</label>
                       
                            <div class="col-md-6">     
                             <select class="form-control" name="parent_id" value="{{old('parent_id')}}">
                                <option value="0">No Parent</option>
                                 @foreach($tree as $ls_category)
                                 <option @if (old('parent_id')==$ls_category->id) selected="selected" @endif value="{{$ls_category->id}}">{{$ls_category->display}}</option>
                                 @endforeach
                             </select>
                                @if ($errors->has('name')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('parent_id') }}</strong> </span> @endif </div>
        
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
        <style>
            .submit-btn{
                padding: 10px 0px 0px 18px;
            }
        </style>
 @endsection