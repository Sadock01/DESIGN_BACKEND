<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'address',
        'postal_code',
        'job',
        'salary',
        'country',
        'country_doc_select',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function files()
    {
        return $this->hasMany(UserFileModel::class, 'client_id');
    }

    public function account()
    {
        return $this->hasOne(AccountModel::class, 'client_id');
    }
    public function beneficiaires()
{
    return $this->hasMany(BeneficiaryModel::class);
}
}
