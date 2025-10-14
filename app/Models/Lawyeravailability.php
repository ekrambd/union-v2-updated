<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyeravailability extends Model
{
    use HasFactory;

    public function lawyer()
    {
    	return $this->belongsTo(Lawyer::class);
    }

    public function getMorningShiftDaysAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    
    public function getAfternoonShiftDaysAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    
    public function getEveningShiftDaysAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
}
