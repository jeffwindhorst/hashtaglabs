<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class validation_results extends Model
{
    protected $table = 'validation_results';
    
    protected $fillable = ['url', 'content', 'status'];

    
    
}
