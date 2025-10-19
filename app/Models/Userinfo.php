<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userinfo extends Model
{
    use HasFactory;

    public function lawyerappointment()
    {
    	return $this->belongsTo(Lawyerappointment::class);
    }

    public function getPreviousDocumentsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
}
