<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'kpr_price' => '500000000',
        'kpr_down_payment' => '20',
        'kpr_interest' => '7.5',
        'kpr_term' => '15',
        'contact_services' => "Pengembangan Properti\nDesain Arsitektur\nRancang Bangun\nRenovasi Rumah\nKonsultasi KPR",
    ];
}
