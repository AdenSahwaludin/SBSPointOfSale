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
            'items' => 'required|array|min:0',
            'items.*.id_detail_pemesanan_barang' => 'required|integer|exists:detail_pemesanan_barang,id_detail_pemesanan_barang',
            'items.*.jumlah_diterima' => 'required|integer|min:0',
            'items.*.jumlah_rusak' => 'nullable|integer|min:0',
            'items.*.catatan' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $jumlahDiterima = intval($item['jumlah_diterima'] ?? 0);
                $jumlahRusak = intval($item['jumlah_rusak'] ?? 0);

                // Validasi: jumlah rusak tidak boleh lebih besar dari jumlah diterima
                // if ($jumlahRusak > $jumlahDiterima) {
                //     $validator->errors()->add(
                //         "items.{$index}.jumlah_rusak",
                //         "Jumlah rusak tidak boleh lebih besar dari jumlah diterima. Qty Diterima: {$jumlahDiterima}, Qty Rusak: {$jumlahRusak}"
                //     );
                // }

                // Validasi: setidaknya harus ada barang yang tidak rusak (qty baik > 0)
                // Ini opsional - uncomment jika ingin minimal 1 barang baik
                // if ($jumlahDiterima - $jumlahRusak <= 0) {
                //     $validator->errors()->add(
                //         "items.{$index}.jumlah_diterima",
                //         "Minimum harus ada 1 barang yang tidak rusak."
                //     );
                // }
            }
        });
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
        ];
    }
}
