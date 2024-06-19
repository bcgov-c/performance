<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id',
            'goal_comment_flag', 'goal_bank_flag', 'share_profile_flag',
            'conversation_setup_flag', 'conversation_signoff_flag', 'conversation_disagree_flag',
            'conversation_due_month', 'conversation_due_week', 'conversation_due_past',
            'team_conversation_due_month', 'team_conversation_due_week', 'team_conversation_due_past',
            'created_by_id', 'updated_by_id',
        ];

    protected $attributes = [

        'goal_comment_flag' => 'Y', 
        'goal_bank_flag' => 'Y', 
        'share_profile_flag' => 'Y',
        'conversation_setup_flag' => 'Y', 
        'conversation_signoff_flag' => 'Y', 
        'conversation_disagree_flag' => 'Y', 
        
        'conversation_due_month' => 'Y', 
        'conversation_due_week' => 'Y', 
        'conversation_due_past' => 'Y',

        'team_conversation_due_month' => 'N', 
        'team_conversation_due_week' => 'N', 
        'team_conversation_due_past' => 'Y',
    ];

        
    public function user() {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function created_by()
    {
        return $this->hasOne(User::Class, 'id', 'created_by_id')->withDefault();
    }

    public function updated_by()
    {
        return $this->hasOne(User::Class, 'id', 'updated_by_id')->withDefault();
    }

}
