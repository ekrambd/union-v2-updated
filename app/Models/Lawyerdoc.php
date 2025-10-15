<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerdoc extends Model
{
    use HasFactory;

    public function lawyer()
    {
    	return $this->belongsTo(Lawyer::class);
    }

    public function getDocumentsAttribute($value)
    {
        return json_decode($value,true);
    }
}
