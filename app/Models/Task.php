<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['team_leader_id','fromable_id','fromable_type', 'task', 'assigned_type', 'assigned_id','stauts','is_approval'];



    public function subServices(){
        return $this->hasMany(SubServiece::class, 'service_id');
    }
    public function fromable()
    {
        return $this->morphTo();
    }


    public function assigned()
    {
        return $this->morphTo();
    }


    public function scopeWithSalesEmployee($query)
    {
        return $query->with(['fromable.salesEmployee' => function ($query) {
            $query->select('id', 'name'); 
        }]);
    }


    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
