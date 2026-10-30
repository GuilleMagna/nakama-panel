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

    public function whatsappWebUrl(): ?string
    {
        $phone = trim((string) $this->phone);
        if ($phone === '' || ! preg_match('/^[+0-9\s().-]+$/', $phone)) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (! preg_match('/^[1-9][0-9]{7,14}$/', $digits)) {
            return null;
        }

        return 'https://web.whatsapp.com/send?phone='.$digits;
    }
}
