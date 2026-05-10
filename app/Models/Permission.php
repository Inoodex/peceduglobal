<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * The users that are assigned this permission.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
