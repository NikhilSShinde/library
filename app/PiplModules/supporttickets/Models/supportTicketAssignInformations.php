<?php

namespace App\PiplModules\supporttickets\Models;

use Illuminate\Database\Eloquent\Model;

class supportTicketAssignInformations extends Model {

    protected $fillable = ['ticket_id', 'assign_by', 'assign_to'];

    public function UserInformation() {
        return $this->belongsTo('App\UserInformation', 'assign_to', 'user_id');
    }

}
