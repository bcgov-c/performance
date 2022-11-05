<?php

namespace App\Http\Requests\MyTeams;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExcuseRequest extends FormRequest
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
            'excused_flag' => 'nullable',
            // 'excused_start_date' => 'nullable|date',
            // 'excused_end_date' => 'nullable|date|after_or_equal:excused_start_date',
            'excused_reason_id' => 'exists:excused_reasons,id',
            'user_id' => 'exists:users,id'
        ];
    }
}
