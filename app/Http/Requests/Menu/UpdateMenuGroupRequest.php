<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_category_id' => ['nullable', 'integer', 'exists:menu_categories,id'],
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['sometimes', 'boolean'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menu_category_id' => 'kategori',
            'name'             => 'nama grup',
            'description'      => 'deskripsi',
            'sort_order'       => 'urutan',
            'is_active'        => 'status',
            'image'            => 'foto grup',
        ];
    }
}
