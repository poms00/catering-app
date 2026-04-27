<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuGroupRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'creates_with_group' => $this->boolean('creates_with_group'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'creates_with_group' => ['required', 'boolean'],
            'menu_category_id' => ['nullable', 'integer', 'exists:menu_categories,id'],
            'name'             => ['required_if:creates_with_group,true', 'nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['boolean'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'variants'         => ['required', 'array', 'min:1'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.base_price' => ['required', 'numeric', 'min:0'],
            'variants.*.description' => ['nullable', 'string'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['boolean'],
            'variants.*.is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menu_category_id' => 'kategori',
            'creates_with_group' => 'membuat dengan grup',
            'name'             => 'nama grup',
            'description'      => 'deskripsi',
            'sort_order'       => 'urutan',
            'is_active'        => 'status',
            'image'            => 'foto grup',
            'variants'         => 'varian',
            'variants.*.name' => 'nama varian',
            'variants.*.base_price' => 'harga dasar varian',
            'variants.*.description' => 'deskripsi varian',
            'variants.*.sort_order' => 'urutan varian',
            'variants.*.is_default' => 'varian default',
            'variants.*.is_active' => 'status varian',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $variants = $this->input('variants', []);

                if (! $this->boolean('creates_with_group') && count($variants) > 1) {
                    $validator->errors()->add(
                        'variants',
                        'Tanpa grup hanya boleh memiliki 1 varian.'
                    );
                }
            },
        ];
    }
}
