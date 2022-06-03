<?php

namespace App\Console\Commands;

use App\Models\EmployeeDemo;
use Illuminate\Console\Command;
use App\Models\OrganizationTree;

class BuildOrgTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:buildOrgTree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Hierarchical Org Chart based on Employee demography data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info( now() );
        $this->info('Level 0 - Oragnization Level');

        $Organizations =  EmployeeDemo::whereNotIn('organization',['',' '])
            ->select('organization')->groupBy('organization')->pluck('organization');

        foreach ($Organizations as $organization_name ) {

            $node = OrganizationTree::where('name', $organization_name )
                ->where('level', 0)->first();

            // check no of employee 
            // $count = EmployeeDemo::where('organization',$organization_name)->count();

            if (!$node) {
                $node = new OrganizationTree([
                        'name' => $organization_name,
                        'status' => 1,
                        'level' => 0,
                        'organization' =>  $organization_name,
                        // 'no_of_employee' => $count,
                ]);
                $node->saveAsRoot(); // Saved as root
            } else {
                $node->status = 1;
                // $node->no_of_employee = $count;
                $node->save();
            }

        }


        // Level 1 - Programs 
        $this->info( now() );
        $this->info('Level 1 - Programs');

        $organizations = OrganizationTree::withDepth()->having('depth', '=', 0)->get();

        foreach ($organizations as $parent ) {

            $programs =  EmployeeDemo::where('organization', $parent->name )
                 ->whereNotIn('level1_program',['',' '])
                 ->select('level1_program')
                 ->groupBy('level1_program')
                 ->orderBy('level1_program')
                 ->pluck('level1_program');

            foreach ($programs as $program_name ) {
                $node = OrganizationTree::where('name', $program_name )
                    ->where('organization', $parent->name )
                    ->where('level', 1)->first();


                // check no of employee 
                // $count = EmployeeDemo::where('organization',$parent->name)
                //             ->where('level1_program', $program_name)
                //             ->count();

                if (!$node) {
                    $node = new OrganizationTree([
                        'name' => $program_name,
                        'status' => 1,
                        'level' => 1,
                        'organization' =>  $parent->organization,
                        'level1_program' =>  $program_name,
                        // 'no_of_employee' => $count,
                    ]);
                    $node->save(); // Saved as root
                    $parent->appendNode($node);
                } else {
                    $node->status = 1;
                    // $node->no_of_employee = $count;
                    $node->save();
                }


            }
        }


        // Level 2 - Division  
        $this->info( now() );
        $this->info('Level 2 -Division');

        $programs = OrganizationTree::withDepth()->having('depth', '=', 1)->get();

        foreach ($programs as $parent ) {

            $divisions =  EmployeeDemo::where('organization', $parent->organization)
                 ->where('level1_program', $parent->name)
                 ->whereNotIn('level2_division',['',' '])
                 ->select('level2_division')
                 ->groupBy('level2_division')
                 ->orderBy('level2_division')
                 ->pluck('level2_division');

            foreach ($divisions as $division_name ) {
                
                $node = OrganizationTree::where('name', $division_name )
                ->where('organization', $parent->organization )
                ->where('level1_program', $parent->name )
                ->where('level', 2)->first();

                // // check no of employee 
                // $count = EmployeeDemo::where('organization',$parent->organization)
                //             ->where('level1_program', $parent->name)
                //             ->where('level2_division', $division_name)
                //             ->count();

                if (!$node) {
                    $node = new OrganizationTree([
                        'name' => $division_name,
                        'status' => 1,
                        'level' => 2,
                        'organization' =>  $parent->organization,
                        'level1_program' =>  $parent->name,
                        'level2_division' =>  $division_name,
                        // 'no_of_employee' => $count,
                    ]);
                    $node->save(); // Saved as root
                    $parent->appendNode($node);
                } else {
                    $node->status = 1;
                    // $node->no_of_employee = $count;
                    $node->save();
                }

            }

            
        }

        // Level 3 - Branch  
        $this->info( now() );
        $this->info('Level 3 - Branch');

        $divisions = OrganizationTree::withDepth()->having('depth', '=', 2)->get();

        foreach ($divisions as $parent) {

            $branches =  EmployeeDemo::where('organization',$parent->organization)
                 ->where('level1_program', $parent->level1_program)
                 ->where('level2_division', $parent->name)
                 ->whereNotIn('level3_branch',['',' '])
                 ->select('level3_branch')
                 ->groupBy('level3_branch')
                 ->orderBy('level3_branch')
                 ->pluck('level3_branch');

            foreach ($branches as $branch_name ) {

                $node = OrganizationTree::where('name', $branch_name )
                ->where('organization', $parent->organization )
                ->where('level1_program', $parent->level1_program )
                ->where('level2_division', $parent->name )
                ->where('level', 3)->first();

                // // check no of employee 
                // $count = EmployeeDemo::where('organization',$parent->organization)
                // ->where('level1_program', $parent->level1_program)
                // ->where('level2_division', $parent->name)
                // ->where('level3_branch', $branch_name)
                // ->count();

                                
                if (!$node) {
                    $node = new OrganizationTree([
                        'name' => $branch_name,
                        'status' => 1,
                        'level' => 3,
                        'organization' =>  $parent->organization,
                        'level1_program' =>  $parent->level1_program,
                        'level2_division' =>  $parent->name,
                        'level3_branch' =>  $branch_name,
                        // 'no_of_employee' => $count,
                    ]);
                    $node->save(); // Saved as root
                    $parent->appendNode($node);
                } else {
                    $node->status = 1;
                    // $node->no_of_employee = $count;
                    $node->save();
                }

            }

        }


        // Level 4 
        $this->info( now() );
        $this->info('Level 4 - Level 4');
        
        $branches = OrganizationTree::withDepth()->having('depth', '=', 3)->get();

        foreach ($branches as $parent) {

            $level4_list =  EmployeeDemo::where('organization',$parent->organization)
                    ->where('level1_program', $parent->level1_program)
                    ->where('level2_division', $parent->level2_division)
                    ->where('level3_branch', $parent->name)
                    ->whereNotIn('level4',['',' '])
                    ->select('level4')
                    ->groupBy('level4')
                    ->orderBy('level4')
                    ->pluck('level4');

            foreach ($level4_list as $level4_name ) {

                $node = OrganizationTree::where('name', $level4_name )
                ->where('organization', $parent->organization )
                ->where('level1_program', $parent->level1_program )
                ->where('level2_division', $parent->level2_division )
                ->where('level3_branch', $parent->name )
                ->where('level', 4)->first();

                // check no of employee 
                // $count = EmployeeDemo::where('organization',$parent->organization)
                //             ->where('level1_program', $parent->level1_program)
                //             ->where('level2_division', $parent->level2_division)
                //             ->where('level3_branch', $parent->name)
                //             ->where('level4', $level4_name)
                //             ->count();

                if (!$node) {
                    $node = new OrganizationTree([
                        'name' => $level4_name,
                        'status' => 1,
                        'level' => 4,
                        'organization' =>  $parent->organization,
                        'level1_program' =>  $parent->level1_program,
                        'level2_division' =>  $parent->level2_division,
                        'level3_branch' => $parent->name,
                        'level4' => $level4_name,
                        // 'no_of_employee' => $count,
                    ]);
                    $node->save(); // Saved as root
                    $parent->appendNode($node);
                } else {
                    $node->status = 1;
                    // $node->no_of_employee = $count;
                    $node->save();
                }

            }
            
        }
        
        $this->info( now() );

        return 0;
    }
}
