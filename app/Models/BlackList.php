<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    protected $table = 'black_lists';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function blockedPerson()
    {
        return $this->belongsTo(User::class,'from_uid');
    }
}