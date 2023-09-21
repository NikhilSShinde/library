jQuery(document).ready(function() {


    /* landing Form Validation Start */
    jQuery("#register_normal").validate({
    
        errorClass: 'text-danger',
        rules: {
            first_name: 
            {
                required: true
            },
            email: 
            {
                
                email:true,
                remote: {
                        url: javascript_site_path + 'chk-email-duplicate',
                        method: 'get'
                    }
                
            },            
            country: 
            {
                required: true
            },
            state: 
            {
               
                 required: true
            },
            city: 
            {
               
                 required: true
            },
            last_name: {
                required: true
            },
          
            suburb: {
                required: true
            },
            address: {
                required: true
            },
            working_time: {
                required: true
            },
            nationality: {
                required: true
            },
            device: {
                required: true
            }
           
        },
        messages: {
          
            first_name: {
                required: "الرجاء إدخال الاسم الأول."
            },
            last_name: {
                required: "الرجاء إدخال اسم آخر."
            },
            email:{
                required: "يرجى إدخال البريد الإلكتروني",
                email: "الرجاء إدخال بريد إلكتروني صحيح",
                remote:"الايميل أخذ مسبقا"
            },
           
            country: {
                required: "الرجاء اختيار البلد."
            },
            address: {
                required: "الرجاء إدخال عنوانك."
            },
            working_time: {
                required: "الرجاء اختيار نوع العمل الخاص بك."
            },
            state: {
                required: "الرجاء اختيار المنطقة."
            },
            city: {
                required: "اختر المدينة."
            },
            nationality: {
                 required: "الرجاء اختيار الجنسية",
            },
            device: {
                 required: "الرجاء اختيار الجهاز الذي تستخدمه",
            }
                   
            
        },
        submitHandler: function(form) {
            jQuery("#btn_register").hide();
            jQuery("#btn_loader").show();
            form.submit();
        }
    });
    /* landing Form Validation Start */
    jQuery("#update_profile").validate({
    
        errorClass: 'text-danger',
        rules: {
            first_name: 
            {
                required: true
            },
            
            last_name: {
                required: true
            },
            email: {
                required: true,
                email: true
//                remote: {
//                    url: javascript_site_path + 'chk-email-duplicate',
//                    method: 'post'
//                }
            },
            password: {
                required: true,
                minlength: 8
            },
            password_confirmation: 
            {
                required: true,
                equalTo: "#password"
            },
            suburb: {
                required: true
            },
            zipcode: {
                required: true
            },
            user_description_type: {
                required: true
            }
           
        },
        messages: {
            first_name: {
                required: "الرجاء إدخال الاسم الأول."
            },
            last_name: {
                required: "الرجاء إدخال اسم آخر."
            },
            suburb: {
                required: "الرجاء إدخال ضاحية."
            },
            email: {
                required: "يرجى إدخال البريد الإلكتروني.",
                specialChars:"يرجى إدخال البريد الإلكتروني الصحيح.",
                email: "يرجى إدخال البريد الإلكتروني الصحيح.",
                remote: "يتم تسجيل عنوان البريد الإلكتروني هذا بالفعل مع الموقع."
            },
            password: {
                required: "الرجاء إدخال كلمة المرور.",
                
            },
            password_confirmation: {
                required: "الرجاء التأكد من فوق كلمة المرور.",
                equalTo: "هذه لا تتطابق كلمات المرور. حاول ثانية"
            },
            zipcode: {
                 required: "الرجاء إدخال الرمز البريدي.",
            },
            user_description_type: {
                 required: "الرجاء تحديد خيار.",
            }            
            
        }
    });
    /* landing Form Validation Start */
    jQuery("#become_a_star").validate({
    
        errorClass: 'text-danger',
        rules: {
            mobile: 
            {
                required: true,
                digits:true
            },
            
            country_code: {
                required: true
            }
          
           
        },
        messages: {
         
            mobile: {
                required: "الرجاء إدخال رقم هاتفك المحمول",
                digits:"الرجاء إدخال رقم جوال صحيح",
            },
            country_code: {
                required: "الرجاء اختيار رمز البلد",
            }
        }
//        }, submitHandler: function(form) {
//            jQuery("#btn_register_first").hide();
//            jQuery("#btn_loader_first").show();
//            form.submit();
//        }
    });
    jQuery("#update_email").validate({
    
        errorClass: 'text-danger',
        rules: {
            email: 
            {
                required: true,
                email:true
            },
            
            confirm_email: {
                required: true,
                email:true,
                equalTo: "#email"
            }
          
           
        },
        messages: {
         
            email: {
                required: "يرجى إدخال البريد الإلكتروني",
                specialChars:"يرجى إدخال البريد الإلكتروني الصحيح",
                email: "يرجى إدخال البريد الإلكتروني الصحيح",
                remote: "يتم تسجيل عنوان البريد الإلكتروني هذا بالفعل مع الموقع."
            },
            confirm_email: {
                required: "الرجاء إدخال تأكيد البريد الإلكتروني.",
                email: "يرجى إدخال البريد الإلكتروني الصحيح."
            }
            
        }
    });
    jQuery("#update_email").validate({
    
        errorClass: 'text-danger',
        rules: {
            email: 
            {
                required: true,
                email:true
            },
            
            confirm_email: {
                required: true,
                email:true,
                equalTo: "#email"
            }
          
           
        },
        messages: {
         
            email: {
                required: "يرجى إدخال البريد الإلكتروني.",
                specialChars:"يرجى إدخال البريد الإلكتروني الصحيح.",
                email: "يرجى إدخال البريد الإلكتروني الصحيح.",
                remote: "يتم تسجيل عنوان البريد الإلكتروني هذا بالفعل مع الموقع."
            },
            confirm_email: {
                required: "الرجاء إدخال تأكيد البريد الإلكتروني.",
                email: "يرجى إدخال البريد الإلكتروني الصحيح."
            }
            
        }
    });
    /* landing Form Validation Start */
    jQuery("#update_password").validate({
    
        errorClass: 'text-danger',
        rules: {
            current_password: 
            {
                required: true,
                remote: {
                        url: javascript_site_path + 'chk-current-password',
                        method: 'get'
                    }
            },
            
            new_password: {
                required: true
            },
            confirm_password: {
                required: true,
                equalTo: "#new_password"
            }
          
           
        },
        messages: {
         
           current_password: 
            {
                required: "يرجى إدخال كلمة المرور الحالية.",
                remote  : "سجل ليست مطابقة في نظامنا"
            },
            new_password: {
                 required: "يرجى إدخال كلمة المرور الجديدة",
            },
            confirm_password: {
                required: "الرجاء إدخال تأكيد كلمة",
                equalTo: "يرجى إدخال كلمة المرور نفسها على النحو الوارد أعلاه",
            }
           
            
        }
    });

       
});