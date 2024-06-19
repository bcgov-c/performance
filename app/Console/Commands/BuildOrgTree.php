<?php

namespace App\Console\Commands;

use App\Models\EmployeeDemo;
use Illuminate\Console\Command;
use App\Models\OrganizationTree;
use App\Models\JobSchedAudit;
use Carbon\Carbon;

class BuildOrgTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:buildOrgTree {--manual}';

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
        $switch = strtolower(env('PRCS_BUILD_ORG_TREE'));
        $manualoverride = (strtolower($this->option('manual')) ? true : false);
  
        if ($switch == 'on' || $manualoverride) {

            $this->info( now() );
            $this->info('Level 0 - Oragnization Level');

            $Organizations =  EmployeeDemo::whereNotIn('organization',['',' '])
            ->select('organization', 'deptid')->distinct()->get();

            foreach ($Organizations as $organization ) {

                $node = OrganizationTree::where('name', $organization->organization )
                    ->where('level', 0)->first();

                // check no of employee 
                // $count = EmployeeDemo::where('organization',$organization_name)->count();

                if (!$node) {
                    $node = new OrganizationTree([
                            'name' => $organization->organization,
                            'status' => 1,
                            'level' => 0,
                            'organization' =>  $organization->organization,
                            // 'no_of_employee' => $count,
                    ]);
                    $node->deptid = $organization->deptid;
                    $node->saveAsRoot(); // Saved as root
                } else {
                    $node->status = 1;
                    $node->deptid = $organization->deptid;
                    // $node->no_of_employee = $count;
                    $node->save();
                }

            }


            // Level 1 - Programs 
            $this->info( now() );
            $this->info('Level 1 - Programs');

            $organizations = OrganizationTree::withDepth()->having('depth', '=', 0)->get();

            foreach ($organizations as $parent ) {

                $programs =  EmployeeDemo::where('organization', $parent->organization )
                    ->whereNotIn('level1_program',['',' '])
                    ->select('level1_program', 'deptid')
                    ->distinct()
                    ->orderBy('level1_program')
                    ->get();

                foreach ($programs as $program ) {
                    $node = OrganizationTree::where('name', $program->level1_program )
                        ->where('organization', $parent->organization )
                        ->where('level', 1)->first();

                    // check no of employee 
                    // $count = EmployeeDemo::where('organization',$parent->name)
                    //             ->where('level1_program', $program_name)
                    //             ->count();

                    if (!$node) {
                        $node = new OrganizationTree([
                            'name' => $program->level1_program,
                            'status' => 1,
                            'level' => 1,
                            'organization' =>  $parent->organization,
                            'level1_program' =>  $program->level1_program,
                            // 'no_of_employee' => $count,
                        ]);
                        $node->deptid = $program->deptid;
                        $node->save(); // Saved as root
                        $parent->appendNode($node);
                    } else {
                        $node->status = 1;
                        $node->deptid = $program->deptid;
                        // $node->no_of_employee = $count;
                        $node->save();
                    }
                }
            }

            // Level 2 - Division  
            $this->info( now() );
            $this->info('Level 2 - Division');

            $programs = OrganizationTree::withDepth()->having('depth', '=', 1)->get();

            foreach ($programs as $parent ) {
                $divisions =  EmployeeDemo::where('organization', $parent->organization)
                    ->where('level1_program', $parent->level1_program)
                    ->whereNotIn('level2_division',['',' '])
                    ->select('level2_division', 'deptid')
                    ->distinct()
                    ->orderBy('level2_division')
                    ->get();

                foreach ($divisions as $division ) {
                    
                    $node = OrganizationTree::where('name', $division->level2_division )
                    ->where('organization', $parent->organization )
                    ->where('level1_program', $parent->level1_program )
                    ->where('level', 2)->first();

                    // // check no of employee 
                    // $count = EmployeeDemo::where('organization',$parent->organization)
                    //             ->where('level1_program', $parent->name)
                    //             ->where('level2_division', $division_name)
                    //             ->count();

                    if (!$node) {
                        $node = new OrganizationTree([
                            'name' => $division->level2_division,
                            'status' => 1,
                            'level' => 2,
                            'organization' =>  $parent->organization,
                            'level1_program' =>  $parent->level1_program,
                            'level2_division' =>  $division->level2_division,
                            // 'no_of_employee' => $count,
                        ]);
                        $node->deptid = $division->deptid;
                        $node->save(); // Saved as root
                        $parent->appendNode($node);
                    } else {
                        $node->status = 1;
                        $node->deptid = $division->deptid;
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

                $branches =  EmployeeDemo::where('organization', $parent->organization)
                    ->where('level1_program', $parent->level1_program)
                    ->where('level2_division', $parent->level2_division)
                    ->whereNotIn('level3_branch',['',' '])
                    ->select('level3_branch', 'deptid')
                    ->distinct()
                    ->orderBy('level3_branch')
                    ->get();

                foreach ($branches as $branch ) {

                    $node = OrganizationTree::where('name', $branch->level3_branch )
                    ->where('organization', $parent->organization )
                    ->where('level1_program', $parent->level1_program )
                    ->where('level2_division', $parent->level2_division )
                    ->where('level', 3)->first();

                    // // check no of employee 
                    // $count = EmployeeDemo::where('organization',$parent->organization)
                    // ->where('level1_program', $parent->level1_program)
                    // ->where('level2_division', $parent->name)
                    // ->where('level3_branch', $branch_name)
                    // ->count();

                                    
                    if (!$node) {
                        $node = new OrganizationTree([
                            'name' => $branch->level3_branch,
                            'status' => 1,
                            'level' => 3,
                            'organization' =>  $parent->organization,
                            'level1_program' =>  $parent->level1_program,
                            'level2_division' =>  $parent->level2_division,
                            'level3_branch' =>  $branch->level3_branch,
                            // 'no_of_employee' => $count,
                        ]);
                        $node->deptid = $branch->deptid;
                        $node->save(); // Saved as root
                        $parent->appendNode($node);
                    } else {
                        $node->status = 1;
                        $node->deptid = $branch->deptid;
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
                        ->where('level3_branch', $parent->level3_branch)
                        ->whereNotIn('level4',['',' '])
                        ->select('level4', 'deptid')
                        ->distinct()
                        ->orderBy('level4')
                        ->get();

                foreach ($level4_list as $level4 ) {

                    $node = OrganizationTree::where('name', $level4->level4 )
                    ->where('organization', $parent->organization )
                    ->where('level1_program', $parent->level1_program )
                    ->where('level2_division', $parent->level2_division )
                    ->where('level3_branch', $parent->level3_branch )
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
                            'name' => $level4->level4,
                            'status' => 1,
                            'level' => 4,
                            'organization' =>  $parent->organization,
                            'level1_program' =>  $parent->level1_program,
                            'level2_division' =>  $parent->level2_division,
                            'level3_branch' => $parent->level3_branch,
                            'level4' => $level4->level4,
                            // 'no_of_employee' => $count,
                        ]);
                        $node->deptid = $level4->deptid;
                        $node->save(); // Saved as root
                        $parent->appendNode($node);
                    } else {
                        $node->status = 1;
                        $node->deptid = $level4->deptid;
                        // $node->no_of_employee = $count;
                        $node->save();
                    }
                }
            }
            
            $this->info( now() );

        } else {
            $start_time = Carbon::now()->format('c');
            $audit_id = JobSchedAudit::insertGetId(
            [
                'job_name' => 'command:buildOrgTree',
                'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                'status' => 'Disabled'
            ]
            );
            $this->info( 'Process is currently disabled; or "PRCS_BUILD_ORG_TREE=on" is currently missing in the .env file.');
        }

        return 0;
    }
}
