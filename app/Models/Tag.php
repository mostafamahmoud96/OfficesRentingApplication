<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $guarded =[];
    public $timestamps = false ;

    public function offices()
    {
        return $this->belongsToMany(Office::class,'offices_tags');
    }
}
