<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFileModel extends Model
{ 
    use HasFactory;

    protected $table = 'user_file';

    protected $fillable = [
        'file_name',
        'file_path',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(ClientModel::class);
    }
}
