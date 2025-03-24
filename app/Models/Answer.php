<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = ['answer_text'];

    // public function questions() {
    //     return $this->belongsToMany(Question::class, 'question_answer');
    // }
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_answer', 'answer_id', 'question_id');
    }
}
