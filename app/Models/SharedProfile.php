<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedProfile extends Model
{
    protected $table = 'shared_profiles';

    protected $fillable = [
        'shared_id', 'shared_item', 'comment', 'shared_by', 'shared_with'
    ];

    protected $casts = [
        'shared_item' => 'json'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $appends = [
        'shared_element_name',
    ];

    public function sharedWith() {
        return $this->belongsTo(User::class, 'shared_with');
    }

    public function sharedWithUser() {
        return $this->sharedWith();
    }

    public function sharedUser() {
        return $this->belongsTo(User::class, 'shared_id');
    }

    public function getSharedElementNameAttribute() {

        $text = '';
        if (count($this->shared_item) == 2) {
            $text = 'Full Profile';
        } elseif (in_array(1, $this->shared_item)) {
            $text = 'Goal';
        } else {
            $text = 'Conversations';
        }

        return $text;
    }
}
