<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentationRequests extends Model
{
    protected $table = "documentation_requests";

    protected $fillable = [
        "user_id",
        'user_name',
        "document_name",
        "document_file",
        "document_status"
    ];
}
