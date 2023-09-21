<?php

namespace App\PiplModules\loan\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model {

    protected $fillable = [
        'driver_id',
        'receipt_bp_type',
        'loan_account',
        'loan_amount',
        'vehicle_no',
        'business_partner_type',
        'receipt_mode',
        'instrument_no',
        'issuning_bank',
        'auto_manual',
        'receipt_amount',
        'received_from',
        'marker_remark',
        'customer_name',
        'business_partner_name',
        'receipt_date',
        'instrument_date',
        'issuing_branch',
        'receipt_no',
        'tds_amount',
        'deposit_bank_account',
        'contact_no',
        'author_remarks',
        'payment_method',
        'intrest',
        'terms'
    ];

    public function driverDetail() {
        return $this->hasOne('App\UserInformation', 'user_id', 'driver_id');
    }

}
