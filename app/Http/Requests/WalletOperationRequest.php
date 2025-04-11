<?php

// app/Http/Requests/WalletOperationRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gt:0',
            'description' => 'nullable|string|max:255',
        ];
    }
}
