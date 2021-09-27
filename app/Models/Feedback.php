<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{ 
    use HasFactory;
    protected $table = 'feedbacks';
    protected $fillable = [
        'file',
        'comments',
        'status',
        'user_id',
        'address_id',
        'feedback_types_id',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    public function feedback_types()
    {
        return $this->belongsTo(FeedbackType::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
