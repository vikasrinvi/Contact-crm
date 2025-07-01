<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomFieldDefinition;

class CustomFieldDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            ['field_name' => 'Birthday', 'field_type' => 'date', 'is_required' => false],
            ['field_name' => 'Company Name', 'field_type' => 'text', 'is_required' => false],
            ['field_name' => 'Address', 'field_type' => 'textarea', 'is_required' => false],
            ['field_name' => 'Lead Source', 'field_type' => 'text', 'is_required' => false], 
            ['field_name' => 'Annual Revenue', 'field_type' => 'number', 'is_required' => false],
            ['field_name' => 'Subscribed to Newsletter', 'field_type' => 'checkbox', 'is_required' => false],
        ];

        foreach ($fields as $field) {
            CustomFieldDefinition::create($field);
        }

        $this->command->info('Custom field definitions seeded!');
    }
}