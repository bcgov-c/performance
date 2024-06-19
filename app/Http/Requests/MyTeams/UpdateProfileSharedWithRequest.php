<?php

namespace App\Http\Requests\MyTeams;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileSharedWithRequest extends FormRequest
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
            'comment' => 'required',
            // 'shared_item' => 'required|array',
            // 'shared_item.*' => 'required|in:1,2',
            'action' => 'required|in:items,comment,stop'
        ];
    }
}
