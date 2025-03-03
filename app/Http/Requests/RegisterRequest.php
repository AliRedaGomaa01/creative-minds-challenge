<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'name' => 'required|string',
      'phone' => 'required|string',
      'device_token' => 'required|string',
      'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      'latitude' => 'required|string',
      'longitude' => 'required|string',
      'password' => [
        'required',
        'string',
        Password::min(8)
          ->mixedCase()  // Requires at least one uppercase and one lowercase letter
          ->letters()
          ->numbers()
          ->symbols()
      ],
    ];
  }
}
