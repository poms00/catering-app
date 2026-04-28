<?php

namespace App\Http\Requests\Menu;

use App\Models\MenuCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
        if (is_array($this->input('entries'))) {
            return [
                'category_drafts' => ['nullable', 'array'],
                'category_drafts.*.temp_id' => ['required', 'integer', 'lt:0', 'distinct'],
                'category_drafts.*.name' => ['required', 'string', 'max:255'],
                'entries' => ['required', 'array', 'min:1'],
                'entries.*.type' => ['required', 'string', 'in:wrapper,single'],
                'entries.*.name' => ['nullable', 'string', 'max:255'],
                'entries.*.description' => ['nullable', 'string'],
                'entries.*.sort_order' => ['nullable', 'integer', 'min:0'],
                'entries.*.is_active' => ['nullable', 'boolean'],
                'entries.*.menu_category_id' => ['nullable', 'integer'],
                'entries.*.menu_category_ids' => ['nullable', 'array'],
                'entries.*.menu_category_ids.*' => ['integer'],
                'entries.*.variants' => ['required', 'array', 'min:1'],
                'entries.*.variants.*.menu_category_id' => ['nullable', 'integer'],
                'entries.*.variants.*.menu_category_ids' => ['nullable', 'array'],
                'entries.*.variants.*.menu_category_ids.*' => ['integer'],
                'entries.*.variants.*.name' => ['required', 'string', 'max:255'],
                'entries.*.variants.*.base_price' => ['required', 'numeric', 'min:0'],
                'entries.*.variants.*.description' => ['nullable', 'string'],
                'entries.*.variants.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
                'entries.*.variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
                'entries.*.variants.*.is_default' => ['boolean'],
                'entries.*.variants.*.is_active' => ['boolean'],
            ];
        }

        return [
            'category_drafts' => ['nullable', 'array'],
            'category_drafts.*.temp_id' => ['required', 'integer', 'lt:0', 'distinct'],
            'category_drafts.*.name' => ['required', 'string', 'max:255'],
            'creates_with_group' => ['required', 'boolean'],
            'menu_group_id' => ['nullable', 'integer', 'exists:menu_groups,id'],
            'menu_category_id' => ['nullable', 'integer'],
            'menu_category_ids' => ['nullable', 'array'],
            'menu_category_ids.*' => ['integer'],
            'name' => ['required_if:creates_with_group,true', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'variants' => ['required', 'array', 'min:1'],
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
            'menu_group_id' => 'grup menu',
            'menu_category_id' => 'kategori',
            'creates_with_group' => 'membuat dengan grup',
            'name' => 'nama grup',
            'description' => 'deskripsi',
            'sort_order' => 'urutan',
            'is_active' => 'status',
            'image' => 'foto grup',
            'category_drafts' => 'kategori baru',
            'category_drafts.*.temp_id' => 'id sementara kategori',
            'category_drafts.*.name' => 'nama kategori baru',
            'entries' => 'entri menu',
            'entries.*.type' => 'tipe entri',
            'entries.*.name' => 'nama wrapper',
            'entries.*.description' => 'deskripsi wrapper',
            'entries.*.sort_order' => 'urutan entri',
            'entries.*.is_active' => 'status entri',
            'entries.*.menu_category_id' => 'kategori wrapper',
            'entries.*.menu_category_ids' => 'kategori wrapper',
            'entries.*.menu_category_ids.*' => 'kategori wrapper',
            'entries.*.variants' => 'menu dalam entri',
            'entries.*.variants.*.menu_category_id' => 'kategori menu',
            'entries.*.variants.*.menu_category_ids' => 'kategori menu',
            'entries.*.variants.*.menu_category_ids.*' => 'kategori menu',
            'entries.*.variants.*.name' => 'nama menu',
            'entries.*.variants.*.base_price' => 'harga menu',
            'entries.*.variants.*.description' => 'deskripsi menu',
            'entries.*.variants.*.image' => 'foto menu',
            'entries.*.variants.*.sort_order' => 'urutan menu',
            'entries.*.variants.*.is_default' => 'menu default',
            'entries.*.variants.*.is_active' => 'status menu',
            'variants' => 'varian',
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
                $entries = $this->input('entries', null);

                if (is_array($entries)) {
                    $this->validateCategoryReferences($validator);

                    foreach ($entries as $index => $entry) {
                        if (! is_array($entry)) {
                            continue;
                        }

                        $type = $entry['type'] ?? null;
                        $variants = $entry['variants'] ?? [];

                        if ($type === 'wrapper' && blank($entry['name'] ?? null)) {
                            $validator->errors()->add(
                                "entries.{$index}.name",
                                'Nama wrapper wajib diisi.'
                            );
                        }

                        if ($type === 'single' && count($variants) !== 1) {
                            $validator->errors()->add(
                                "entries.{$index}.variants",
                                'Menu single harus memiliki tepat 1 menu.'
                            );
                        }
                    }

                    return;
                }

                $variants = $this->input('variants', []);

                if (! is_array($variants)) {
                    return;
                }

                $this->validateCategoryReferences($validator);

                if (! $this->boolean('creates_with_group') && count($variants) > 1) {
                    $validator->errors()->add(
                        'variants',
                        'Tanpa grup hanya boleh memiliki 1 varian.'
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

        foreach ((array) $this->input('entries', []) as $entryIndex => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $this->pushCategoryReference(
                $references,
                "entries.{$entryIndex}.menu_category_id",
                $entry['menu_category_id'] ?? null,
            );
            $this->pushCategoryReferences(
                $references,
                "entries.{$entryIndex}.menu_category_ids",
                $entry['menu_category_ids'] ?? [],
            );

            foreach ((array) ($entry['variants'] ?? []) as $variantIndex => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $this->pushCategoryReference(
                    $references,
                    "entries.{$entryIndex}.variants.{$variantIndex}.menu_category_id",
                    $variant['menu_category_id'] ?? null,
                );
                $this->pushCategoryReferences(
                    $references,
                    "entries.{$entryIndex}.variants.{$variantIndex}.menu_category_ids",
                    $variant['menu_category_ids'] ?? [],
                );
            }
        }

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
