<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotDB extends Model
{
   protected $table = 'telegram.users';
   protected $fillable = ['name'];
}
