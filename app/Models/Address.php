<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country',
        'city',
        'latitude',
        'longitude',
        'type'
    ];
    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    public function user()
    {
        return $this->hasOne(User::class,'address_id');
    } 
    public function feedback()
    {
        return $this->hasOne(Feedback::class,'address_id');
    } 
}
