<?php
namespace App\PiplModules\supporttickets\Models;

use Illuminate\Database\Eloquent\Model;

class TicketDescription extends Model 
{

       protected $fillable = ['ticket_id','description','posted_by'];
       protected $table='support_ticket_conversations';
}