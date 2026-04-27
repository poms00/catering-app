<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:menu_categories,slug'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'nama kategori',
            'slug'        => 'slug',
            'description' => 'deskripsi',
            'sort_order'  => 'urutan',
            'is_active'   => 'status',
        ];
    }
}