
@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

<div class="card">
    <div class="d-flex mt-3">
        <h4></h4>
        <div class="px-1">
            {{-- 
            <form action="{{ route('generic-template.index') }}" class="form-inline" method="get">
                <div class="form-group mx-sm-3 mb-2">
                  <input style="width: 18em;" type="text" class="form-control" name="q" placeholder="name or description" 
                    value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Search</button>
              </form>
             --}}

        </div>    
        <div class="flex-fill"></div>
        <div class="px-4">
            <form class="" action="{{ route('generic-template.create') }}" method="GET">
                <button class="btn btn-primary" type="submit">Add a New Value</button>
            </form>
        </div>
    </div>


    <div class="card-body">
                <table class="table table-sm table-bordered rounded table-striped">
                    <thead>
                    <tr class="text-center bg-light">
                        <th class="col-2">Template Name</th>
                        <th class="col-8">Description</th>
                        <th class="col-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($generic_templates as $generic_template)
                     
                            <tr class="text-center">
                                <td class="col-2 text-left">{{$generic_template->template}} </td>
                                <td class="col-8 text-left">{{ $generic_template->description }}</td>
                                <td class="col-2">
                                    <a class="btn btn-info btn-sm"" href="{{ route('generic-template.show',$generic_template->id) }}">Show</a>
                    
                                    <a class="btn btn-primary btn-sm"" href="{{ route('generic-template.edit',$generic_template->id) }}">Edit</a>
                                </td>
                            </tr>
                        
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col">
                    <span class="float-left px-2">
                        Showing {{ $generic_templates->firstItem() }}–{{ $generic_templates->lastItem() }} of {{ $generic_templates->total() }} results
                        </span>
                </div>
                <div class="col">
                </div>
                <div class="col">
                    <span class="pr-4 float-right">
                        {{  $generic_templates->withQueryString()->links('pagination::bootstrap-4')  }}                
                    </span>
                </div>
            </div>
    </div>
