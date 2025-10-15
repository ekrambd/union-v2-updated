<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Lawyer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function lawyeravailability()
    {
    	return $this->hasOne(Lawyeravailability::class);
    }

    public function lawyerdoc()
    {
    	return $this->hasOne(Lawyerdoc::class);
    }

    public function lawyerfee()
    {
    	return $this->hasOne(Lawyerfee::class);
    }

    public function getLawyerdegreesAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function lawyerreviews()
    {
        return $this->hasMany(Lawyerreview::class);
    }
}
