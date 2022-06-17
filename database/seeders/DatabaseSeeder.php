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
        $this->call(TopicSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(ExcusedReasonSeeder::class);
        $this->call(AccessLevelsSeeder::class);
        $this->call(RoleSeeder_Add_Longnames::class);
        $this->call(SharedElementSeeder::class);


        // Seeder for Test Environment
        // Items below need to be removed before Go-Live
        $this->call(UserTableSeeder::class);
        $this->call(UserTableSeederAdmins::class);
        $this->call(SupervisorGoalSeeder::class);
        $this->call(GoalBankSeeder::class);
        $this->call(AdditionalUsersTableSeeder::class);
        $this->call(ConversationSeeder::class);
        $this->call(GenericTemplateSeeder::class);
        
    }
}
