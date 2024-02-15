
@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

<div class="card">
    <div class="d-flex mt-3">
        <h4></h4>
        <div class="px-1">

        </div>    
        <div class="flex-fill"></div>
    </div>


    <div class="card-body">
                <table class="table table-sm table-bordered rounded table-striped">
                    <thead>
                    <tr class="text-center bg-light">
                        <th class="col-2">ID</th>
                        <th class="col-2">Topic</th>
                        <th class="col-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($resources as $resource)
                     
                            <tr class="text-center">
                                <td class="col-2 text-left">{{$resource->id}} </td>
                                <td class="col-2 text-left">{{ $resource->name }}</td>
                                <td class="col-2">
                                    <a class="btn btn-info btn-sm"" href="{{ route('conversation-template.show',$resource->id) }}">Show</a>
                    
                                    <a class="btn btn-primary btn-sm"" href="{{ route('conversation-template.edit',$resource->id) }}">Edit</a>
                                </td>
                            </tr>
                        
                        @endforeach
                    </tbody>
                </table>
            </div>
    </div>
