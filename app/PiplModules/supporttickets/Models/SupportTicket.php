<?php
namespace App\PiplModules\supporttickets\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model 
{
    
	protected $fillable = ['is_read','is_admin_read','support_subject','support_attachment','ticket_unique_id','added_by','order_id','status'];
        
        public function UserInformation()
	{
		return $this->belongsTo('App\UserInformation','added_by','user_id');
	}
        public function TicketConversation()
	{
		return $this->hasMany('App\PiplModules\supporttickets\Models\supportTicketConversation','ticket_id','id');
	}
        
        
        public function orderInformation()
	{
		return $this->belongsTo('App\PiplModules\orderdetails\Models\Order','order_id','id');
	}
        
        public function assignTicketInformation()
	{
		return $this->hasOne('App\PiplModules\supporttickets\Models\supportTicketAssignInformations','ticket_id','id');
	}
}