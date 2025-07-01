<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactCustomField;
use App\Models\CustomFieldDefinition;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();


        $customFieldDefinitions = CustomFieldDefinition::all();

        Contact::factory(50)->create()->each(function ($contact) use ($customFieldDefinitions, $faker) {
            
            if ($customFieldDefinitions->isNotEmpty()) {
               
                $assignedFields = $customFieldDefinitions->random(rand(0, min(3, $customFieldDefinitions->count())));

                foreach ($assignedFields as $definition) {
                    $value = null;
                    switch ($definition->field_type) {
                        case 'text':
                            $value = $faker->sentence(rand(3, 7)); 
                            break;
                        case 'number':
                            $value = $faker->randomNumber(4);
                            break;
                        case 'date':
                            $value = $faker->date();
                            break;
                        case 'textarea':
                            $value = $faker->paragraph(rand(1, 3)); 
                            break;
                        case 'checkbox':
                            $value = $faker->boolean() ? '1' : '0';
                            break;
                        
                    }

                    if ($value !== null) {
                        ContactCustomField::create([
                            'contact_id' => $contact->id,
                            'custom_field_definition_id' => $definition->id,
                            'value' => $value,
                        ]);
                    }
                }
            }
        });

        $this->command->info('Contacts seeded successfully!');
    }
}