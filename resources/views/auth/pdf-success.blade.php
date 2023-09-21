
        <script type="text/javascript"  src="{{url('public/media/front/js/jquery-v2.1.3.js')}}"></script>
<input type="hidden" name="file_name" id="file_name" value="{{url('download-pdf-file-admin')}}/{{$file_name}}">

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