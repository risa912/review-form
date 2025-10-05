<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('ja_JP');
        return [
            'category_id' => $this->faker->numberBetween(1, 5),
            'first_name' => $this->faker->lastname(),
            'last_name' => $this->faker->firstname(),
            'gender' => $this->faker->randomElement([1, 2, 3]),
            'email' => $this->faker->safeEmail(),
            'tel' => $this->faker->phoneNumber(),
            'address' => $this->faker->city() . $this->faker->streetAddress(),
            'building' => $this->faker->secondaryAddress(),
            'detail' => $this->faker->realText(120),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
