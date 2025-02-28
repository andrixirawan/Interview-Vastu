<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bus_id' => ['required', 'exists:buses,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'bus_id.required' => 'Bus harus dipilih',
            'bus_id.exists' => 'Bus tidak valid',
            'route_id.required' => 'Rute harus dipilih',
            'route_id.exists' => 'Rute tidak valid',
            'departure_date.required' => 'Tanggal keberangkatan harus diisi',
            'departure_date.date' => 'Format tanggal tidak valid',
            'departure_date.after_or_equal' => 'Tanggal keberangkatan tidak boleh di masa lalu',
        ];
    }
} 