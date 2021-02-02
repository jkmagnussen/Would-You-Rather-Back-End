<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Authenticatable implements JWTSubject {
    use HasFactory;
    use Notifiable;
    public $timestamps = false;

    protected $table = "Users";

    protected $fillable = array("userName", "email", "password", "avatarUrl");

    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [];
    }
}