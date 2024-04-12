<?php

namespace App\Http\Requests\Conversation;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class ConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $date = \Carbon\Carbon::now()->addDays(122);

        $latestPastConversation = Conversation::latestPastConversation();
        if ($latestPastConversation) {
            $date = $latestPastConversation->date_time->addDays(122);
        }
        return [
            'conversation_topic_id' => 'required|exists:conversation_topics,id',
            'participant_id' => 'required',
            'date' => 'required|sometimes|date|after_or_equal:today|before:'. $date,
            'time' => 'required|sometimes',
            'owner_id' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'date.*' => 'Conversations must be scheduled every four months, at minimum.'
        ];
    }
}
