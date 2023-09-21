$("document").ready(function()
{
$.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
});
function afterRequestComplete()
{
    // Ajax loaded Do post back functions
     $(".checkboxes").on("click",function()
    { 
        var flag=1;
        $(".checkboxes").each(function()
        { 
            if(!$(this).is(":checked"))
            {
                flag=0;
            }
            
        });
        
        if(flag===0)
        { 
             //$("#select_all_delete").parent().removeClass("checked");
             $("#select_all_delete").prop("checked",false);
        }else{
            // $("#select_all_delete").parent().addClass("checked");
             $("#select_all_delete").prop("checked",true);
        }
    });
}
    
$('#select_all_delete').change(function()
{

    $(".checkboxes").each(function()
    { 
        if($("#select_all_delete").is(":checked"))
        {  
           $(this).prop("checked",true);
        }else{
          $(this).prop("checked",false);
       }
      
    });
});

function deleteAll(path)
{
    
       var flag=0;
        $(".checkboxes").each(function()
        { 
            if($(this).is(":checked"))
            {
                flag=1;
            }
            
        });
        
        if(flag===0)
        { 
            alert("Please select atleast one record to delete?");
        }else{
            if(confirm("Do you really want to proceed with deleting of selected records?"))
            {
                $(".checkboxes").each(function()
                { 
                    if($(this).is(":checked")!='')
                    {
                      $.ajax(
                      {
                          url:path+'/'+($(this).val()),
                          type: 'DELETE',
                          data: {},
                          'dataType': 'json',
                          success:function(data)
                          {
                            if(data.success=="1")
                            {
                              
                              
                            }
                           
                            
                          }
                          
                      });
                    }

                });
                 alert("Selected records has been deleted successfully.");
                  window.location.reload();
            }
        }
}
