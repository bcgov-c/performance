@forelse ($sharedProfiles as $shared)
    <div class="py-1 px-2 border rounded">
        <strong>{{$shared->sharedWith->name}}</strong>
    </div>
    <div class="p-2">
        <form action="{{route('my-team.profile-shared-with.update', $shared->id)}}" method="POST" class="share-profile-form-edit">
        @csrf
        <table class="table table-sm">
            <tr style="background-color: #ccc">
                <th style="width:35%">Shared Elements</th>
                <th style="width:35%">Reason</th>
                <th style="width:30%">Action</th>
            </tr>
            <tr>
                <td>
                    <div class="view-mode">
                        {{count($shared->shared_item) == 2 ? 'All' : ($shared->shared_item[0] == 1 ? 'Goal' : 'Conversation')}}
                        <x-button class="edit-field" type="button" style="link" size="sm" icon="edit" data-action="item-edit"></x-button>
                    </div>
                    <div class="edit-mode item-edit d-none">
                        <div class="d-flex">
                            <x-dropdown name="shared_item[]" :list="[['id'=>1, 'name'=> 'Goals', 'selected'=>in_array(1, $shared->shared_item)], ['id'=>2, 'name'=> 'Conversations',  'selected'=>in_array(2, $shared->shared_item)]]" multiple class="items-to-share-edit"></x-dropdown>
                            <span class="pl-1">
                                <x-button onclick="this.form.submitted=this.value;" value="items" size="sm" name="action" type="submit">Save</x-button>
                            </span>
                        </div>
                    </div>
                    </form>
                </td>
                <td>
                    <div class="view-mode">
                        {{$shared->comment}}
                        <x-button class="edit-field" type="button" style="link" size="sm" icon="edit" data-action="update-reason"></x-button>
                    </div>
                    <div class="edit-mode reason-edit d-none">
                        <div class="d-flex">
                            <x-input :value="$shared->comment" name="comment" size="sm"></x-input>
                            <span class="pl-1">
                                <x-button onclick="this.form.submitted=this.value;" value="comment" size="sm" name="action" type="submit">Save</x-button>
                            </span>
                        </div>
                    </div>
                </td>
                <td>
                    <x-button onclick="this.form.submitted=this.value;" value="stop" type="submit"  style="link" size="sm">{{__('Stop sharing')}}</x-button>
                </td>
            </tr>
        </table>
    </div>
    @empty
    None
@endforelse
