<?php

namespace App\PiplModules\supporttickets\Models;

use Illuminate\Database\Eloquent\Model;

class supportTicketConversation extends Model {

    protected $fillable = ['ticket_id', 'posted_by', 'description'];

    public function UserInformation() {
        return $this->belongsTo('App\UserInformation', 'posted_by', 'user_id');
    }

}
