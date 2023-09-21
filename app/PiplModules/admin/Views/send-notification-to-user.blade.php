@extends(config("piplmodules.back-view-layout-location-payment"))

@section("meta")

<title>Send Notification to User's</title>

@endsection

@section('content')

<div class="page-content-wrapper">
		<div class="page-content">
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashbard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="javascript:void(0);">Create notification</a>
					
				</li>
                        </ul>
       @if (session('sent-msg'))
          <div class="alert alert-success">
                {{ session('sent-msg') }}
          </div>
         @endif
      
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Create notification message
                        </div>

             </div>
             <div class="portlet-body form">
  	  <form class="form-horizontal" id="create_notification" name="create_notification" role="form" action="{{url('admin/send-notification-to-user')}}" method="post">
                {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                       <div class="col-md-12">    
                        <div class="col-md-8">
                        @if(Auth::user()->userInformation->user_type=='1')
                          
                        <div class="form-group">
                          <label class="col-md-6 control-label">Choose Country</label>
                            <div class="col-md-6">
                                <select name="country" id="country" onchange="getAllStates(this.value)" class="form-control">
                                    <option value="" selected="">--Select--</option>
                                    @foreach($countries as $country)
                                        @if($country->id!='17')
                                        <option value="{{$country->id}}">{{$country->name}}</option>
                                       @endif 
                                    @endforeach
                                </select>
                           
                            </div>     
                        </div>     
                       @endif 
                        <div class="form-group">
                           <label class="col-md-6 control-label">Send to?<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="radio" name="type" @if(old('type')=='0' || old('type')=='') checked="" @endif onchange='showAutocomplete(this.value);' value='0'   id="type">Specific user
                               <input type="radio" name="type" @if(old('type')=='1') checked="" @endif value='1' onchange='showAutocomplete(this.value);'   id="type">All Customers
                               <input type="radio" name="type" @if(old('type')=='2') checked="" @endif value='2' onchange='showAutocomplete(this.value);' id="type">All Drivers
                               <input type="radio" name="type" @if(old('type')=='3') checked="" @endif value='3' onchange='showAutocomplete(this.value);'   id="type">All Users(Driver+Customers)
                              
                          </div>
                       
                        </div>
                        <div class="form-group"  id='autocomplete_div' style="">
                           <label class="col-md-6 control-label">User<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" name="user" class="form-control" placeholder="Start typing to auto suggest (min 3 characters)..."  id="user">
                               <input type="hidden" name="user_id" class="form-control"  id="user_id">
                                @if ($errors->has('user_id'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('user_id') }}</strong>
                                        </span>
                                @endif
                          </div>
                       
                        </div>    
                         <div class="form-group">
                          <label class="col-md-6 control-label">Title<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input name="title" class="form-control" placeholder="Title" value="" type="text">
                                @if ($errors->has('title'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('title') }}</strong>
                              </span>
                              @endif
                           </div> 
                         </div>
                         <div class="form-group">
                          <label class="col-md-6 control-label">Message<sup>*</sup></label>
                           <div class="col-md-6">     
                               <textarea name="message" class="form-control" placeholder="Type a Message" id="message"></textarea>
                                @if ($errors->has('message'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('message') }}</strong>
                              </span>
                              @endif
                           </div> 
                         </div>    
                            
                        <div class="form-group">
                         <div class="col-md-12">   
                             <button onclick="return submitForm();" type="submit" id="submit" class="btn btn-primary  pull-right">Submit</button>
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
 $( function() {
    $( "#user" ).autocomplete({
      source: "{{url('/admin/getAllStarMateUsers')}}",
      minLength: 3,
      select: function( event, ui ) {
          
        $("#user_id").val(ui.item.id );
     
      }
    });
 });
 function showAutocomplete(value)
 {
     if(value=='0')
     {
         $("#autocomplete_div").show();
     }else{
          $("#autocomplete_div").hide();
     }
 }
 function submitForm()
 {
    if($("#message").val()!='' && $("#title").val()!='')
    {
        
    
       if($('input[name="type"]:checked').val()==0)
       {
            if(parseInt($("#user_id").val())>0)
            {
                return true;
            }else{
                alert("Please choose a user to send the notification");
                return false;
            }
        }else{
            return true;
        }
    }else{
          return true;
    }
    
     
 }
  </script>
 @endsection
  