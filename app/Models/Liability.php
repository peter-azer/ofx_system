<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liability extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = ['user_id', 'total_amount', 'type' ,'description'];

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
