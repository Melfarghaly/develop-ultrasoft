@foreach($children as $child)
    <li>
        <span class="toggle-node cost-center-node {{ $child->is_last_record ? 'last-record' : '' }}"
            data-id="{{ $child->id }}"
            data-code="{{ $child->code }}"
            data-name="{{ $child->name }}"
            data-childs="{{ $child->children->count() }}"
            data-parent-id="{{ $child->parent_id ?? '' }}"
            data-is-last="{{ $child->is_last_record ? 'true' : 'false' }}">
            <i class="icon"></i> {{ $child->code }} - {{ $child->name }}
        </span>
        <span class="badge bg-info rounded-pill pull-left hide">{{ $child->children->count() }}</span>
        <!-- delete button -->
        
            <button type="button" class="btn btn-default btn-sm delete-node" style="color:red !important" data-id="{{ $child->id }}" data-name="{{ $child->name }}" data-toggle="modal" data-target="#deleteModal">
            <i class="fa fa-trash"></i> 
        </button>

        @if($child->children->count() > 0)
            <ul class="nested">
                @include('cost_center.partials.tree_node', ['children' => $child->children])
            </ul>
        @endif
    </li>
@endforeach
