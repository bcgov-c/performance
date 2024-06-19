<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Seeder for Prod Environment
        $this->call(UserRoleSeeder::class);
        $this->call(UserRoleSeederAdmins::class);
        $this->call(GoalTypeSeeder::class);
        $this->call(GoalTypeSeeder_Update20220607::class);
        // $this->call(TopicSeeder::class);
        // $this->call(TagSeeder::class);
        $this->call(ExcusedReasonSeeder::class);
        $this->call(AccessLevelsSeeder::class);
        $this->call(RoleSeeder_Add_Longnames::class);
        $this->call(SharedElementSeeder::class);
        $this->call(DashboardMessageSeeder::class);
        $this->call(UserTableSeeder_GoLive::class);
        $this->call(GenericTemplateSeeder::class);
        $this->call(StoredDatesSeeder::class);
        $this->call(StoredDatesSeeder2::class);

        
    }
}
