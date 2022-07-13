<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tag;

class UpdateTagsJuly1107 extends Migration
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
                    'id' => 7,
                    'name' => 'Diversity and Inclusion',
                    'description' => 'Related to supporting a diverse workforce and enhancing inclusion in the workplace to ensure the BC Public Service is reflective of our province and inclusive of Indigenous peoples, minority communities, immigrants, persons with disabilities and the LGBTQ2S+ community.'
                ],
                [
                    'id' => 16,
                    'name' => 'Reconciliation and Decolonization',
                    'description' => 'Related to establishing and maintaining a mutually respectful relationship between Indigenous and non-Indigenous peoples in this country and dismantling the process by which one nation asserts and establishes its domination and control over another nation’s land, people, and culture.'
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

