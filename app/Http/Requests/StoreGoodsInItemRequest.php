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
            'jumlah_dipesan' => 'required|integer|min:1|max:9999',
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
            'jumlah_dipesan.required' => 'Kuantitas harus diisi.',
            'jumlah_dipesan.integer' => 'Kuantitas harus berupa angka.',
            'jumlah_dipesan.min' => 'Kuantitas minimal adalah 1.',
            'jumlah_dipesan.max' => 'Kuantitas maksimal adalah 9999.',
        ];
    }
}
