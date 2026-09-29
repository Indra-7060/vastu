@foreach($subCategories as $item)
<tr>
    <td>{{ $subCategories->firstItem() + $loop->index }}</td>
    <td>{{ $item->title }}</td>
    <td>{{ $item->category->title ?? '-' }}</td>
    <td>
        <form method="POST" action="{{ route('admin.sub-categories.toggle', $item) }}">
            @csrf
            @method('PATCH')
            <label class="switch">
                <input type="checkbox" onchange="this.form.submit()" {{ $item->is_active ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </form>
    </td>
    <td>
        <form method="POST" action="{{ route('admin.sub-categories.destroy', $item) }}" class="js-delete-form" data-confirm-title="Are you sure?">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
        </form>
    </td>
</tr>
@endforeach
