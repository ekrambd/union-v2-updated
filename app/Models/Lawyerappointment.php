<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerappointment extends Model
{
    use HasFactory;

    public function userinfo()
    {
    	return $this->hasOne(Userinfo::class);
    }

    public function lawyerconsultation()
    {
        return $this->hasOne(Lawyerconsultation::class);
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }
}
