<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceBetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bets' => ['required', 'array', 'min:1', 'max:60'],
            'bets.*.type' => ['required', 'string', 'max:24'],
            'bets.*.selection' => ['required', 'string', 'max:40'],
            'bets.*.amount' => ['required', 'integer', 'min:1', 'max:50000'],
        ];
    }
}
