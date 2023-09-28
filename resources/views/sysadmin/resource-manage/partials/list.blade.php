
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
        <div class="px-4">
            <form class="" action="{{ route('resource-manage.create') }}" method="GET">
                <button class="btn btn-primary" type="submit">Add a New Resource</button>
            </form>
        </div>
    </div>


    <div class="card-body">
                <table class="table table-sm table-bordered rounded table-striped">
                    <thead>
                    <tr class="text-center bg-light">
                        <th class="col-2">Category</th>
                        <th class="col-2">Subject</th>
                        <th class="col-6">Body</th>
                        <th class="col-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($resources as $resource)
                     
                            <tr class="text-center">
                                <td class="col-2 text-left">{{$resource->category}} </td>
                                <td class="col-2 text-left">{{ $resource->question }}</td>
                                <td class="col-6 text-left">{!! $resource->answer !!}</td>
                                <td class="col-2">
                                    <a class="btn btn-info btn-sm"" href="{{ route('resource-manage.show',$resource->content_id) }}">Show</a>
                    
                                    <a class="btn btn-primary btn-sm"" href="{{ route('resource-manage.edit',$resource->content_id) }}">Edit</a>
                                </td>
                            </tr>
                        
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col">
                    <span class="float-left px-2">
                        Showing {{ $resources->firstItem() }}–{{ $resources->lastItem() }} of {{ $resources->total() }} results
                        </span>
                </div>
                <div class="col">
                </div>
                <div class="col">
                    <span class="pr-4 float-right">
                        {{  $resources->withQueryString()->links('pagination::bootstrap-4')  }}                
                    </span>
                </div>
            </div>
    </div>
