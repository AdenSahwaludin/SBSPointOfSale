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
            'items.*.id_goods_in_detail' => 'required|integer|exists:goods_in_details,id_goods_in_detail',
            'items.*.qty_received' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal 1 item harus dicatat.',
            'items.min' => 'Minimal 1 item harus dicatat.',
            'items.*.id_goods_in_detail.required' => 'Item detail tidak ditemukan.',
            'items.*.id_goods_in_detail.exists' => 'Item detail tidak valid.',
            'items.*.qty_received.required' => 'Qty diterima harus diisi.',
            'items.*.qty_received.min' => 'Qty diterima minimal 1.',
        ];
    }
}
