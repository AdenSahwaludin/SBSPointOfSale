<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStockAdjustmentRequest extends FormRequest
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
            'id_produk' => 'required|exists:produk,id_produk',
            'tipe' => 'required|in:retur_pelanggan,retur_gudang,koreksi_plus,koreksi_minus,penyesuaian_opname,expired,rusak',
            'qty_adjustment' => 'required|integer|min:1',
            'alasan' => 'nullable|string|max:500',
        ];
    }
}
