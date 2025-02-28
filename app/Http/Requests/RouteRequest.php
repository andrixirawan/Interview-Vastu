<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_city_id' => [
                'required',
                'exists:cities,id',
                Rule::notIn([$this->to_city_id])
            ],
            'to_city_id' => [
                'required',
                'exists:cities,id',
                Rule::notIn([$this->from_city_id])
            ],
            'price' => ['required', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'from_city_id.not_in' => 'Kota asal tidak boleh sama dengan kota tujuan',
            'to_city_id.not_in' => 'Kota tujuan tidak boleh sama dengan kota asal',
            'price.required' => 'Harga tiket harus diisi',
            'price.numeric' => 'Harga tiket harus berupa angka',
            'price.min' => 'Harga tiket tidak boleh negatif'
        ];
    }
} 