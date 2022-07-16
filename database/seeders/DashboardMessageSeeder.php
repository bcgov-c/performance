<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\DashboardMessage;

class DashboardMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'id' => 1,
                'title' => 'Version 1.0 - July 18',
                'message' => '
                    <p><b>Version 1.0 - July 18</b></p>
                    <p>Welcome to the Performance Development Platform (PDP)! Please check out the resources section and click on the info icons throughout the platform to learn more about all the new functions.</p>
                    <p>Everything is in place for you to use the PDP to:
                    <ul>
                        <li>Set work, learning, or career development goals and track progress against agreed upon measures of success</li>
                        <li>Have the performance conversations you need to have when you need to have them</li>
                    </ul>
                    </p>
                    <p>We will continue to make improvements to the platform in the coming weeks and months. Not everything will be perfect on day one. Please see below for a few things we are currently working on.</li></p>
                    <p>Updates planned for V1.01
                    <ul>
                        <li>Bringing all pages up to accessibility standards WCAG 2.1 AA</li>
                        <li>Enabling and updating the “Excused” employee function</li>
                        <li>Enabling the PDP email notification system; notifications are currently only displayed on your homepage</li>
                        <li>Improving various designs and page layouts</li>
                    </ul>
                    </p>
                    <p>Thank you for your participation and patience as we work together to make the PDP a success!</p>
                ',
                'status' => 1,
            ],
        ];

        foreach ($list as $l) {
            \App\Models\DashboardMessage::updateOrCreate(['id' => $l['id'],], $l);
        }
    }
}
