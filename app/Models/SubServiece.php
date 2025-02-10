<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubServiece extends Model
{
    /** @use HasFactory<\Database\Factories\SubServieceFactory> */
    use HasFactory;

    protected $fillable = ['service_id', 'sub_service_name'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
