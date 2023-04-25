<style>
    table {
        border-collapse: collapse;
    }

    td, th {
        border: 1px solid black;
        padding:5px;
    }
    .page-break {
        page-break-before: always;
    }
</style>
<h2 class="font-semibold text-xl text-primary leading-tight">Performance Development</h2>

    <h3  role="banner">
            Employee Record
    </h3> 
    <div class="card p-3">  
        <div class="form-row">
            <div class="form-group col-md-12">  
                <b>Goal Details:</b> <p>
                <?php foreach($data as $i=>$item){?>
                    <?php if($i>0){?>
                    <div class="page-break">
                    <?php }?>                 
                    <table class="table">
                        <tr>
                            <th>Title</th>
                            <th>Owner</th>
                            <th>Business Unit</th>
                            <th>Ministry</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created At</th>
                        </tr>
                        <tr>
                            <td><?php echo $item["selected_goal"]->title; ?></td>
                            <td><?php echo $item["selected_goal"]->name; ?></td>
                            <td><?php echo $item["selected_goal"]->business_unit; ?></td>
                            <td><?php echo $item["selected_goal"]->organization; ?></td>
                            <td>
                                <?php 
                                $dateTime = $item["selected_goal"]->start_date;
                                if(strtotime($dateTime)){
                                    $dateTime = new DateTime($dateTime);
                                    $dateTime = $dateTime->format('Y-m-d');
                                }
                                echo $dateTime; 
                                ?>
                            </td>
                            <td>
                                <?php 
                                $dateTime = $item["selected_goal"]->target_date;
                                if(strtotime($dateTime)){
                                    $dateTime = new DateTime($dateTime);
                                    $dateTime = $dateTime->format('Y-m-d');
                                }
                                echo $dateTime; 
                                ?>
                            </td>
                            <td>
                                <?php 
                                $dateTime = $item["selected_goal"]->created_at;
                                if(strtotime($dateTime)){
                                    $dateTime = new DateTime($dateTime);
                                    $dateTime = $dateTime->format('Y-m-d');
                                }
                                echo $dateTime; 
                                ?>
                            </td>
                        </tr>
                    </table>
                
                    <p><b>Description</b></p>
                    <?php echo $item["selected_goal"]->what; ?>

                    <p><b>Measure Of Success</b></p>
                    <?php echo $item["selected_goal"]->measure_of_success; ?>

                    <?php if(isset($item["selected_goal_comments"])){?>
                        <p><b>Comments</b></p>
                        <?php echo $item["selected_goal_comments"];?>
                    <?php } ?>
                    <p>
                    <?php if($i>0){?>
                    </div>
                    <?php }?>  
                 <?php } ?>
            </div>    
        </div>
    </div>
