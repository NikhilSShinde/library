<?php
namespace App\PiplModules\SocialConnect\Controllers;
use App\User;
use App\UserInformation;
use App\PiplModules\Roles\Models\Role;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Socialite;
use GlobalValues;
use Mail;
class SocialConnectController extends Controller
{ 
  
	
	public function redirectToFacebook()
        {
            return Socialite::driver('facebook')->redirect();
        }

            public function redirectToTwitter()
        {
            return Socialite::driver('twitter')->redirect();
        }

            public function redirectToGoogle()
        {
            return Socialite::driver('google')->redirect();
        }

            public function redirectToInstagram()
        {
            return Socialite::driver('instagram')->redirect();
        }

         public function handleFacebookCallback()
        {
          
                $user = Socialite::driver('facebook')->user();
		$email = $user->email;
		$first_name="";
                $last_name="";
                if($user->name!='')
                {
                   $full_name= explode(" ",  $user->name);
                 
                   $first_name = isset($full_name[0])?$full_name[0]:'';  
                   $last_name = isset($full_name[1])?$full_name[1]:'';  
                }
               
		$gender = ($user->user['gender']=="male")?"1":($user->user['gender']=="female")?"2":"3";
		$id = $user->id;
		
		$redirect_url =  $this->handleSocialConnect($email,$gender,$first_name,$last_name,$id,"facebook");
		
		return redirect($redirect_url);
        }
	
	public function handleTwitterCallback()
      {
                 $user = Socialite::driver('twitter')->user();
		// TO login and sign up
		$email = $user->name."_".$user->id."@twitter.com";
                $first_name="";
                $last_name="";
                if($user->name!='')
                {
                   $full_name= explode(" ",  $user->name);
                   $first_name = isset($full_name[0])?$full_name[0]:'';  
                   $last_name = isset($full_name[1])?$full_name[1]:'';  
                }
               
		$gender = "3";
		$id = $user->id;
		
		$redirect_url = $this->handleSocialConnect($email,$gender,$first_name,$last_name,$id,"twitter");
		return redirect($redirect_url);
	}
	
	public function handleGoogleCallback()
        {
          
                $user = Socialite::with('google')->user();
                
		// TO login and sign up
		$email = $user->email;
		$first_name="";
                $last_name="";
                if($user->name!='')
                {
                   $full_name= explode(" ",  $user->name);
                   $first_name = isset($full_name[0])?$full_name[0]:'';  
                   $last_name = isset($full_name[1])?$full_name[1]:'';  
                }
		$gender = ($user->user['gender']=="male")?"1":($user->user['gender']=="female")?"2":"3";
		$id = $user->id;
		$redirect_url = $this->handleSocialConnect($email,$gender,$first_name,$last_name,$name,$id,"google");
		return redirect($redirect_url);
	}
	
	public function handleInstagramCallback()
	{
		$user = Socialite::driver('instagram')->user();
		
		$email = $user->user['username']."_".$user->id."@instagram.com";
		$first_name="";
                $last_name="";
                if($user->name!='')
                {
                   $full_name= explode(" ",  $user->name);
                   $first_name = isset($full_name[0])?$full_name[0]:'';  
                   $last_name = isset($full_name[1])?$full_name[1]:'';  
                }
		$gender = "3";
		$id = $user->id;
		
		$redirect_url = $this->handleSocialConnect($email,$gender,$first_name,$last_name,$id,"instagram");
		return redirect($redirect_url);
		
	}
	
	private function handleSocialConnect($email,$gender,$first_name,$last_name,$social_id,$social_type)
	{
		
                //getting from global setting
                $site_email=GlobalValues::get('site-email');
                $site_title=GlobalValues::get('site-title');
                
		$newUser = User::where('email',$email)->get();
		if($newUser->count()>0)
		{
			// user is already exists, make login
			\Auth::loginUsingId($newUser->first()->id);
			return 'profile';
		}
		else
		{
			// make registration
			
                    $password=str_random(8);
                    $created_user = User::create([
                        'email' => $email,
                        'password' =>$password,
                    ]);
		
                    $arr_userinformation = array();
		
                    $arr_userinformation["profile_picture"] = "";
                    $arr_userinformation["gender"] = $gender;
                    $arr_userinformation["activation_code"] = "";													// By default it'll be no activation code
		
                    if($social_type=="facebook")
                    {
                         $arr_userinformation["facebook_id"] = $social_id;
                    }
		
                    if($social_type=="twitter")
                    {
                            $arr_userinformation["twitter_id"] = $social_id;
                    }
		
                    if($social_type=="google")
                    {
                            $arr_userinformation["google_id"] = $social_id;
                    }

                    $arr_userinformation["linkedin_id"] = "";
                    $arr_userinformation["pintrest_id"] = "";
                    $arr_userinformation["user_birth_date"] = "";
                    $arr_userinformation["first_name"] = $first_name;
                    $arr_userinformation["last_name"] = $last_name;
                    $arr_userinformation["about_me"] = "";
                    $arr_userinformation["user_phone"] = "";
                    $arr_userinformation["user_mobile"] = "";
                    $arr_userinformation["user_status"] = "1"; // Active and Verified
                    $arr_userinformation["user_type"] = "2";
                    $arr_userinformation["user_id"] = $created_user->id;
		
		
                    $updated_user_info = UserInformation::create($arr_userinformation);
		
		// asign role to respective user
		// asign role to respective user		
		$userRole = Role::where("slug","registered.user")->first();
		
		$created_user->attachRole($userRole);
		 //sending an email to the user on successfull registration.
               
                if($social_type=="facebook" || $social_type=="google")
                {
                    $arr_keyword_values = array();
                    //Assign values to all macros
                    $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                    $arr_keyword_values['LAST_NAME'] =  $updated_user_info->last_name;
                    $arr_keyword_values['EMAIL'] =      $created_user->email;
                    $arr_keyword_values['PASSWORD'] = $password;

                    Mail::send('emailtemplate::registration-successfull-social',$arr_keyword_values, function ($message) use ($created_user,$social_type,$site_email,$site_title)  {

                            $message->to( $created_user->email, $created_user->first_name )->subject("Registration Successful Using "+$social_type)->from($site_email,$site_title);

                    });
                }
		\Auth::loginUsingId($created_user->id);
		return 'profile';
		
		}
		
	}
	
	public function socialMessage($social_type)
	{
			$is_email_found = 1;
			if($social_type=="twitter" || $social_type=="instagram" )
			{
				$is_email_found = 0;
			}
			
			return view("socialconnect::social-message",array('social_type'=>$social_type,"is_email_found"=>$is_email_found));
	}

}
