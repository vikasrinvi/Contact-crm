<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['Male', 'Female', 'Other']);

        return [
            'name' => $this->faker->name($gender),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $gender,
            // For profile_image and additional_file, we'll store placeholder paths.
            // In a real application, you'd integrate with dummy file uploads or copy actual dummy files.
            'profile_image' => $this->faker->boolean(50) ? 'public/contacts/profile_images/placeholder_' . $this->faker->randomNumber(2) . '.jpg' : null,
            'additional_file' => $this->faker->boolean(30) ? 'public/contacts/additional_files/document_' . $this->faker->randomNumber(2) . '.pdf' : null,
            'merge_status' => 'active', 
            'merged_into_contact_id' => null,
        ];
    }
}