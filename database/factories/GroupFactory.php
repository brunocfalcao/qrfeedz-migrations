<?php

namespace QRFeedz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use QRFeedz\Cube\Models\Group;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
