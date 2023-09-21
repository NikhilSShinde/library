<?php
namespace App\Http\Controllers\Auth;
use App\User;
use App;
use App\Nationality;
use App\Http\Requests;
use App\UserInformation;
use App\UserAddress;
use App\PiplModules\roles\Models\Role;
use Validator;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Mail;
use GlobalValues;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\CountryTranslation;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\category\Models\Category;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use Session;
use Lang;
use App\DriverUserInformation;
use Storage;
use Elibyy\TCPDF\TCPDF;
use App\PiplModules\admin\Models\SpokenLanguage;
use App\PiplModules\admin\Models\SpokenLanguageTranslation;
use App\UserSpokenLanguageInformation;
use PDF;
use Twilio;
class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/redirect-dashboard';
   

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       //$this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        //only common files if we have multiple registration
        return Validator::make($data, [           
            'email' => 'required|email|max:355|unique:users',
            'password' => 'required|min:6|confirmed',
            'first_name' => 'required',
            'last_name' => 'required',
            'driver_license' => 'mimes:pdf,png,jpeg',
			
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    
    
    protected function becomeAStar(Request $request)
    {
       $data_values = $request->all();
        if($request->method() == "GET" )
         {
             $ip = $_SERVER['REMOTE_ADDR'];
             if($ip=='192.168.2.1')
             {
                 $ip="182.72.79.154";
             }
             $countr_iso="";
             $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            
               if(count($details)>0)
               {
                   if(isset($details->country))
                   {
                       $countr_iso=$details->country;
                   }
               }
              
               
                //get all countries
                $arrCountries=Country::translatedIn(\App::getLocale())->get();
                $arrCountries=$arrCountries->reject(function($country)
                {
                    
                    return ($country->id=='17');
                });
                
                return view("auth.become-a-star",array("countries"=>$arrCountries,"country_iso"=>$countr_iso));
        }
            else
              {
                    
                     $validate_response = Validator::make($data_values, [

                                'country_code' =>  'required',
                                'mobile' => 'required|numeric'     
                                ],
                          [
                              'country_code.required'=>Lang::choice('website_keywords.mobile_code_is_required',\App::getLocale()),
                              'mobile.required'=>Lang::choice('website_keywords.mobile_is_required',\App::getLocale())
                          ]
                     );

                if($validate_response->fails())
                 {
                  
                         return redirect('become-a-star')
                          ->withErrors($validate_response)
                          ->withInput();
                 }else{
                     //checing mobile for existance.
                     $mobile_code=str_replace("+","",$data_values['country_code']);
                      $mobile=$data_values['mobile'];
                     $userData=User::where('username',$mobile)->first();
                
                     if(count($userData)>0 && ($userData->userInformation->mobile_code==$mobile_code))
                     {
                          $validate_response1 = Validator::make($data_values,
                                   [    
                                       'mobile' => 'required|numeric|unique:users,username',   
                                   ],
                                  [
                                     'mobile.required'=>Lang::choice('website_keywords.mobile_is_required',\App::getLocale()), 
                                     'mobile.numeric'=>Lang::choice('website_keywords.mobile_numeric',\App::getLocale()), 
                                     'mobile.unique'=>Lang::choice('website_keywords.mobile_unique',\App::getLocale())
                                    
                                  ]
                                  );
                            if($validate_response1->fails())
                             {
                               
                                     return redirect('/become-a-star')
                                      ->withErrors($validate_response1)
                                      ->withInput();
                             }else{
                                 Session::put("mobile_code",$mobile_code);
                                 Session::put("mobile",$mobile);
                                 return redirect('/become-a-star-personal-info');
                             }
                     }else{
                         Session::put("mobile_code",$mobile_code);
                         Session::put("mobile",$mobile);
                         return redirect('/become-a-star-personal-info');
                     }
                 }

        }
    }
    
     
    protected  function downloadPdfFile($file_path)
    {
        $filename = url('public/star_registration')."/".$file_path;
        $fileinfo = pathinfo($filename);
        $sendname = $fileinfo['filename'] . '.' . strtoupper($fileinfo['extension']);

        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"$sendname\"");
        readfile($filename);die;
    }
    protected  function downloadPdfFileAdmin($file_path)
    {
        $filename = url('public/star_registration_admin')."/".$file_path;
        $fileinfo = pathinfo($filename);
        $sendname = $fileinfo['filename'] . '.' . strtoupper($fileinfo['extension']);

        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"$sendname\"");
        readfile($filename);die;
    }
            
    protected function becomeAStarSetp1(Request $request)
    {
         $locale=\App::getLocale();
         //generating pdf 
       $data_values = $request->all();
       if(Session::get("mobile_code")=='')
        {
              return redirect('/become-a-star');
        }
       
        if($request->method() == "GET" )
           {
                    
                   
                   //get all countries
                   $arrCountries=Country::translatedIn(\App::getLocale())->get();
                   $arrCountries=$arrCountries->sortBy('name');
                   $arrCountries=$arrCountries->reject(function($country)
                   {
                       return ($country->id=='17');
                   });
                   //getting all nationalities
                   $nationality=Nationality::all();
                   $all_Spokenlangusge = SpokenLanguage::translatedIn(\App::getLocale())->get();
                   return view("auth.register",array("nationality"=>$nationality,"countries"=>$arrCountries,"spoken_languages"=>$all_Spokenlangusge));
           }else{
              
                $data_values = $request->all();
                $validate_response = Validator::make($data_values,
                              [    
                                       'first_name' => 'required',   
                                       'last_name' => 'required',   
                                       'country' => 'required',   
                                       'state' => 'required',   
                                       'city' => 'required',   
                                       'nationality' => 'required',   
                                       'device' => 'required',   
                                       'driver_license' => 'mimes:pdf,jpg,png,jpeg',
//                                       'licence_number' => 'required'  
                               ],
                               [
                                   'first_name.required'=>Lang::choice('website_keywords.first_name_is_required',\App::getLocale()), 
                                   'last_name.required'=>Lang::choice('website_keywords.last_name_is_required',\App::getLocale()), 
                                   'country.required'=>Lang::choice('website_keywords.country_is_required',\App::getLocale()),
                                   'state.required'=>Lang::choice('website_keywords.state_is_required',\App::getLocale()),
                                   'city.required'=>Lang::choice('website_keywords.city_is_required',\App::getLocale()),
                                   'nationality.required'=>Lang::choice('website_keywords.nationalitiy_is_required',\App::getLocale()),
                                   'licence_number.required'=>Lang::choice('website_keywords.license_is_required',\App::getLocale()),
                                   'driver_license.mimes'=>Lang::choice('website_keywords.license_file_validation',\App::getLocale())

                               ]
                        );

                   if($validate_response->fails())
                    {

                            return redirect('/become-a-star-personal-info')
                             ->withErrors($validate_response)
                             ->withInput();
                    }else{
                        $first_name=isset($data_values['first_name'])?$data_values['first_name']:'';
                        $last_name=isset($data_values['last_name'])?$data_values['last_name']:'';
                        $email=isset($data_values['email'])?$data_values['email']:'-';
                        $country=isset($data_values['country'])?$data_values['country']:'';
                        $state=isset($data_values['state'])?$data_values['state']:'-';
                        $city=isset($data_values['city'])?$data_values['city']:'';
                        $nationality_value=isset($data_values['nationality'])?$data_values['nationality']:'';
                        $licence_number=isset($data_values['licence_number'])?$data_values['licence_number']:'';
                        $driver_id=isset($data_values['driver_id'])?$data_values['driver_id']:'';
                        $device_type=isset($data_values['device'])?$data_values['device']:'';
                        $address=isset($data_values['address'])?$data_values['address']:'';
                        $working_time_value=isset($data_values['working_time'])?$data_values['working_time']:'';
                        $working_time=($working_time_value=='0')? (Lang::choice('website_keywords.part_time', $locale)):(Lang::choice('website_keywords.full_time', $locale));
                        $language_prefer_value=isset($data_values['prefer_language'])?$data_values['prefer_language']:'';
                        $language_prefer=Lang::choice('website_keywords.language_any', $locale);
                        if($language_prefer_value=='1')
                       {
                           $language_prefer=Lang::choice('website_keywords.english', $locale);
                       }else if($language_prefer_value=='2')
                       {
                           $language_prefer=Lang::choice('website_keywords.arabic', $locale);
                       }
                        $mobile=session::get('mobile');
                        $mobile_code=session::get('mobile_code');
                        $latitude=session::get('latitude');
                        $lontitude=session::get('lontitude');
                         $user_type = 2;
                     //check if user is already registered
                    $arruserDetails=User::where('username',$mobile)->first();   
                  if(count($arruserDetails)>0 && $arruserDetails->userInformation->mobile_code==$mobile_code) 
                  {
                       return redirect('become-a-star-personal-info')->with('star-error','star_error');
                  }else{
                       //making user registration
                       if(session::get('mobile')!='')
                       {
                           $arruserMain['username']=$mobile;
                           if($email!='' && $email!='-')
                           {
                               $arruserMain['email']=$email;
                           }
                           $created_user = User::create($arruserMain);
                           
                            $mobile_code=str_replace("+","",$mobile_code);
                            $arr_userinformation["first_name"] = $first_name;
                            $arr_userinformation["last_name"] = $last_name;
                            $arr_userinformation["user_mobile"] = ltrim($mobile, '0');
                            $arr_userinformation["mobile_code"] = $mobile_code;
                            $arr_userinformation["nationality"] = $nationality_value;
                            $arr_userinformation["device_type"] = $device_type;
                            $arr_userinformation["user_status"] = 0;
                            $arr_userinformation["user_type"] = $user_type;
                            $arr_userinformation["user_id"] = $created_user->id;
                            $updated_user_info = UserInformation::create($arr_userinformation);
                            $arr_userAddress["user_id"] = $created_user->id;
                            
                            //address data
                            $arr_userAddress["user_country"] = $country;
                            $arr_userAddress["user_state"] = $state;
                            $arr_userAddress["user_city"] = $city;
                            $arr_userAddress["latitude"] = $latitude;
                            $arr_userAddress["longitude"] =$lontitude;
                            $arr_userAddress["address"] =$address;
                             if($country!='')
                            {
                             UserAddress::create($arr_userAddress);
                            }
                            $arr_spoken_languages = array();
                           if($language_prefer_value!='' && $language_prefer_value!='0')
                           {
                            $arr_spoken_languages["spoken_language_id"] = $language_prefer_value;
                            $arr_spoken_languages["user_id"] = $created_user->id;
                            UserSpokenlanguageinformation::create($arr_spoken_languages);
                           }
                            $userRole = Role::where("slug","registered.user")->first();
                            $created_user->attachRole($userRole);
                             // asign role to respective user	
                            //star informations
                            $arr_starInfo["user_id"] = $created_user->id;
                            $arr_starInfo["driver_license"] = $licence_number;
                            $arr_starInfo["driver_license_flle"] = "";
                            $arr_starInfo["id_number"] = $driver_id;
                            if($request->file('driver_license'))
                            {
                              $extension = $request->file('driver_license')->getClientOriginalExtension();
                              $new_file_name = time().".".$extension;                             
                              Storage::put('public/star-document/'.$new_file_name,file_get_contents($request->file('driver_license')->getRealPath()));
                              $arr_starInfo["driver_license_flle"]= $new_file_name;
                            }
                            DriverUserInformation::create($arr_starInfo); 
                            
                            //sending email to user.
                             //getting from global setting
                            $site_email=GlobalValues::get('site-email');
                            $site_title=GlobalValues::get('site-title');
                            $arr_keyword_values = array();
                            //Assign values to all macros
                            $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                            $arr_keyword_values['LAST_NAME'] =  $updated_user_info->last_name;
                            $arr_keyword_values['MOBILE'] =  $mobile_code."".$mobile;
                            $arr_keyword_values['SITE_TITLE'] =  $site_title;

                            $email_subject = Lang::choice('messages.register_email_subject', $locale);
                            $tempate_name = "emailtemplate::star-registration-successfull-" . $locale;
                            if (isset($created_user->email) && $created_user->email != '' && $created_user->email != NULL) {
                                Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($created_user, $email_subject, $site_email, $site_title) {

                                    $message->to($created_user->email)->subject($email_subject)->from($site_email, $site_title);
                                });
                            }
                           //sendimng email to admin
                             Mail::send('emailtemplate::star-registration-successful-admin',$arr_keyword_values, function ($message) use ($site_email,$site_title)  {

                                $message->to($site_email,"BAGGI")->subject("A New Star User Request")->from($site_email,"BAGGI");

                            });
                           $countryInfo=Country::where('id',$country)->first();  
                           $countryTransInfo=CountryTranslation::where('country_id',$country)->where('locale','en')->first();  
                         
                            $mobile_number_to_send= str_replace("+","",$mobile_code);
                            $mobile_number_to_send="+".$mobile_number_to_send."".$mobile;
                            $website_link=url('');
                            $messagesToSend="Thank you for becoming a STAR. ";
                           
                            if($locale=='ar')
                            {
                                 $messagesToSend="شكرأ لإنضمامك الى نجوم دلفر فور اوول";
                            }
                          if($locale=='ar')
                          {
                               if(isset($countryTransInfo->support_number) && $countryTransInfo->support_number !='')
                           {
                                $messagesToSend.=" ";   
                                $messagesToSend.="لمزيد من المعلومات الاتصال بنا على"." ".$countryTransInfo->support_number;
                           }
                          }else{
                            
                                if(isset($countryTransInfo->support_number) && $countryTransInfo->support_number !='')
                            {
                                 $messagesToSend.="for more info contact us on ".$countryTransInfo->support_number;
                            }
                          }
                           
                           
                        Twilio::message($mobile_number_to_send, $messagesToSend);
                         $dt = new DateTime(date('Y-m-d H:i:s'));
          
                    //get timezone as per country
                    
                   $stateInfo=State::where('id',$state)->first();
                    $cityInfo=City::where('id',$city)->first();
                    if(count($countryInfo)>0)
                    {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         //$tz = new DateTimeZone('UTC'); // or whatever zone you're after
                         $dt->setTimezone($tz);
                    }

                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                    $pdf_title=Lang::choice('website_keywords.become_star',\App::getLocale());
                    $lg = Array();
                    $lg['a_meta_charset'] = 'UTF-8';

                    // set some language-dependent strings (optional)
                    PDF::setLanguageArray($lg);
                    
                    PDF::SetTitle($pdf_title);
                    PDF::AddPage();
                    PDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $logo_images=url('public/media/front/images/logo_dlvr4all.jpg');
                    $categories=Category::where('status','1')->get();
                   // PDF::Image($logo_images, 50, 50, 100, '', '', 'http://www.dlvr4all.com', '', false, 300);
                    $nationality_details=Nationality::where('id',$nationality_value)->first();
                  if($locale=='ar')
                  {
                    PDF::setRTL(true);
                    PDF::SetFont('dejavusans', '', 10);
                    $service_html='<table border="1"  width="100%" style="border-top:none;border:1px solid #000;direction:rtl;" cellpadding="10"   cellspacing="0"  >';
                    $service_html.="<tr>";
                    $service_html.='<td valign="middle" width="22%" style="font-weight:bold; padding:20px;">ترغب بتقديمهاالخدمة التي </td>';
                    if(count($categories)>0)
                    {
                        foreach($categories as $category)
                        {       
                            $service_html.='<td valign="middle" width="26%" style="padding:20px;"><span>&nbsp;&nbsp;</span><input style="padding:5px;margin-right:10px;" name="'.$category->name.'" type="checkbox" value="1"/> '." ".$category->name.'</td>';
                        }
                    }
                    $service_html.="</tr></table>";

                   

                    $delivery_methods_html="";
                    $delivery_methods_html.='<table  border="1"  width="100%" style="border-top:0px solid #000;border:1px solid #000;" cellpadding="10"   cellspacing="0"  >';
                    
                    if(count($categories)>0)
                    {
                        $k=0;
                        
                        foreach($categories as $category)
                        {    
                            $main_flag=0;
                             $delivery_methods_html.='<tr>';
                            if($k==0)
                                 {
                                     $delivery_methods_html.='<td rowspan="6" colspan="1"   valign="bottom" width="11%" style="font-weight:bold; padding:20px;">توصيلأساليب</td> ';
                                 }
                          
                          if($k==1)
                            {
                               $delivery_methods_html.='<td  rowspan="2" valign="middle" width="11%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                            }else{
                                 $delivery_methods_html.='<td  rowspan="2" valign="bottom" width="11%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                            }
                            $category_id=$category->id;
                            $countryserviceData = CountryServices::where('country_id', $country)->orderBy('sort_index','asc')->with('serviceInformation')->get(); 
                            $countryserviceData=$countryserviceData->reject(function($countyservice) use($category_id)
                            {
                               return ($countyservice->serviceInformation->category_id!=$category_id ||$countyservice->serviceInformation->parent_id>0 || $countyservice->serviceInformation->id=='17');
                            })->values();
                            
                            
                            if(count($countryserviceData)>0)
                            {
                                $l=0;
                                $flag=0;
                                foreach($countryserviceData as $countryservice)
                                {    
                                    $l++;   
                                    if( $l==4)
                                   {
                                        $flag=1;
                                        $delivery_methods_html.='</tr>';
                                       $delivery_methods_html.='<tr>';
                                   }
                                    $delivery_methods_html.='<td valign="middle" width="26%" style="padding:20px;"><span>&nbsp;&nbsp;</span><input style="padding:0px;" name="'.$countryservice->id.'" value="1" type="checkbox"> '.$countryservice->serviceInformation->name.'</td>';
                                
                                   if(count($countryserviceData)==$l && $flag==1)
                                   {
                                       $main_flag=1;
                                       $delivery_methods_html.='</tr>';
                                   }
                                   
                                }
                            }
                            if($main_flag==0)
                            {
                                $delivery_methods_html.="</tr>";
                            }
                           $k++;
                            
                           }
                           
                         }
                         
                         
                    $delivery_methods_html.="</table>";
                    
                    $html1 = '
                        <table  width="100%" cellpadding="10" cellspacing="0"  style="line-height:0;direction:rtl;">
                        <tr>
                          <td align="right" valign="middle" width="50%" style="padding:20px;"><img width="100" src="'.$logo_images.'"></td>
                          <td align="left" valign="middle" width="50%" style="padding:20px; padding-right:50px;"><p style="margin:0;">تاريخ: '.$date2_val.'</p></td>
                         </tr>

                      </table>
                        <table  width="100%" cellpadding="5"  cellspacing="0"  style="line-height:0;margin-bottom:20px;direction:rtl;">
                        <tr>
                        <td valign="middle" align="center"><h1 style="margin:0; font-weight:bold;">استمارة التسجيل نجوم</h1></td>

                        </tr>
                        <tr>
                            <td ></td>
                        </tr>
                      </table>

                      <table border="1" width="100%" style="border-top:none;border:1px solid #000;direction:rtl;" cellpadding="10"   cellspacing="0"  >
                      
                      
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold; padding:20px;">الاسم الكامل:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;">'.$first_name." ".$last_name.' ('.$created_user->id.')</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold; padding:20px;">رقم الهاتف:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;">'.$mobile_code."".$mobile.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold; padding:20px;">البريد الالكتروني:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;">'.$email.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">الجنسية:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;">'.$nationality_details->country_name_arabic.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">الدولة:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;">'.$countryInfo->name.'</p></td>

                        </tr>
                         <tr>
                          <td valign="middle" width="22%" style="font-weight:bold; padding:20px;">المحافظة:</td>
                          <td valign="middle" width="78%" style="padding:20px;"><p style="margin:0;;">'.$stateInfo->name.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">المدينة:</td>
                          <td valign="middle" width="78%" style="padding:20px;" ><p style="margin:0;">'.$cityInfo->name.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">العنوان:</td>
                          <td valign="middle" width="78%" style="padding:20px;" ><p style="margin:0;">'.$address.'</p></td>

                        </tr>
                         <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">أوقات العمل:</td>
                          <td valign="middle" width="78%" style="padding:20px;" ><p style="margin:0;">'.$working_time.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="22%" style="font-weight:bold;padding:20px;">مُعرف:</td>
                          <td valign="middle" width="39%" style="padding:20px;" ><p style="margin:0;">الاسم :</p></td>
                          <td valign="middle" width="39%" style="padding:20px;" ><p style="margin:0;">رقم الهاتف :</p></td>

                        </tr>
                      </table>
                      '.$service_html.'
                      '.$delivery_methods_html.'
                    ';
                   
                    PDF::writeHTML($html1, true, false, true, false, '');
                    $html2='
                      <table cellpadding="10" style="direction:rtl;" cellspacing="0" width="100%">
                         <tr>
                          <td valign="middle" style="font-weight:bold;padding:10px;">الأورا</td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; ١- صورة شخصية</td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; ٢- صورة عن البطاقة المدنية (الهوية الشخصية) </td>
                        </tr>
                        
                        <tr>
                          <td valign="middle" style="padding:10px;"><span style="font-weight:bold;">أنا, الموقع أدناه, أقر بأن جميع البيانات المعبئة في هذا النموذج صحيحة وأتحمل المسؤولية الكاملة في حال إثبات عدم صحتها</span></td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>الاسم</b>:</td>
                         
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>التاريخ</b>:</td>
                         
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>التوقيع</b>:</td>
                         
                        </tr>
                      </table>
                    ';
                    
                    PDF::AddPage();
                    PDF::writeHTML($html2, true, false, true, false, '');
                    //PDF::Write(0, 'Hello World');
                    $pdf_file_name=$created_user->id.".pdf";
                    $pdf_file_name_url=$pdf_file_name;
                    $pdf_file_name='public/star_registration/'.$pdf_file_name;
                    
                  
                    $file_path=  realpath(dirname(__DIR__).'/../../..');
                    $file_path=$file_path."/".$pdf_file_name;
                    PDF::Output($file_path,"F");
                    session::put('pdf_file',$pdf_file_name_url);
                  }else{
                    PDF::SetFont('dejavusans', '', 10);
                    //PDF::SetFont('helvetica', '', 10);
                    $service_html='<table border="1"  width="100%" style="border-top:0px solid #000;border:1px solid #000;" cellpadding="10"   cellspacing="0"  >';
                    $service_html.="<tr>";
                    $service_html.='<td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Service you want to provide</td>';
                    if(count($categories)>0)
                    {
                        foreach($categories as $category)
                        {       
                            $service_html.='<td valign="middle" width="23%" style="padding:20px;"><input style="padding:0px;" name="'.$category->name.'" type="checkbox" value="1"/> '." ".$category->name.'</td>';
                        }
                    }
                    $service_html.="</tr></table>";

                    $delivery_methods_html="";
                    $delivery_methods_html.='<table  border="1"  width="100%" style="border-top:0px solid #000;border:1px solid #000;" cellpadding="10"   cellspacing="0"  >';
                    
                    if(count($categories)>0)
                    {
                        $k=0;
                        
                        foreach($categories as $category)
                        {    
                            $main_flag=0;
                             $delivery_methods_html.='<tr>';
                            if($k==0)
                                 {
                                     $delivery_methods_html.='<td rowspan="6" colspan="1"  style="border:1px solid #000;" valign="bottom" width="15%" style="font-weight:bold; padding:20px;">Delivery Methods</td> ';
                                 }
                          
                          if($k==1)
                            {
                               $delivery_methods_html.='<td rowspan="2" valign="middle" width="15%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                            }else{
                                 $delivery_methods_html.='<td  rowspan="2" valign="bottom" width="15%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                            }
                            $category_id=$category->id;
                            $countryserviceData = CountryServices::where('country_id', $country)->orderBy('sort_index','asc')->with('serviceInformation')->get(); 
                            $countryserviceData=$countryserviceData->reject(function($countyservice) use($category_id)
                            {
                               return ($countyservice->serviceInformation->category_id!=$category_id ||$countyservice->serviceInformation->parent_id>0 || $countyservice->serviceInformation->id=='17');
                            })->values();
                            
                            
                            if(count($countryserviceData)>0)
                            {
                                $l=0;
                                $flag=0;
                                foreach($countryserviceData as $countryservice)
                                {    
                                    $l++;   
                                    if( $l==4)
                                   {
                                        $flag=1;
                                        $delivery_methods_html.='</tr>';
                                       $delivery_methods_html.='<tr>';
                                   }
                                    $delivery_methods_html.='<td valign="middle" width="23%" style="padding:20px;"><input style="padding:0px;" name="'.$countryservice->id.'" value="1" type="checkbox"> '.$countryservice->serviceInformation->name.'</td>';
                                
                                   if(count($countryserviceData)==$l && $flag==1)
                                   {
                                       $main_flag=1;
                                       $delivery_methods_html.='</tr>';
                                   }
                                   
                                }
                            }
                            if($main_flag==0)
                            {
                                $delivery_methods_html.="</tr>";
                            }
                           $k++;
                            
                           }
                           
                         }
                         
                    $delivery_methods_html.="</table>";
                    
                    $html1 = '
                        <table  width="100%" cellpadding="10" cellspacing="0"  style="line-height:0;">
                        <tr>
                          <td align="left" valign="middle" width="50%" style="padding:20px;"><img width="100" src="'.$logo_images.'"></td>
                          <td align="right" valign="middle" width="50%" style="padding:20px; padding-right:50px;"><p style="margin:0;">Date: '.$date2_val.'</p></td>
                         </tr>

                      </table>
                        <table  width="100%" cellpadding="5"  cellspacing="0"  style="line-height:0;margin-bottom:20px">
                        <tr>
                        <td valign="middle" align="center"><h1 style="margin:0; font-weight:bold;">Star Registration Form</h1></td>

                        </tr>
                        <tr>
                            <td ></td>
                        </tr>
                      </table>

                      <table border="1"  width="100%" style="border:1px solid #000;" cellpadding="10"   cellspacing="0"  >
                      
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Full Name:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$first_name." ".$last_name.' ('.$created_user->id.')</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Mobile Number:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$mobile_code."".$mobile.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Email:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$email.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Nationality:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$nationality_details->country_name.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Country:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$countryInfo->name.'</p></td>

                        </tr>
                         <tr>
                          <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Region:</td>
                          <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;;">'.$stateInfo->name.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">City:</td>
                          <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;">'.$cityInfo->name.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Address:</td>
                          <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;">'.$address.'</p></td>

                        </tr>
                         <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Working Time:</td>
                          <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;">'.$working_time.'</p></td>

                        </tr>
                        <tr>
                          <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Reference:</td>
                          <td valign="middle" width="35%" style="padding:20px;" ><p style="margin:0;">Name:</p></td>
                          <td valign="middle" width="34%" style="padding:20px;" ><p style="margin:0;">Mobile Number:</p></td>

                        </tr>
                      </table>
                      '.$service_html.'
                      '.$delivery_methods_html.'
                    ';
                  
                    PDF::writeHTML($html1, true, false, true, false, '');
                    $html2='
                      <table cellpadding="10" cellspacing="0" width="100%">
                         <tr>
                          <td valign="middle" style="font-weight:bold; text-decoration:underline; padding:10px;">Required documents: </td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 1- Personal Photo. </td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 2- A copy of Civil ID </td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 3- A copy of Driving License </td>
                        </tr>
                        
                        <tr>
                          <td valign="middle" style="padding:10px;"><span style="text-decoration:underline;"> I, the undersigned, acknowledge that all data contained in this form are correct and I take full responsibility if otherwise proven.</span></td>
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>Name</b>:</td>
                         
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>Date</b>:</td>
                         
                        </tr>
                        <tr>
                          <td valign="middle" style="padding:10px;"><b>Your Signature</b>:</td>
                         
                        </tr>
                      </table>
                    ';
                    
                    PDF::AddPage();
                    PDF::writeHTML($html2, true, false, true, false, '');
                    //PDF::Write(0, 'Hello World');
                    $pdf_file_name=$created_user->id.".pdf";
                    $pdf_file_name_url=$pdf_file_name;
                    $pdf_file_name='public/star_registration/'.$pdf_file_name;
                    
                  
                    $file_path=  realpath(dirname(__DIR__).'/../../..');
                    $file_path=$file_path."/".$pdf_file_name;
                    PDF::Output($file_path,"F");
                    session::put('pdf_file',$pdf_file_name_url);
                    $driverUserInformation=array();
                   
                  }
                        return redirect('become-a-star-success')->with('star-success','star_sucecess');
                      }
                        
                    }
           }
    }}
   
     protected function becomeAStarSuccess(Request $request)
      {
           $locale=\App::getLocale();
           if($request->method() == "GET" )
           {
               //here we generating the pdf of registration page
               
               if(session::get('mobile')!='')
               {
                 $pdf_file= session::get('pdf_file');
                 
                 $msg=Lang::choice('website_keywords.request_success',\App::getLocale());
                 session::put('mobile','');
                 session::put('mobile_code','');
                 session::put('latitude','');
                 session::put('lontitude','');
                 return view("auth.register-success",array("msg"=>$msg,"file_name"=>$pdf_file));
                
               }else{
                    return redirect('become-a-star');
               }
           }
      }
     protected function create(array $data)
     {
       
                //getting from global setting
                $site_email=GlobalValues::get('site-email');
                $site_title=GlobalValues::get('site-title');
               //Variable Declarations
                $arr_userinformation = array();  
                $arr_useraddress = array();  
                $hasAddress=0;
        
                /*** here we creating user in user table only with email and password fileds **/
        
                $created_user = User::create([
                    'email' => $data['email'],
                    'password' => ($data['password']),
                ]);
		
                
		// update User Information
		/*
		* Adjusted user specific columns, which may not passed on front end and adjusted with the default values
		*/
		$data["user_type"] = isset($data["user_type"])?$data["user_type"]:"2";			// 1 may have several mean as per enum stored in the database. Here we 
		$data["role_id"] = isset($data["role_id"])?$data["role_id"]:"2";									// 2 means registered user
		$data["user_status"] = isset($data["user_status"])?$data["user_status"]:"0";		// 0 means not active
		$data["gender"] = isset($data["gender"])?$data["gender"]:"3";					// 3 means not specified
		$data["profile_picture"]= isset($data["profile_picture"])?$data["profile_picture"]:"";
		$data["facebook_id"]= isset($data["facebook_id"])?$data["facebook_id"]:"";
		$data["twitter_id"]= isset($data["twitter_id"])?$data["twitter_id"]:"";
		$data["google_id"]= isset($data["google_id"])?$data["google_id"]:"";
		$data["linkedin_id"]= isset($data["linkedin_id"])?$data["linkedin_id"]:"";
		$data["pintrest_id"]= isset($data["pintrest_id"])?$data["pintrest_id"]:"";
		$data["user_birth_date"]= isset($data["user_birth_date"])?$data["user_birth_date"]:"";
		$data["first_name"]= isset($data["first_name"])?$data["first_name"]:"";
		$data["last_name"]= isset($data["last_name"])?$data["last_name"]:"";
		$data["about_me"]= isset($data["about_me"])?$data["about_me"]:"";
		$data["user_phone"]= isset($data["user_phone"])?$data["user_phone"]:"";
		$data["user_mobile"]= isset($data["user_mobile"])?$data["user_mobile"]:"";
		
                //getting address Information.
                
                $data["addressline1"]= isset($data["addressline2"])?$data["addressline1"]:"";
                $data["addressline2"]= isset($data["addressline2"])?$data["addressline2"]:"";
                $data["user_country"]= isset($data["user_country"])?$data["user_country"]:NULL;
                $data["user_state"]= isset($data["user_state"])?$data["user_state"]:NULL;
                $data["user_city"]= isset($data["user_city"])?$data["user_city"]:NULL;
                $data["suburb"]= isset($data["suburb"])?$data["suburb"]:"";
                $data["user_custom_city"]= isset($data["user_custom_city"])?$data["user_custom_city"]:"";
                $data["zipcode"]= isset($data["zipcode"])?$data["zipcode"]:"";
               
                /** user information goes here ****/
                
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
		$arr_userinformation["gender"] = $data["gender"];
		$arr_userinformation["activation_code"] = "";													// By default it'll be no activation code
		$arr_userinformation["facebook_id"] = $data["facebook_id"];
		$arr_userinformation["twitter_id"] = $data["twitter_id"];
		$arr_userinformation["google_id"] = $data["google_id"];
		$arr_userinformation["linkedin_id"] = $data["linkedin_id"];
		$arr_userinformation["pintrest_id"] = $data["pintrest_id"];
		$arr_userinformation["user_birth_date"] = $data["user_birth_date"];
		$arr_userinformation["first_name"] = $data["first_name"];
		$arr_userinformation["last_name"] = $data["last_name"];
		$arr_userinformation["about_me"] = $data["about_me"];
		$arr_userinformation["user_phone"] = $data["user_phone"];
		$arr_userinformation["user_mobile"] = ltrim($data["user_mobile"], '0');
		$arr_userinformation["user_status"] = $data["user_status"];
		$arr_userinformation["user_type"] = $data["user_type"];
		$arr_userinformation["user_id"] = $created_user->id;
		
		$updated_user_info = UserInformation::create($arr_userinformation);
                
                
                /** user addesss informations goes here ****/
                if($data["addressline1"]!='')
                {
                     $arr_useraddress["addressline1"] = $data["addressline1"];
                     $hasAddress=1;
                }
                if($data["addressline2"]!='')
                {
                     $arr_useraddress["addressline2"] = $data["addressline2"];
                     $hasAddress=1;
                }
                if($data["user_country"]!='')
                {
                     $arr_useraddress["user_country"] = $data["user_country"];
                      $hasAddress=1;
                }
                  if($data["user_state"]!='')
                {
                     $arr_useraddress["user_state"] = $data["user_state"];
                      $hasAddress=1;
                }
                if($data["user_city"]!='')
                {
                     $arr_useraddress["user_city"] = $data["user_city"];
                      $hasAddress=1;
                }
                  if($data["suburb"]!='')
                {
                     $arr_useraddress["suburb"] = $data["suburb"];
                     $hasAddress=1;
                }
                if($data["user_custom_city"]!='')
                {
                     $arr_useraddress["user_custom_city"] = $data["user_custom_city"];
                      $hasAddress=1;
                }
                if($data["zipcode"]!='')
                {
                     $arr_useraddress["zipcode"] = $data["zipcode"];
                     $hasAddress=1;
                }
                if($created_user->id!='')
                {
                     $arr_useraddress["user_id"] = $created_user->id;
                              
                }
              
                if($hasAddress)
                {
                    UserAddress::create($arr_useraddress);
                }
                
		// asign role to respective user		
		$userRole = Role::where("slug","registered.user")->first();
		
		$created_user->attachRole($userRole);
		
                //sending an email to the user on successfull registration.
                
                $arr_keyword_values = array();
                $activation_code=$this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] =  $updated_user_info->last_name;
                $arr_keyword_values['MOBILE'] =  $updated_user_info->last_name;
                $arr_keyword_values['SITE_TITLE'] =  $site_title;
              
                $locale=\App::getLocale();
                $tempate_name="emailtemplate::star-registration-successfull-".$locale;
                Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($created_user,$site_email,$site_title)  {
				
                    $message->to( $created_user->email, $created_user->name )->subject("Registration Successful!")->from($site_email,$site_title);
				
		});
               //sendimng email to admin
                 Mail::send('emailtemplate::star-registration-successful-admin',$arr_keyword_values, function ($message) use ($site_email,$site_title)  {
				
                    $message->to($site_email,"BAGGI")->subject("A New Star User Request")->from($site_email,"BAGGI");
				
		});
		return $created_user;
		
    }
    private function generateReferenceNumber()
   {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0, 0xffff), mt_rand(0, 0xffff),mt_rand(0, 0xffff),mt_rand(0, 0x0fff) | 0x4000,mt_rand(0, 0x3fff) | 0x8000,mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) );
  
   }
   public function printPdf($user_id)
   {
       
        $locale=\App::getLocale();
       $userData=  UserInformation::where('user_type','2')->where('user_id',$user_id)->get();
       $pdf_title=Lang::choice('website_keywords.become_star',\App::getLocale());
        $lg = Array();
        $lg['a_meta_charset'] = 'UTF-8';

        // set some language-dependent strings (optional)
        PDF::setLanguageArray($lg);
        
        PDF::SetTitle($pdf_title);
        PDF::AddPage();
        PDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        
        $logo_images=url('public/media/front/images/logo_dlvr4all.jpg');
       PDF::SetFont('dejavusans', '', 10);
       foreach($userData as $userDetails)
       {
           
           ob_start();
           $html1='';
           $html2='';
           $service_html='';
           $delivery_methods_html='';
           $dt = new DateTime(date('Y-m-d H:i:s'));
           $country_name="";
           $state_name="";
           $city_name="";
           $user_address=UserAddress::where('user_id',$userDetails->user_id)->first();
           //get timezone as per country
            $countryInfo=Country::where('id',$user_address->user_country)->first();
           if(count($countryInfo)>0)
           {
               $country_name=$countryInfo->name;
           }
            $stateInfo=State::where('id',$user_address->user_state)->first();
             $cityInfo=City::where('id',$user_address->user_city)->first();
              if(count($stateInfo)>0)
           {
               $state_name=$stateInfo->name;
           }
            if(count($cityInfo)>0)
           {
               $city_name=$cityInfo->name;
           }
            if(count($countryInfo)>0)
            {
                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                 //$tz = new DateTimeZone('UTC'); // or whatever zone you're after
                 $dt->setTimezone($tz);
            }
                
        $date2_val= $dt->format('Y-m-d H:i:s');   
       
        $categories=Category::where('status','1')->get();
       // PDF::Image($logo_images, 50, 50, 100, '', '', 'http://www.dlvr4all.com', '', false, 300);
        $natility_name="";
        $nationality_details=Nationality::where('id',$userDetails->nationality)->first();
        if(count($nationality_details)>0)
        {
            $natility_name=$nationality_details->country_name;
        }
       
            //PDF::SetFont('helvetica', '', 10);
            $service_html='<table border="1"  width="100%" style="border-top:0px solid #000;border:1px solid #000;" cellpadding="10"   cellspacing="0"  >';
            $service_html.="<tr>";
            $service_html.='<td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Service you want to provide</td>';
            if(count($categories)>0)
            {
                foreach($categories as $category)
                {       
                    $service_html.='<td valign="middle" width="23%" style="padding:20px;"><input style="padding:0px;" name="'.$category->name.'" type="checkbox" value="1"/> '." ".$category->name.'</td>';
                }
            }
            $service_html.="</tr></table>";

            $delivery_methods_html="";
            $delivery_methods_html.='<table  border="1"  width="100%" style="border-top:0px solid #000;border:1px solid #000;" cellpadding="10"   cellspacing="0"  >';

            if(count($categories)>0)
            {
                $k=0;

                foreach($categories as $category)
                {    
                    $main_flag=0;
                     $delivery_methods_html.='<tr>';
                    if($k==0)
                         {
                             $delivery_methods_html.='<td rowspan="6" colspan="1"  style="border:1px solid #000;" valign="bottom" width="15%" style="font-weight:bold; padding:20px;">Delivery Methods</td> ';
                         }

                  if($k==1)
                    {
                       $delivery_methods_html.='<td rowspan="2" valign="middle" width="15%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                    }else{
                         $delivery_methods_html.='<td  rowspan="2" valign="bottom" width="15%" style="font-weight:bold; padding:20px;">'.$category->name.'</td>';
                    }
                    $category_id=$category->id;
                    $countryserviceData = CountryServices::where('country_id', $countryInfo->id)->orderBy('sort_index','asc')->with('serviceInformation')->get(); 
                    $countryserviceData=$countryserviceData->reject(function($countyservice) use($category_id)
                    {
                       return ($countyservice->serviceInformation->category_id!=$category_id ||$countyservice->serviceInformation->parent_id>0 || $countyservice->serviceInformation->id=='17');
                    })->values();


                    if(count($countryserviceData)>0)
                    {
                        $l=0;
                        $flag=0;
                        foreach($countryserviceData as $countryservice)
                        {    
                            $l++;   
                            if( $l==4)
                           {
                                $flag=1;
                                $delivery_methods_html.='</tr>';
                               $delivery_methods_html.='<tr>';
                           }
                            $delivery_methods_html.='<td valign="middle" width="23%" style="padding:20px;"><input style="padding:0px;" name="'.$countryservice->id.'" value="1" type="checkbox"> '.$countryservice->serviceInformation->name.'</td>';

                           if(count($countryserviceData)==$l && $flag==1)
                           {
                               $main_flag=1;
                               $delivery_methods_html.='</tr>';
                           }

                        }
                    }
                    if($main_flag==0)
                    {
                        $delivery_methods_html.="</tr>";
                    }
                   $k++;

                   }

                 }

            $delivery_methods_html.="</table>";

            $html1 = '
                <table  width="100%" cellpadding="10" cellspacing="0"  style="line-height:0;">
                <tr>
                  <td align="left" valign="middle" width="50%" style="padding:20px;"><img width="100" src="'.$logo_images.'"></td>
                  <td align="right" valign="middle" width="50%" style="padding:20px; padding-right:50px;"><p style="margin:0;">Date: '.$date2_val.'</p></td>
                 </tr>

              </table>
                <table  width="100%" cellpadding="5"  cellspacing="0"  style="line-height:0;margin-bottom:20px">
                <tr>
                <td valign="middle" align="center"><h1 style="margin:0; font-weight:bold;">Star Registration Form</h1></td>

                </tr>
                <tr>
                    <td ></td>
                </tr>
              </table>

              <table border="1"  width="100%" style="border:1px solid #000;" cellpadding="10"   cellspacing="0"  >

                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Full Name:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$userDetails->first_name." ".$userDetails->last_name.' ('.$userDetails->user_id.')</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Mobile Number:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$userDetails->mobile_code."".$userDetails->user_mobile.'</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Email:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$userDetails->user->email.'</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Nationality:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.($natility_name).'</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Country:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;">'.$country_name.'</p></td>

                </tr>
                 <tr>
                  <td valign="middle" width="30%" style="font-weight:bold; padding:20px;">Region:</td>
                  <td valign="middle" width="69%" style="padding:20px;"><p style="margin:0;;">'.$state_name.'</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">City:</td>
                  <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;">'.$city_name.'</p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Address:</td>
                  <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;">'.$user_address->address.'</p></td>

                </tr>
                 <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Working Time:</td>
                  <td valign="middle" width="69%" style="padding:20px;" ><p style="margin:0;"></p></td>

                </tr>
                <tr>
                  <td valign="middle" width="30%" style="font-weight:bold;padding:20px;">Reference:</td>
                  <td valign="middle" width="35%" style="padding:20px;" ><p style="margin:0;">Name:</p></td>
                  <td valign="middle" width="34%" style="padding:20px;" ><p style="margin:0;">Mobile Number:</p></td>

                </tr>
              </table>
              '.$service_html.'
              '.$delivery_methods_html.'
            ';

            PDF::writeHTML($html1, true, false, true, false, '');
          
            $html2='
              <table cellpadding="10" cellspacing="0" width="100%">
                 <tr>
                  <td valign="middle" style="font-weight:bold; text-decoration:underline; padding:10px;">Required documents: </td>
                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 1- Personal Photo. </td>
                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 2- A copy of Civil ID </td>
                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;">-&nbsp;&nbsp; 3- A copy of Driving License </td>
                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;"><span style="text-decoration:underline;"> I, the undersigned, acknowledge that all data contained in this form are correct and I take full responsibility if otherwise proven.</span></td>
                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;"><b>Name</b>:</td>

                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;"><b>Date</b>:</td>

                </tr>
                <tr>
                  <td valign="middle" style="padding:10px;"><b>Your Signature</b>:</td>

                </tr>
              </table>
            ';

            PDF::AddPage();
            PDF::writeHTML($html2, true, false, true, false, '');
            //PDF::Write(0, 'Hello World');
            $pdf_file_name=$pdf_file_name1=$userDetails->user_id.".pdf";
            $pdf_file_name_url=$pdf_file_name;
            $pdf_file_name='public/star_registration_admin/'.$pdf_file_name;


            $file_path=  realpath(dirname(__DIR__).'/../../..');
            $file_path=$file_path."/".$pdf_file_name;
            PDF::Output($file_path,"F");
            
          return view("auth.pdf-success",array("file_name"=>$pdf_file_name1));
                
   }}
   
   
	
	
}
