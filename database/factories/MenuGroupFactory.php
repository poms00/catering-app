<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use App\Models\MenuGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MenuGroup>
 */
class MenuGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'menu_category_id' => MenuCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numerify('##??')),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
