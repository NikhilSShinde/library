jQuery(document).ready(function() 
{
    /* landing Form Validation Start */
   jQuery("#admin_login").validate({
    
        errorClass: 'text-danger',
        rules: {
           
            email: 
            {
                required: true,
                email:true
                
            },
            password: 
            {
                required: true,
                minlength:6
            }
           
        },
        messages: {
            email:{
                required: "Please enter email.",
                email: "Please enter valid email."
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            }   
            
        },
        submitHandler: function(form) {
          
            form.submit();
        }
    });
   jQuery("#create_notification").validate({
    
        errorClass: 'text-danger',
        rules: {
           
            user: 
            {
                required: true
                
            },
            title: 
            {
                required: true
            },
            message: 
            {
                required: true
            }
           
        },
        messages: {
            user:{
                required: "Please choose a user."
            },
            title: {
                required: "Please enter the tilte."
            },  
            message: {
                required: "Please enter a message."
            }   
              
            
        },
        submitHandler: function(form) {
          
            form.submit();
        }
    });
   jQuery("#frm_admin_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },

                   
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
           
                    
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
   jQuery("#frm_admin_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    user_status: 
                    {
                        required: true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },

                   
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           user_status:{
                required: "Please select status.",
           },
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
           
                    
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
   jQuery("#frm_admin_update_email").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    confirm_email: 
                    {
                        required: true,
                        email:true,
                        equalTo:"#email"
                    },
                    
                },
        messages: {
          
            
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
            confirm_email:{
                required: "Please enter confirm email.",
                email: "Please enter valid email.",
                remote:"This email is already taken",
                equalTo:"Confirm email does not match"
            }
                    
            
        }
    });
   jQuery("#frm_admin_update_password").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    new_password: 
                    {
                        required: true,
                        minlength:6
                    },
                    confirm_password: 
                    {
                        required: true,
                        equalTo:"#new_password"
                    },
                    
                },
        messages: {
          
            
            new_password:{
                required: "Please enter password.",
            },
            confirm_password:{
                required: "Please enter confirm password.",
                
                equalTo:"Confirm password does not match"
            }
                    
            
        }
    }); 
   jQuery("#add_company").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },

                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
           
            state: {
                 required: "Please select state.",
            },
            city: {
                 required: "Please select an city.",
            },            
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
   jQuery("#add_agent").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },
                    mobile_code: {
                        required: true
                    },

                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            mobile_code: {
                required: "Please select mobile/country code."
            },
           
            state: {
                 required: "Please select state.",
            },
            city: {
                 required: "Please select an city.",
            },            
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
 
   jQuery("#frm_agent_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    user_status: 
                    {
                        required: true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },
                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                   
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           user_status:{
                required: "Please select status.",
           },
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            state: {
                required: "Please select state."
            },
            city: {
                required: "Please select city."
            },
                    
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
   jQuery("#frm_agent_update_email").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    confirm_email: 
                    {
                        required: true,
                        email:true,
                        equalTo:"#email"
                    },
                    
                },
        messages: {
          
            
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
            confirm_email:{
                required: "Please enter confirm email.",
                email: "Please enter valid email.",
                remote:"This email is already taken",
                equalTo:"Confirm email does not match"
            }
                    
            
        }
    });
   jQuery("#frm_agent_update_password").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    new_password: 
                    {
                        required: true,
                        minlength:6
                    },
                    confirm_password: 
                    {
                        required: true,
                        equalTo:"#new_password"
                    },
                    
                },
        messages: {
          
            
            new_password:{
                required: "Please enter password.",
            },
            confirm_password:{
                required: "Please enter confirm password.",
                
                equalTo:"Confirm password does not match"
            }
                    
            
        }
    }); 
    
    jQuery("#frm_regsitered_user").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },
                  
                    mobile_code: {
                        required: true
                    },

                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            mobile_code: {
                required: "Please select mobile/country code."
            },
                  
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
 
   jQuery("#frm_regsitered_user_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    user_status: 
                    {
                        required: true
                    },
                    

                    country: {
                        required: true
                    },
                    
                   
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    }

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           user_status:{
                required: "Please select status.",
           },
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            state: {
                required: "Please select state."
            },
            city: {
                required: "Please select city."
            },
                    
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            }            
            
        }
    });
   jQuery("#frm_regsitered_user_update_email").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    confirm_email: 
                    {
                        required: true,
                        email:true,
                        equalTo:"#email"
                    },
                    
                },
        messages: {
          
            
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
            confirm_email:{
                required: "Please enter confirm email.",
                email: "Please enter valid email.",
                remote:"This email is already taken",
                equalTo:"Confirm email does not match"
            }
                    
            
        }
    });
   jQuery("#frm_regsitered_user_update_password").validate({
                errorClass: 'text-danger',
                rules: {
                    new_password: 
                    {
                        required: true,
                        minlength:6
                    },
                    new_password_confirmation: 
                    {
                        required: true,
                        equalTo:"#new_password"
                    }
                    
                },
        messages: {
            new_password:{
                required: "Please enter password.",
            },
            new_password_confirmation:{
                required: "Please enter confirm password.",
                
                equalTo:"Confirm password does not match"
            }
                    
            
        }
    });
   jQuery("#add_star").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    locale: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        email: true,
               
                    },
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required:function(){
                            return $("#password").val()!='';
                        },
                        equalTo:"#password"
                    },

                    country: {
                        required: true
                    },
                    mobile_code: {
                        required: true
                    },

                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    user_mobile: {
                        required: true
                    },
                    owner_name: {
                        required: true
                    },
                    owner_number: {
                        required: true
                    }

                },
        messages: {
          
            locale: {
                required: "Please select prefered language."
            },
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            mobile_code: {
                required: "Please select country/mobile code."
            },
           
            state: {
                 required: "Please select state.",
            },
            city: {
                 required: "Please select an city.",
            },            
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            },            
            owner_number: {
                 required: "Please enter owner number.",
            },            
            owner_name: {
                 required: "Please enter owner name.",
            }            
            
        }
    });
   
   jQuery("#frm_star_user_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    first_name: 
                    {
                        required: true
                    },
                    last_name: 
                    {
                        required: true
                    },
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    user_status: 
                    {
                        required: true
                    },
                    
                    
                    country: {
                        required: true
                    },
                    
                   state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    owner_number: {
                        required: true
                    },            
                   owner_name: {
                       required: true
                   }     

                },
        messages: {
          
            first_name: {
                required: "Please enter first name."
            },
            last_name: {
                required: "Please enter last name."
            },
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
           user_status:{
                required: "Please select status.",
           },
            password: {
                required: "Please enter password.",
                minlength: "Please enter minimum 6 characters."
            },
            password_confirmation: {
                required: "Please enter confirm password.",
                equalTo: "Confirm password is not matching with above password entered."
            },
            country: {
                required: "Please select country."
            },
            state: {
                required: "Please select state."
            },
            city: {
                required: "Please select city."
            },
                    
            gender: {
                 required: "Please select gender.",
            },            
            user_mobile: {
                 required: "Please enter mobile no.",
            },
            owner_number: {
                 required: "Please enter owner number.",
            },            
            owner_name: {
                 required: "Please enter owner name.",
            }                
            
        }
    });
   jQuery("#frm_star_user_update_email").validate({
    
                errorClass: 'text-danger',
                rules: {
                 
                    email: 
                    {
                        required: true,
                        email:true
                    },
                    confirm_email: 
                    {
                        required: true,
                        email:true,
                        equalTo:"#email"
                    },
                    
                },
        messages: {
          
            
            email:{
                required: "Please enter email.",
                email: "Please enter valid email.",
                remote:"This email is already taken"
            },
            confirm_email:{
                required: "Please enter confirm email.",
                email: "Please enter valid email.",
                remote:"This email is already taken",
                equalTo:"Confirm email does not match"
            }
                    
            
        }
    });
   jQuery("#frm_star_user_update_password").validate({
                errorClass: 'text-danger',
                rules: {
                    password: 
                    {
                        required: true,
                        minlength:6
                    },
                    password_confirmation: 
                    {
                        required: true,
                        equalTo:"#password"
                    }
                    
                },
        messages: {
            password:{
                required: "Please enter password.",
            },
            password_confirmation:{
                required: "Please enter confirm password.",
                
                equalTo:"Confirm password does not match"
            }
                    
            
        }
    }); 
    
   jQuery("#frm_country_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    }
                },
        messages: {
          
            name: {
                required: "Please enter the name."
            },
                    
            
        }
    });
    
   jQuery("#frm_language_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    }
                },
        messages: {
          
            name: {
                required: "Please enter the name."
            }
                    
            
        }
    });
   jQuery("#frm_language_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    }
                },
        messages: {
          
            name: {
                required: "Please enter the name."
            }
                    
            
        }
    });
    
    jQuery("#frm_country_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    },
                    iso:
                    {
                        required: true
                    },
                    country_code:
                    {
                        required: true
                    },
                    cancellation_charge:
                    {
                        required: true
                    }
                },
        messages: {
          
                    name: {
                        required: "Please enter the name."
                    },
                    iso: {
                        required: "Please enter the iso."
                    },
                    country_code: {
                        required: "Please enter country code."
                    },
                    cancellation_charge:
                    {
                         required: "Please enter the cancellation charges."
                    }      
           }
    });
    jQuery("#frm_state_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    },
                   
                    country:
                    {
                        required: true
                    }
                },
        messages: {
          
                    name: {
                        required: "Please enter the name."
                    },
                   
                    country: {
                        required: "Please select a country."
                    }     
           }
    });
    jQuery("#frm_state_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    },
                   
                    country:
                    {
                        required: true
                    }
                },
        messages: {
          
                    name: {
                        required: "Please enter the name."
                    },
                   
                    country: {
                        required: "Please select a country."
                    }     
           }
    });
    
    jQuery("#frm_city_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    },
                   
                    country:
                    {
                        required: true
                    },
                    state:
                    {
                        required: true
                    }
                },
        messages: {
          
                    name: {
                        required: "Please enter the name."
                    },
                   
                    country: {
                        required: "Please select a country."
                    }, 
                    state: {
                        required: "Please select a state."
                    }
           }
    });
    jQuery("#frm_city_update").validate({
    
               
                errorClass: 'text-danger',
                rules: {
                    name: 
                    {
                        required: true
                    },
                   
                    country:
                    {
                        required: true
                    },
                    state:
                    {
                        required: true
                    }
                },
        messages: {
          
                    name: {
                        required: "Please enter the name."
                    },
                   
                    country: {
                        required: "Please select a country."
                    }, 
                    state: {
                        required: "Please select a state."
                    }
           }
    });
    jQuery("#frm_vehicle_add").validate({
    
                errorClass: 'text-danger',
                rules: {
                    vehicle_name: 
                    {
                        required: true
                    },
                    plate_number:
                    {
                        required: true
                    },
                    vehicle_desc:
                    {
                        required: true
                    },
                    status:
                    {
                        required: true
                    },
                    vehicle_list:
                    {
                        required: true
                    },
                    year_manufacture: {
                     required: true
                    }
                },
        messages: {
          
                    vehicle_name: {
                        required: "Please enter the name."
                    },
                   
                    plate_number: {
                        required: "Please enter plate number."
                    }, 
                    vehicle_desc: {
                        required: "Please select status."
                    },
                    vehicle_list: {
                        required: "Please select a vehicle."
                    },
                    status: {
                        required: "Please select status."
                    },
                    year_manufacture: {
                     required: "Please select vehicle manufacture type."
                    }
           }
    });
    
    jQuery("#frm_vehicle_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    vehicle_name: 
                    {
                        required: true
                    },
                    plate_number:
                    {
                        required: true
                    },
                    vehicle_desc:
                    {
                        required: true
                    },
                    status:
                    {
                        required: true
                    },
                    financial_type:
                    {
                        required: true
                    },
                    vehicle_list:
                    {
                        required: true
                    }
                },
        messages: {
          
                    vehicle_name: {
                        required: "Please enter the name."
                    },
                   
                    plate_number: {
                        required: "Please enter plate number."
                    }, 
                    vehicle_desc: {
                        required: "Please select status."
                    },
                    vehicle_list: {
                        required: "Please select a vehicle."
                    },
                    status: {
                        required: "Please select status."
                    },
                    financial_type: {
                      required: "Please select vehicle manufacture type."
                    }
           }
    });
    jQuery("#frm_emailtemplate_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    subject: 
                    {
                        required: true
                    },
                    html_content:
                    {
                        required: true
                    }
                    
                },
        messages: {
          
                    subject: {
                        required: "Please enter subject."
                    },
                    html_content: {
                        required: "Please enter contents."
                    }
                   
                    
           }
    });
    jQuery("#frm_cms_update").validate({
    
                errorClass: 'text-danger',
                rules: {
                    page_title: 
                    {
                        required: true
                    },
                    page_content:
                    {
                        required: true
                    },
                    page_alias:
                    {
                        required: true
                    },
                    page_status:
                    {
                        required: true
                    },
                    page_seo_title:
                    {
                        required: true
                    },
                    page_meta_keywords:
                    {
                        required: true
                    },
                    page_meta_descriptions:
                    {
                        required: true
                    }
                },
        messages: {
          
                     page_title: 
                    {
                        required: "Please enter name/title."
                    },
                    page_content:
                    {
                         required: "Please enter content."
                    },
                    page_alias:
                    {
                          required: "Please enter page alias."
                    },
                    page_status:
                    {
                        required: "Please select status."
                    },
                    page_seo_title:
                    {
                         required: "Please enter seo title."
                    },
                    page_meta_keywords:
                    {
                         required: "Please enter meta keyword."
                    },
                    page_meta_descriptions:
                    {
                       required: "Please enter meta description."
                    }
           }
    });
    jQuery("#create_payment").validate({
    
                errorClass: 'text-danger',
                rules: {
                    
                    payment_mode:
                    {
                        required: true
                    },
                    user:
                    {
                        required: true
                    },
                    bank_name:
                    {
                        required: true
                    },
                     cheque_number: 
                    {
                         required: true
                    },
                     transaction_number: 
                    {
                         required: true
                    },
                     amount: 
                    {
                         required: true
                    }
                },
        messages: {
          
                     payment_mode: 
                    {
                        required: "Please choose a payment mode."
                    },
                     user: 
                    {
                        required: "Please choose a user."
                    },
                    bank_name: 
                    {
                        required: "Please enter the bank name."
                    },
                     cheque_number: 
                    {
                        required: "Please enter the cheque number."
                    },
                     transaction_number: 
                    {
                        required: "Please enter the transaction number."
                    },
                     amount: 
                    {
                        required: "Please enter the amount."
                    }
                    
           }
    });
});

