<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tag;

class UpdateTags20220928 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        {
            $tags = [
                
                [
                    'id' => 19,
                    'name' => 'Business Acumen',
                    'description' => 'Related to understanding business issues, processes and outcomes as they impact the individual’s and the organization’s business needs and providing quality insight as to how to achieve goals and ensure business success.'
                ],
                [
                    'id' => 20,
                    'name' => 'Change Management',
                    'description' => 'Related to preparing yourself and others to understand and adopt change at all levels.'
                ],
                [
                    'id' => 21,
                    'name' => 'Digital Dexterity',
                    'description' => 'Related to applying the culture, practices, processes, and emerging modern technologies of the internet era to support the work of the public service.'
                ],
                [
                    'id' => 22,
                    'name' => 'Leadership',
                    'description' => 'Related to motivating, influencing, or guiding other individuals, teams, or entire organizations.'
                ],
            ];

            foreach($tags as $tag) {
                Tag::updateOrCreate([
                    'id' => $tag['id'],
                ], $tag);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
