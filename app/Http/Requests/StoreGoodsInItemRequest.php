<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsInItemRequest extends FormRequest
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
            'id_produk' => 'required|integer|exists:produk,id_produk',
            'qty_request' => 'required|integer|min:1|max:9999',
        ];
    }

    /**
     * Get custom error messages for validator rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_produk.required' => 'Produk harus dipilih.',
            'id_produk.exists' => 'Produk yang dipilih tidak ditemukan.',
            'qty_request.required' => 'Kuantitas harus diisi.',
            'qty_request.integer' => 'Kuantitas harus berupa angka.',
            'qty_request.min' => 'Kuantitas minimal adalah 1.',
            'qty_request.max' => 'Kuantitas maksimal adalah 9999.',
        ];
    }
}
