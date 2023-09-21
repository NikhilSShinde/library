@extends('layouts.app')

 @section('meta')
    <title>{{Lang::choice('website_keywords.become_a_star',\App::getLocale())}}</title>
 @endsection
 
@section('content')

<section class="registration-process board-lg-opt">
  <div class="container">
    <div class="row">
      <div class="board"> 
        <div class="board-inner ">
          <ul class="nav nav-tabs" id="myTab">
            <div class="liner"></div>
            <li> <a href="javascript:void(0);" data-toggle="tab" title="{{Lang::choice('website_keywords.personal_details',\App::getLocale())}}"> <span class="round-tabs one"> <i class="fa fa-user" aria-hidden="true"></i> </span> </a></li>
            <li class="active"><a href="javascript:void(0);" data-toggle="tab" title="{{Lang::choice('website_keywords.completed',\App::getLocale())}}"> <span class="round-tabs three"> <i class="fa fa-file-text-o" aria-hidden="true"></i> </span> </a> </li>
          </ul>
        </div>
        <div class="tab-content">
       
          <div class="tab-pane fade active in" id="messages">
            <h3 class="head text-center">{{Lang::choice('website_keywords.request_completed',\App::getLocale())}}</h3>
           <div class="message-section">
            <p class="narrow"> {{isset($msg)?$msg:''}}.
            </p>
            </div>
          
          </div>
          <div class="clearfix"></div>
        </div>
           <input type="hidden" name="file_name" id="file_name" value="{{url('download-pdf-file')}}/{{$file_name}}">

      </div>
    </div>
  </div>
</section>
<script>
       
    function startDownload()  
    {  
          var file_name=$("#file_name").val();  
            if(file_name!='')
            {
                window.location.href=file_name;
             // 
           }
    }  
    $(document).ready(function()
        {
           
            setTimeout('startDownload()', 2000); 
        });
   
      
 </script>
            
@endsection
