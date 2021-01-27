<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "Users";

    protected $fillable = array("userName", "email", "password", "avatarUrl");

    protected $hidden = array("password");

    
}