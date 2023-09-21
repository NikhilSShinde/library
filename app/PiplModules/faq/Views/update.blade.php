@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Faq</title>

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
					<a href="javascript:void(0);">Update Faq</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Faq
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
                           <input class="form-control" name="question" value="{{old('question',$faq->question)}}" />
                             @if ($errors->has('question'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('question') }}</strong>
                                    </span>
                                @endif
                          </div>
                       
                      </div>
                       <div class="form-group @if ($errors->has('answer')) has-error @endif">
                          <label class="col-md-6 control-label">Answer<sup>*</sup></label>
                          @if ($errors->has('question'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('answer') }}</strong>
                                    </span>
                             @endif
                            <div class="col-md-6">     
                                <textarea class="form-control" name="answer" >{{old('answer',$faq->answer)}}</textarea>
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
                                    <option value="2" @if($faq_main->faq_type=='2') selected @endif>BOTH</option>
                                    <option value="0" @if($faq_main->faq_type=='0') selected @endif>WEB</option>
                                    <option value="1" @if($faq_main->faq_type=='1') selected @endif>APP</option>                                     
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
                                    <option @if (old('category',$category)==$ls_category->id) selected="selected" @endif value="{{$ls_category->id}}">{{$ls_category->display}}</option>
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
                      
                       
                      </div>
                      <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update</button>
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
 @endsection