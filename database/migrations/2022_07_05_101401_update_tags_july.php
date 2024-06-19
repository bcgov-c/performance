<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tag;

class UpdateTagsJuly extends Migration
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
                    'id' => 1,
                    'name' => 'Accessibility',
                    'description' => 'Related to removing barriers caused by environments, attitudes, practices, policies, information, communications or technologies that prevent equal and meaningful participation in society.'
                ],
                [
                    'id' => 2,
                    'name' => 'Accounting',
                    'description' => 'Related to accounting principles and practices. This includes the recording, analyzing, and reporting of financial information.'
                ],
                [
                    'id' => 3,
                    'name' => 'Clerical',
                    'description' => 'Related to administrative and clerical procedures and systems such as word processing, managing files and records, stenography and transcription, designing forms, and other office procedures and terminology.'
                ],
                [
                    'id' => 4,
                    'name' => 'Client Service',
                    'description' => 'Related to principles and practices of providing service to clients to maintain and build organizational success.'
                ],
                [
                    'id' => 5,
                    'name' => 'Communication',
                    'description' => 'Related to understanding and responding effectively to different audiences through careful listening, verbal and written communications, problem framing, and use of presentation technologies.'
                ],
                [
                    'id' => 6,
                    'name' => 'Computer and Information Systems',
                    'description' => 'Related to computer programming, hardware, and software.'
                ],
                [
                    'id' => 7,
                    'name' => 'Diversity and Inclusion',
                    'description' => 'Related to supporting a diverse workforce and enhancing inclusion in the workplace to ensure the BC Public Service is reflective of our province and inclusive of Indigenous peoples, minority communities, immigrants, persons with disabilities and the LGBTQ2S+ community.'
                ],
                [
                    'id' => 8,
                    'name' => 'Economics',
                    'description' => 'Related to economic theories, principles, and methods of analysis including simulation and forecasting techniques.'
                ],
                [
                    'id' => 9,
                    'name' => 'Education and Training',
                    'description' => "Related to principles and methods for curriculum and training design, teaching, and instruction for individuals and groups, and the measurement of training effects."
                ],
                [
                    'id' => 10,
                    'name' => 'Finance',
                    'description' => 'Related to principles and practices of financial management, monitoring and accountability frameworks, reporting procedures, banking, and markets.'
                ],
                [
                    'id' => 11,
                    'name' => 'Human Resources',
                    'description' => 'Related to principles and procedures for personnel recruitment, selection, training, compensation and benefits, labor relations and negotiation, and personnel information systems.'
                ],
                [
                    'id' => 12,
                    'name' => 'Innovation',
                    'description' => 'Related to improving performance by creating, promoting, or integrating new concepts and ways of working within the organization.'
                ],
                [
                    'id' => 13,
                    'name' => 'Law',
                    'description' => 'Related to the legal system, laws, legal codes, court procedures, and precedents.'
                ],
                [
                    'id' => 14,
                    'name' => 'Marketing',
                    'description' => 'Related to principles and practices for determining consumers\' wants and needs, assessing and developing business opportunities, and advertising products and services.'
                ],
                [
                    'id' => 15,
                    'name' => 'Operational Planning',
                    'description' => 'Related to determining phases and steps, defining activities and tasks, and establishing schedules to complete objectives on time and within budget.'
                ],
                [
                    'id' => 16,
                    'name' => 'Reconciliation and Decolonization',
                    'description' => 'Related to establishing and maintaining a mutually respectful relationship between Indigenous and non-Indigenous peoples in this country and dismantling the process by which one nation asserts and establishes its domination and control over another nation’s land, people, and culture.'
                ],
                [
                    'id' => 17,
                    'name' => 'Strategic Planning',
                    'description' => 'Related to envisioning a future state and developing strategies, goals, objectives, and action plans to achieve it.'
                ],
                [
                    'id' => 18,
                    'name' => 'Talent Management',
                    'description' => 'Related to recruiting, retaining, and developing talent with the goal of meeting organizational needs.'
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

