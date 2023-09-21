@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Faq</title>

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
					<a href="{{url('admin/faqs')}}">Manage Faq's</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Create Faq</a>
					
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
                 <form class="form-horizontal" role="form" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group @if ($errors->has('question')) has-error @endif">
                          <label class="col-md-6 control-label">Question<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                           <input class="form-control" name="question" value="{{old('question')}}" />
                             @if ($errors->has('question'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('question') }}</strong>
                                    </span>
                                @endif
                          </div>
                       
                      </div>
                       <div class="form-group @if ($errors->has('answer')) has-error @endif">
                          <label class="col-md-6 control-label">Answer<sup>*</sup></label>
                         
                            <div class="col-md-6">     
                                <textarea class="form-control" name="answer" >{{old('answer')}}</textarea>
                            @if ($errors->has('answer'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('answer') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                      <div class="form-group @if ($errors->has('faq_type')) has-error @endif">
                          <label class="col-md-6 control-label">Display On?<sup>*</sup></label>
                            <div class="col-md-6">     
                                <select name="faq_type" class="form-control">
                                    <option value="2" selected>BOTH</option>
                                    <option value="0">WEB</option>
                                    <option value="1">APP</option>                                     
                                </select>
                            @if ($errors->has('faq_type'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('faq_type') }}</strong>
                              </span>
                              @endif
                          </div>
                      </div>      
                   @if(count($tree)>0 && empty($locale))
                     <div class="form-group @if ($errors->has('category')) has-error @endif">
                        <label for="category" class="col-md-6 control-label">Choose Category </label>
                      <div class="col-md-6">     
                        <select name="category" class="form-control">
                           <option value="0">No Category</option>
                                    @foreach($tree as $ls_category)
                                    <option  value="{{$ls_category->id}}">{{$ls_category->display}}</option>
                                    @endforeach
                        </select>
                            @if ($errors->has('category'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('category') }}</strong>
                                </span>
                            @endif
                        </div>
                        </div>
                        @endif           
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
 @endsection