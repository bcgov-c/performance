<?php

namespace App\Http\Requests\MyTeams;

use Illuminate\Foundation\Http\FormRequest;

class ShareProfileRequest extends FormRequest
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
        return [
            'share_with_users' => 'required|array',
            'share_with_users.*' => 'required|exists:users,id',
            'shared_id' => 'required|exists:users,id',
            'items_to_share' => 'required|array',
            'items_to_share.*' => 'required|in:1,2',
            'reason' => 'required',
            'accepted' => 'accepted'
        ];
    }

    public function attributes() {
        return [
            'accepted' => 'terms',
            'items_to_share' => 'elements to share'
        ];
    }
}
