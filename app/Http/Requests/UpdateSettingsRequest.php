<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Already handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number_id'    => 'required|string',
            'access_token'       => 'nullable|string', // Nullable so we don't overwrite if empty
            'verify_token'       => 'required|string',
            'api_version'        => 'required|string',
            'ngrok_public_url'   => 'nullable|url',
            'bank_transfer_info' => 'nullable|string',
            'group_id_seller'    => 'nullable|string',
            'qris_file'          => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ];
    }
}
