@extends('sysadmin.layout', ['title' => 'Tags Management','tab_title' => 'Tags Management - Performance Development Platform'])
@section('page-content')
@push('css')
        <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <x-slot name="css">
            <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        </x-slot>
    @endpush

    @push('js')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
        <script src="https://code.jquery.com/jquery-3.6.0.js"></script>  
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    @endpush    
    
    <p><a href="/sysadmin/tag-new"><x-button type="button" class="btn-lg" id="new"> New Tag </x-button></a></p>
 
    <div class="card">
        <div class="card-body p-n5 ">
            <table id="tagslist" class="display table table-striped" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php  foreach($tags as $tag) { ?> 
                    <tr>
                        <td width="30%"><a href="/sysadmin/tag-detail/<?php echo $tag["id"]; ?>"><?php echo $tag["name"]; ?></a></td>
                        <td><?php echo $tag["description"]; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>    
    </div>   



    @push('js')
        <script type="text/javascript">
            let tagslist = $('#tagslist').DataTable({});

        </script>
    @endpush

@endsection

