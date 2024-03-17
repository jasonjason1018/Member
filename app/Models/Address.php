<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $table = 'address';
    protected $fillable = [
        'user_id',
        'address_type_id',
        'zipcode',
        'city',
        'country',
        'address',
        'state',
    ];
    public function documents()
    {
        return $this->morphMany('App\Models\Document', 'documentable');
    }
}
