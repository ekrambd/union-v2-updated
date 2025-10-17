<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerconsultation extends Model
{
    use HasFactory;

    public function lawyerappointment()
    {
        return $this->belongsTo(Lawyerappointment::class);
    }
}
