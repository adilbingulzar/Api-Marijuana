<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\SupportForm;

class SupportFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // No authorization required for support forms
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Form type validation - must be either 'member' or 'app'
            'type' => [
                'required',
                'string',
                'in:' . implode(',', SupportForm::getValidTypes())
            ],
            
            // Name validation - required string with min/max length
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\'-\.]+$/' // Only letters, spaces, apostrophes, hyphens, dots
            ],
            
            // Email validation - required valid email format
            'email' => [
                'required',
                'email:filter',
                'max:255'
            ],
            
            // Message validation - required text with min/max length
            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ]
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Type field messages
            'type.required' => 'Support form type is required.',
            'type.in' => 'Support form type must be either "member" or "app".',
            
            // Name field messages
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'name.regex' => 'Name can only contain letters, spaces, apostrophes, hyphens, and dots.',
            
            // Email field messages
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            
            // Message field messages
            'message.required' => 'Message is required.',
            'message.min' => 'Message must be at least 10 characters long.',
            'message.max' => 'Message cannot exceed 2000 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'type' => 'support type',
            'name' => 'name',
            'email' => 'email address',
            'message' => $this->getMessageFieldName(),
        ];
    }

    /**
     * Get the appropriate field name for the message based on type
     *
     * @return string
     */
    private function getMessageFieldName(): string
    {
        return match ($this->input('type')) {
            SupportForm::TYPE_MEMBER => 'support message',
            SupportForm::TYPE_APP => 'issue description',
            default => 'message',
        };
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Return consistent JSON error response for API
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Trim whitespace from string fields
        $this->merge([
            'type' => $this->input('type') ? trim(strtolower($this->input('type'))) : null,
            'name' => $this->input('name') ? trim($this->input('name')) : null,
            'email' => $this->input('email') ? trim(strtolower($this->input('email'))) : null,
            'message' => $this->input('message') ? trim($this->input('message')) : null,
        ]);
    }
}
