<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordGoodsReceivedRequest extends FormRequest
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
            'items' => 'required|array|min:1',
            'items.*.id_detail_pemesanan_barang' => 'required|integer|exists:detail_pemesanan_barang,id_detail_pemesanan_barang',
            'items.*.jumlah_diterima' => 'required|integer|min:1',
            'items.*.jumlah_rusak' => 'nullable|integer|min:0|lt:items.*.jumlah_diterima',
            'items.*.catatan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal 1 item harus dicatat.',
            'items.min' => 'Minimal 1 item harus dicatat.',
            'items.*.id_detail_pemesanan_barang.required' => 'Item detail tidak ditemukan.',
            'items.*.id_detail_pemesanan_barang.exists' => 'Item detail tidak valid.',
            'items.*.jumlah_diterima.required' => 'Jumlah diterima harus diisi.',
            'items.*.jumlah_diterima.min' => 'Jumlah diterima minimal 1.',
            'items.*.jumlah_rusak.min' => 'Jumlah rusak tidak boleh negatif.',
            'items.*.jumlah_rusak.lt' => 'Jumlah rusak harus lebih kecil dari jumlah diterima.',
        ];
    }
}
