<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'number',
    ];

    protected $casts = [
        'name' => 'string',
        'number' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
