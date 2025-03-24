<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenAIConfig extends Model
{
    use HasFactory;

    protected $table = 'openai_config';

    protected $fillable = ['api_key', 'model', 'temperature'];
}
