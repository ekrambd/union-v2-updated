<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riderpayout extends Model
{
    use HasFactory; 
    protected $fillable = [
	    'rider_id',
	    'account_holder_name',
	    'personal_address',
	    'dob',
	    'identification_type',
	    'identification_number',
	    'bank_account_number',
	    'bank_name',
	    'bank_branch',
	    'bank_code',
	];

}
