<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'email',
        'organization_id',
        'name',
    ];
}
