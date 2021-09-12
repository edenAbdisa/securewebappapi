<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 
        'status' 
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
