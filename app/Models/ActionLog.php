<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $fillable = [
        'action_item_id',
        'user_id',
        'delta',
        'note',
    ];

    public function actionItem()
    {
        return $this->belongsTo(ActionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
