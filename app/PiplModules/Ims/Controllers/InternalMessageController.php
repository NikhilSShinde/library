<?php

namespace App\PiplModules\Ims\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\Ims\Models\InternalMessage;
use App\PiplModules\Ims\Models\InternalMessageStatus;
use App\PiplModules\Ims\Models\Conversation;
use App\PiplModules\Ims\Models\ConversationUser;
use Mail;

class InternalMessageController extends Controller
{

     public function __construct()
    {
        $this->middleware('auth');
    }
	// treat as inbox
	public function index()
	{
            if (Auth::user())
            {
			$all_conversations = ConversationUser::all();
			$user_conversation_collection = $all_conversations->filter(function($conv_user){
						
						return $conv_user->user_id === Auth::user()->id;	
			});
			
			$user_conversations = array();
			
			foreach($user_conversation_collection as $uc)
			{
				if(!in_array($uc->conversation_id,$user_conversations))
				{
					$user_conversations[]=$uc->conversation_id;
				}
			}
		
			$messages = InternalMessage::all();
			$con_messages = $messages->filter(function($msg) use($user_conversations){
				
				$statuses = $msg->status()->where('user_id',Auth::user()->id);
				


				return (in_array($msg->conversation->id , $user_conversations) and ($msg->sender_id !== Auth::user()->id) and( in_array($statuses->first()->status,array(0,1))));
				
				});
			
			$con_messages = $con_messages->sortByDesc('created_at');
			
			// take only latest messages from conversation
			
			$added_conversations = array();
			$inbox_messages = array();
			foreach($con_messages as $message)
			{
				if(!in_array($message->conversation->id,$added_conversations))
				{
					
					$statuses = $message->status()->where('user_id',Auth::user()->id);
					$inbox_messages[]=new msgClass($message,$statuses->first()->status);
					
					$added_conversations[]=$message->conversation->id;
				}
			}
			
			// filter options
			
			$arr_filter_options = array();
			$arr_filter_options['projects']=array();
			$arr_filter_options['users']=array();
			$arr_filter_options['date_range']=array();
			
			foreach($inbox_messages as $message_obj)
			{
					if(!in_array($message_obj->message->conversation->title,$arr_filter_options['projects']))
					{
						$arr_filter_options['projects'][]=$message_obj->message->conversation->title;
					}
					
					if(!in_array($message_obj->message->sender->id,array_keys($arr_filter_options['users'])))
					{
						$arr_filter_options['users']["".$message_obj->message->sender->id]=$message_obj->message->sender->userInformation->first_name." ".$message_obj->message->sender->userInformation->last_name;
					}
					
					if(!in_array(date("d M Y",strtotime($message_obj->message->created_at)),$arr_filter_options['date_range']))
					{
						$arr_filter_options['date_range'][]=date("d M Y",strtotime($message_obj->message->created_at));
					}
			}
			
			return view("ims::inbox",array('messages'=>$inbox_messages,'filters'=>$arr_filter_options));
		}else{
                    $errorMsg  = "Please login to continue.";
                    Auth::logout();
                    return redirect("login")->with("login-error",$errorMsg);
             }	
	}
	
	public function sentFolder()
	{
			$messages = InternalMessage::where('sender_id',Auth::user()->id)->get();
			$con_messages = $messages->filter(function($msg) {
				
				$statuses = $msg->status()->where('user_id',Auth::user()->id);
				


				return in_array($statuses->first()->status,array(0,1));
				
				});
			
			$con_messages = $con_messages->sortByDesc('created_at');
			
			// take only latest messages from conversation
			
			$added_conversations = array();
			$sent_messages = array();
			foreach($con_messages as $message)
			{
				if(!in_array($message->conversation->id,$added_conversations))
				{
					$statuses = $message->status()->where('user_id',Auth::user()->id);
					$sent_messages[]=new msgClass($message,$statuses->first()->status);
					
					$added_conversations[]=$message->conversation->id;
				}
			}
			
			// filter options
			
			$arr_filter_options = array();
			$arr_filter_options['projects']=array();
			$arr_filter_options['users']=array();
			$arr_filter_options['date_range']=array();
			
			foreach($sent_messages as $message_obj)
			{
					if(!in_array($message_obj->message->conversation->title,$arr_filter_options['projects']))
					{
						$arr_filter_options['projects'][]=$message_obj->message->conversation->title;
					}
					
					$cov_users = $message_obj->message->conversation->users;
					foreach($cov_users as $conv_user)
					{
						if($conv_user->user_id != $message_obj->message->sender_id)
						{
							$recipient = $conv_user;
							break;
						}
					}
					
					if(!in_array($recipient->user->id,array_keys($arr_filter_options['users'])))
					{
						$arr_filter_options['users']["".$recipient->user->id]=$recipient->user->userInformation->first_name." ".$recipient->user->userInformation->last_name;
					}
					
					if(!in_array(date("d M Y",strtotime($message_obj->message->created_at)),$arr_filter_options['date_range']))
					{
						$arr_filter_options['date_range'][]=date("d M Y",strtotime($message_obj->message->created_at));
					}
			}
			

			return view("ims::sent",array('messages'=>$sent_messages,'filters'=>$arr_filter_options));
	}
	
	public function trashFolder()
	{
			$messages = InternalMessage::all();
			$con_messages = $messages->filter(function($msg) {
				
			$statuses = $msg->status()->where('user_id',Auth::user()->id);

				return ($statuses->count() > 0 && in_array($statuses->first()->status,array(2)));
				
				});
			
			$con_messages = $con_messages->sortByDesc('created_at');
			

			$trash_messages = array();
			foreach($con_messages as $message)
			{
					$statuses = $message->status()->where('user_id',Auth::user()->id);
					$trash_messages[]=new msgClass($message,$statuses->first()->status);
			}
			
			// filter options
			
			$arr_filter_options = array();
			$arr_filter_options['projects']=array();
			$arr_filter_options['users']=array();
			$arr_filter_options['date_range']=array();
			
			foreach($trash_messages as $message_obj)
			{
					if(!in_array($message_obj->message->conversation->title,$arr_filter_options['projects']))
					{
						$arr_filter_options['projects'][]=$message_obj->message->conversation->title;
					}
					
					if(!in_array($message_obj->message->sender->id,array_keys($arr_filter_options['users'])))
					{
						$arr_filter_options['users']["".$message_obj->message->sender->id]=$message_obj->message->sender->userInformation->first_name." ".$message_obj->message->sender->userInformation->last_name;
					}
					
					if(!in_array(date("d M Y",strtotime($message_obj->message->created_at)),$arr_filter_options['date_range']))
					{
						$arr_filter_options['date_range'][]=date("d M Y",strtotime($message_obj->message->created_at));
					}
			}

			return view("ims::trash",array('messages'=>$trash_messages,'filters'=>$arr_filter_options));
	}
	
	public function conversationMessages(Request $request,$conversation_id)
	{
			if($request->method()=="POST")
			{
				$sender_id = Auth::user()->id;
				
				//now create message object
				$arr_msg_data = array();
				
				$arr_msg_data['subject'] =  $request->subject;
				$arr_msg_data['content'] =  $request->content;
				
				// check whethere attachements
				$attachments=array();
				if($request->hasFile('attachment'))
				{
					
					$uploaded_files = $request->file('attachment');
					
					foreach($uploaded_files as $uploaded_file)
					{
					
						$extension = $uploaded_file->getClientOriginalExtension();
						$new_file_name = str_replace(".","-",microtime(true)).".".$extension;
				
						Storage::put('public/ims/'.$conversation_id."/".$new_file_name,file_get_contents($uploaded_file->getRealPath()));
						
						$attachments[] = array("original_name"=>$new_file_name,"display_name"=> $uploaded_file->getClientOriginalName());
					
					}
					
				}
				
				$arr_msg_data["attachments"] = $attachments;
				$arr_msg_data["sender_id"] = intval($sender_id);
				$arr_msg_data["conversation_id"] = $conversation_id;
				
				$internal_message = InternalMessage::create($arr_msg_data);
				
				// now add status for the message
				
				InternalMessageStatus::create(
																						array('internal_message_id'=>$internal_message->id,
																						'user_id'=>$sender_id,
																						'self'=>1,
																						'status'=>0)
																			);
																			
				InternalMessageStatus::create(
																						array('internal_message_id'=>$internal_message->id,
																						'user_id'=>$request->recipient,
																						'self'=>0,
																						'status'=>0)
																			);
				
				
				
				// Done, send message
				
				return redirect($request->url())->with('status','Message sent successfully');
				
			}
			else
			{
		
			// get last message from conversation, which was the initiation message
			
			$messages = InternalMessage::all();
			$con_messages = $messages->filter(function($msg) use($conversation_id){
				
				$statuses = $msg->status()->where('user_id',Auth::user()->id);
				
				return (($msg->conversation->id === intval($conversation_id)) and($statuses->count()>0 &&  in_array($statuses->first()->status,array(0,1))));
				
				});
			
			if($con_messages->count() < 1)
			{
				// this user doesn't belongs to this conversation. Redirect to IMS
				
				return redirect('ims');
			}
			
			$con_messages = $con_messages->sortByDesc('created_at');
			
			// take only latest messages from conversation
			

			$conversation_messages = array();
			foreach($con_messages as $message)
			{
					$message_statuses = $message->status->where('user_id',Auth::user()->id);
					$message_status = $message_statuses->first();
					$message_status -> status = 1;
					$message_status ->save();
					
					$conversation_messages[]=new msgClass($message);
			}

			$initial_message = array_pop($conversation_messages);
			
			// decide reply to
			$reply_to = 0;
			$participants = ConversationUser::where("conversation_id",$conversation_id)->get();

			foreach($participants as $participant)
			{
				if($participant->user_id !== Auth::user()->id)
				{
					$reply_to = $participant->user_id;
					break;
				}
			}
			
			return view("ims::conversation-details",array('messages'=>$conversation_messages,'initial_message'=>$initial_message,'reply_to'=>$reply_to));
			}

	}
	
	public function compose(Request $request)
	{
		if($request->method() == "POST")
		{
				// first create conversation enter
				
				$sender_id = Auth::user()->id;
				
				$conversation = Conversation::create(array('title'=>$request->project));
				
								
				//now create message object
				$arr_msg_data = array();
				
				$arr_msg_data['subject'] =  $request->subject;
				$arr_msg_data['content'] =  $request->content;
				
				// check whethere attachements
				$attachments=array();
				if($request->hasFile('attachment'))
				{
					
					$uploaded_files = $request->file('attachment');
					
					foreach($uploaded_files as $uploaded_file)
					{
					
						$extension = $uploaded_file->getClientOriginalExtension();
						$new_file_name = str_replace(".","-",microtime(true)).".".$extension;
				
						Storage::put('public/ims/'.$conversation->id."/".$new_file_name,file_get_contents($uploaded_file->getRealPath()));
						
						$attachments[] = array("original_name"=>$new_file_name,"display_name"=> $uploaded_file->getClientOriginalName());
					
					}
					
				}
				
				$arr_msg_data["attachments"] = $attachments;
				$arr_msg_data["sender_id"] = intval($sender_id);
				$arr_msg_data["conversation_id"] = $conversation->id;
				
				$internal_message = InternalMessage::create($arr_msg_data);
				
				// now add status for the message
				
				InternalMessageStatus::create(
                                                    array('internal_message_id'=>$internal_message->id,
                                                    'user_id'=>$sender_id,
                                                    'self'=>1,
                                                    'status'=>0)
                               );

                                InternalMessageStatus::create(
                                            array('internal_message_id'=>$internal_message->id,
                                            'user_id'=>$request->recipient,
                                            'self'=>0,
                                           'status'=>0)
                                );

				// now add users
				
				ConversationUser::create(array('conversation_id'=>$conversation->id,'user_id'=>$sender_id));
				ConversationUser::create(array('conversation_id'=>$conversation->id,'user_id'=>$request->recipient));
				
				// Done, send message
				
				return redirect($request->url())->with('status','Message sent successfully');
				
		}
		else
		{
			$users = \App\User::all(); // temporary users, must replace with actual project users
			$projects = array('project 1','project 2','project 3','project 4'); // temporary projects, must replace with actual projects
			return view("ims::compose",array('users'=>$users,'projects'=>$projects));
		}
	}
	
	public function trashMessage(Request $request)
	{
		 if( $request->method() == "POST")
		 {
			 $message_id = $request->msg_id;
	
				if(!empty($message_id) && intval($message_id) > 0)
				{
					$message = InternalMessage::find($message_id );
					if($message)
					{
						$message_status = $message->status->where('user_id',Auth::user()->id)->first();
						
						$message_status->status = 2; // Moved to trash
						$message_status->save();
						
						return redirect(url('ims/conversation/'.$message ->conversation_id))->with('status','Message deleted successfully!');
					}
					else
					{
						abort(403);
					}
				}
				else
				{
					abort(403);  
				}
		  }
		  else
		  {
				abort(403);  
		  }
	}
	
	public function trashConversation(Request $request)
	{
		if( $request->method() == "POST")
		 {
			 $conversation_id = $request->conversation_id;
	
				if(!empty($conversation_id) && intval($conversation_id) > 0)
				{
					$messages = InternalMessage::where('conversation_id',$conversation_id)->get();
					
					if($messages)
					{
						foreach($messages as $message)
						{
							$message_status = $message->status->where('user_id',Auth::user()->id)->first();
							
							$message_status->status = 2; // Moved to trash
							$message_status->save();
							
						
						}
							return redirect(url('ims'))->with('status','Conversation deleted successfully!');
					}
					else
					{
						abort(403);
					}
				}
				else
				{
					abort(403);  
				}
		  }
		  else
		  {
				abort(403);  
		  }
	}
	
	public function restoreMessage(Request $request)
	{
		 if( $request->method() == "POST")
		 {
			 $message_id = $request->msg_id;
	
				if(!empty($message_id) && intval($message_id) > 0)
				{
					$message = InternalMessage::find($message_id );
					if($message)
					{
						$message_status = $message->status->where('user_id',Auth::user()->id)->first();
						
						$message_status->status = 1; // Read Message
						$message_status->save();
						
						return redirect(url('ims/conversation/'.$message ->conversation_id))->with('status','Message restored successfully!');
					}
					else
					{
						abort(403);
					}
				}
				else
				{
					abort(403);  
				}
		  }
		  else
		  {
				abort(403);  
		  }
	}
	public function deleteMessagePermanently(Request $request)
	{
		 if( $request->method() == "POST")
		 {
			 $message_id = $request->msg_id;
	
				if(!empty($message_id) && intval($message_id) > 0)
				{
					$message = InternalMessage::find($message_id );
					if($message)
					{
						$message_status = $message->status->where('user_id',Auth::user()->id)->first();
						
						$message_status->status = 3; // Permanent Delete
						$message_status->save();
						
						return redirect(url('ims/trash'))->with('status','Message deleted successfully!');
					}
					else
					{
						abort(403);
					}
				}
				else
				{
					abort(403);  
				}
		  }
		  else
		  {
				abort(403);  
		  }
	}
}

class msgClass{
	public $message = "";
	public $message_status = 0;
	
	public function __construct($message,$status='')
	{
		$this->message = $message;
		
		if($status!="")
		$this->message_status = $status;
	}
	
	
}