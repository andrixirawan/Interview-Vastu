<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama bus harus diisi',
            'capacity.required' => 'Kapasitas bus harus diisi',
            'capacity.integer' => 'Kapasitas bus harus berupa angka bulat',
            'capacity.min' => 'Kapasitas bus minimal 1'
        ];
    }
} 