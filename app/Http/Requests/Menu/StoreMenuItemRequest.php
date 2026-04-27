<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_group_id' => ['nullable', 'integer', 'exists:menu_groups,id'],
            'name'          => ['required', 'string', 'max:255'],
            'base_price'    => ['required', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string'],
            'is_default'    => ['boolean'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menu_group_id' => 'grup menu',
            'name'          => 'nama varian',
            'base_price'    => 'harga dasar',
            'description'   => 'deskripsi',
            'is_default'    => 'default',
            'sort_order'    => 'urutan',
            'is_active'     => 'status',
            'image'         => 'foto varian',
        ];
    }
}
