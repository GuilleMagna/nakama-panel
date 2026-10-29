<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'notes'];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
