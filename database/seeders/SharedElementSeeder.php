<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SharedElement;


class SharedElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $elements = [
            [
                'id' => 'C',
                'name' => 'Conversation',
                'description' => 'Shared Conversation'
            ],
            [
                'id' => 'G',
                'name' => 'Goal',
                'description' => 'Shared Goals'
            ],
            [
                'id' => 'B',
                'name' => 'Both',
                'description' => 'Shared Conversation & Goal'
            ]
        ];

        foreach ($elements as $element) {
            SharedElement::updateOrCreate([
                'id' => $element['id'],
            ], $element);
        }
    }
}
