<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordHelpGuide extends Model
{
    use HasFactory;

    protected $connection = 'landlord';
    protected $table = 'landlord_help_guides';

    protected $fillable = [
        'pertanyaan',
        'jawaban',
        'urutan',
    ];
}
