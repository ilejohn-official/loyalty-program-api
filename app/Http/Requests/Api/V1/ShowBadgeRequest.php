<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BadgeType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class ShowBadgeRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true; // Add authorization logic if needed
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'badge_type' => ['required', 'string', new Enum(BadgeType::class)],
    ];
  }
}
