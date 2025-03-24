<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['plant_type_id', 'question_text', 'order'];

    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }

    // public function answers() {
    //     return $this->belongsToMany(Answer::class, 'question_answer');
    // }
    public function answers()
    {
        return $this->belongsToMany(Answer::class, 'question_answer', 'question_id', 'answer_id');
    }
}
