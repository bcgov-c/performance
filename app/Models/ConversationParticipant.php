<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationParticipant extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'participant_id',
        'role',
    ];

    public $timestamps = false;

    protected $with = ['participant'];

    public function participant()
    {
        return $this->belongsTo('App\Models\Participant');
    }

    public function transformAudit(array $data): array
    {

        if(session()->has('user_is_switched')) {
            $original_auth_id = session()->get('existing_user_id');
        } else {
            $original_auth_id = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        }

        $data['auditable_id'] = $this->conversation_id;
        $data['original_auth_id'] =  $original_auth_id;

        return $data;
    }

}
