<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteEvent extends Model
{
    public const CATEGORIES = ['security' => 'Seguridad / hackeo', 'hosting' => 'Cambio de hosting', 'redesign' => 'Rediseño', 'domain' => 'Cambio de dominio', 'maintenance' => 'Mantenimiento', 'other' => 'Otro'];

    protected $fillable = ['site_id', 'user_id', 'category', 'title', 'occurred_on', 'approximate', 'description'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date', 'approximate' => 'boolean'];
    }
}
