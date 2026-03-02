<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['profile_name'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
