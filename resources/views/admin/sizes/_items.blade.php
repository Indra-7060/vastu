@foreach($sizes as $size)
<tr>
    <td>{{ $sizes->firstItem() + $loop->index }}</td>
    <td>{{ $size->name }}</td>
    <td>
        <form method="POST" action="{{ route('admin.sizes.toggle', $size) }}">
            @csrf
            @method('PATCH')
            <label class="switch">
                <input type="checkbox" onchange="this.form.submit()" {{ $size->is_active ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </form>
    </td>
    <td class="actions-cell">
        <button type="button"
                class="action-sq js-edit-btn" title="Edit" aria-label="Edit"
                data-form="size-form"
                data-action="{{ route('admin.sizes.update', $size) }}"
                data-field-name="{{ $size->name }}">
            Edit
        </button>
        <form method="POST" action="{{ route('admin.sizes.destroy', $size) }}" class="js-delete-form" data-confirm-title="Are you sure?">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
        </form>
    </td>
</tr>
@endforeach
