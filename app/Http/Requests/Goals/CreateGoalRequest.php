<?php

namespace App\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class CreateGoalRequest extends FormRequest
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
            'created_goal_id' => 'nullable',
            'title' => 'required',
            'start_date' => 'nullable',
            'target_date' => 'nullable',
            'what' => 'nullable',
            'why' => 'nullable',
            'how' => 'nullable',
            'measure_of_success' => 'nullable',
            'goal_type_id' => 'required|exists:goal_types,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id'
        ];
    }
    
    public function messages() {
        return [
            //'what.required' => 'The description field is required'
        ];
    }
}
