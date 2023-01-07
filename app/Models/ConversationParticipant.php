<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

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
     
        $data['auditable_id'] = $this->conversation_id;

        return $data;
    }
}
