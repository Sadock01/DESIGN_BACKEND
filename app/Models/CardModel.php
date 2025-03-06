<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CardModel extends Model
{
    use HasFactory;

    protected $table = 'cards';

    protected $fillable = [
        'date_validity',
        'date_expiration',
        'card_number',
        'cvv',
        'card_activated',
        'client_account_id',
    ];

    protected $casts = [
        'date_validity' => 'date',
        'date_expiration' => 'date',
    ];

    public function clientAccount()
    {
        return $this->belongsTo(AccountModel::class);
    }
}
