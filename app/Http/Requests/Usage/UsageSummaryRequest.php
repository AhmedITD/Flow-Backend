<?php

namespace App\Http\Requests\Usage;

use Illuminate\Foundation\Http\FormRequest;

class UsageSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'string', 'in:day,week,month,year'],
        ];
    }
}
