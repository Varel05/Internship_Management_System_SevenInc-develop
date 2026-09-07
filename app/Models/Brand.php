<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'company_address',
        'logo',
        'internship_certificate_bg',
        'webinar_certificate_bg',
        'signatory_name',
        'signatory_email',
        'signatory_position',
        'signature',
    ];
}
