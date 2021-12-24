<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingFiles extends Model
{
    use SoftDeletes;
    protected $table = 'marketing_files';
    protected $fillable = [
        'name',
        'type',
        'filename',
        'path',
        'mimetype',
        'extension',
    ];
}
