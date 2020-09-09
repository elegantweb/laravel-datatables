<?php

namespace Elegant\DataTables\Tests\Database\Factories;

use Elegant\DataTables\Tests\Fixtures\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label' => $this->faker->word,
            'slug' => Str::slug($this->faker->unique()->word),
        ];
    }
}
