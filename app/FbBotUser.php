<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FbBotUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fb_user_id', 'first_name', 'last_name', 'profile_pic', 'locale', 'timezone', 'gender',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at',
    ];
}
