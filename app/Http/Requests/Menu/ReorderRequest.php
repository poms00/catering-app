<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'urutan'   => ['required', 'array', 'min:1'],
            'urutan.*' => ['required', 'integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'urutan.required'   => 'Data urutan wajib dikirim.',
            'urutan.array'      => 'Format urutan tidak valid.',
            'urutan.*.integer'  => 'Setiap ID harus berupa angka.',
            'urutan.*.distinct' => 'ID tidak boleh duplikat.',
        ];
    }
}
