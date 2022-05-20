<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackUnit extends Model
{
    use SoftDeletes;
    protected $guraded = ['id'];
    protected $fillable = [
        'medicine_id',
        'jan_code',
        'hot_code', 
        'display_pack_unit',
        'pack_count', 
        'pack_unit', 
        'total_pack_count', 
        'total_pack_unit', 
        'deleter', 
        'creater', 
        'updater'
        ];
}
