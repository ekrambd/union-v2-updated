<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerreview extends Model
{
    use HasFactory;

    public function lawyer()
    {
    	return $this->belongsTo(Lawyer::class);
    }
}
