<?php

namespace App\Http\Requests\Menu;

use App\Models\MenuCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMenuGroupRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active')
                ? $this->boolean('is_active')
                : null,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isStandaloneItem = $this->query('type') === 'item';

        return [
            'category_drafts' => ['nullable', 'array'],
            'category_drafts.*.temp_id' => ['required', 'integer', 'lt:0', 'distinct'],
            'category_drafts.*.name' => ['required', 'string', 'max:255'],
            'menu_category_id' => ['nullable', 'integer'],
            'menu_category_ids' => ['nullable', 'array'],
            'menu_category_ids.*' => ['integer'],
            'name' => [$isStandaloneItem ? 'nullable' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.menu_group_id' => ['nullable', 'integer', 'exists:menu_groups,id'],
            'variants.*.menu_category_id' => ['nullable', 'integer'],
            'variants.*.menu_category_ids' => ['nullable', 'array'],
            'variants.*.menu_category_ids.*' => ['integer'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.base_price' => ['required', 'numeric', 'min:0'],
            'variants.*.description' => ['nullable', 'string'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['boolean'],
            'variants.*.is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menu_category_id' => 'kategori',
            'menu_category_ids' => 'kategori',
            'menu_category_ids.*' => 'kategori',
            'name' => 'nama grup',
            'description' => 'deskripsi',
            'sort_order' => 'urutan',
            'is_active' => 'status',
            'image' => 'foto grup',
            'category_drafts' => 'kategori baru',
            'category_drafts.*.temp_id' => 'id sementara kategori',
            'category_drafts.*.name' => 'nama kategori baru',
            'variants' => 'varian',
            'variants.*.id' => 'id varian',
            'variants.*.menu_group_id' => 'grup varian',
            'variants.*.menu_category_id' => 'kategori varian',
            'variants.*.menu_category_ids' => 'kategori varian',
            'variants.*.menu_category_ids.*' => 'kategori varian',
            'variants.*.name' => 'nama varian',
            'variants.*.base_price' => 'harga dasar varian',
            'variants.*.description' => 'deskripsi varian',
            'variants.*.image' => 'foto varian',
            'variants.*.sort_order' => 'urutan varian',
            'variants.*.is_default' => 'varian default',
            'variants.*.is_active' => 'status varian',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $variants = $this->input('variants', []);

                if (! is_array($variants)) {
                    return;
                }

                $this->validateCategoryReferences($validator);

                if ($this->query('type') === 'item' && count($variants) !== 1) {
                    $validator->errors()->add(
                        'variants',
                        'Menu tanpa grup harus memiliki tepat 1 varian.'
                    );
                }
            },
        ];
    }

    private function validateCategoryReferences(Validator $validator): void
    {
        $references = $this->categoryReferences();
        $draftIds = collect($this->input('category_drafts', []))
            ->pluck('temp_id')
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
        $positiveIds = collect($references)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
        $existingIds = $positiveIds->isEmpty()
            ? collect()
            : MenuCategory::query()
                ->whereKey($positiveIds->all())
                ->pluck('id')
                ->map(fn (int|string $id): int => (int) $id);

        foreach ($references as $attribute => $id) {
            if ($id < 0 && ! in_array($id, $draftIds, true)) {
                $validator->errors()->add($attribute, 'Kategori baru tidak valid.');
            }

            if ($id > 0 && ! $existingIds->contains($id)) {
                $validator->errors()->add($attribute, 'Kategori yang dipilih tidak valid.');
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function categoryReferences(): array
    {
        $references = [];

        $this->pushCategoryReference($references, 'menu_category_id', $this->input('menu_category_id'));
        $this->pushCategoryReferences($references, 'menu_category_ids', $this->input('menu_category_ids', []));

        foreach ((array) $this->input('variants', []) as $variantIndex => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $this->pushCategoryReference(
                $references,
                "variants.{$variantIndex}.menu_category_id",
                $variant['menu_category_id'] ?? null,
            );
            $this->pushCategoryReferences(
                $references,
                "variants.{$variantIndex}.menu_category_ids",
                $variant['menu_category_ids'] ?? [],
            );
        }

        return $references;
    }

    /**
     * @param  array<string, int>  $references
     */
    private function pushCategoryReference(array &$references, string $attribute, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (is_numeric($value)) {
            $references[$attribute] = (int) $value;
        }
    }

    /**
     * @param  array<string, int>  $references
     */
    private function pushCategoryReferences(array &$references, string $attribute, mixed $values): void
    {
        if (! is_array($values)) {
            return;
        }

        foreach ($values as $index => $value) {
            $this->pushCategoryReference($references, "{$attribute}.{$index}", $value);
        }
    }
}
