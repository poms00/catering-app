<?php

namespace Database\Factories;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'menu_group_id' => MenuGroup::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numerify('##??')),
            'base_price' => fake()->numberBetween(15000, 150000),
            'description' => fake()->paragraph(),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
