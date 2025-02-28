<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'exists:trips,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'num_tickets' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_id.required' => 'Perjalanan harus dipilih',
            'trip_id.exists' => 'Perjalanan tidak valid',
            'customer_name.required' => 'Nama pelanggan harus diisi',
            'customer_email.required' => 'Email pelanggan harus diisi',
            'customer_email.email' => 'Format email tidak valid',
            'num_tickets.required' => 'Jumlah tiket harus diisi',
            'num_tickets.integer' => 'Jumlah tiket harus berupa angka bulat',
            'num_tickets.min' => 'Jumlah tiket minimal 1',
        ];
    }
} 