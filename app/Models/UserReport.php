<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plant_type_id',
        'ai_diagnosis',
    ];

    protected $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }
}
