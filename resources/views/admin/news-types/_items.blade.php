@forelse($newsTypes as $type)
<tr>
    <td>{{ $newsTypes->firstItem() + $loop->index }}</td>
    <td>{{ $type->title }}</td>
    <td>{{ $type->slug }}</td>
    <td>{{ $type->sort_order }}</td>
    <td>
        <form method="POST" action="{{ route('admin.news-types.toggle', $type) }}">
            @csrf
            @method('PATCH')
            <label class="switch">
                <input type="checkbox" onchange="this.form.submit()" {{ $type->is_active ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </form>
    </td>
    <td>
        <button type="button"
                class="action-sq js-edit-btn" title="Edit" aria-label="Edit"
                data-form="news-type-form"
                data-action="{{ route('admin.news-types.update', $type) }}"
                data-field-title="{{ $type->title }}"
                data-field-sort_order="{{ $type->sort_order }}">
            Edit
        </button>
        <form method="POST" action="{{ route('admin.news-types.destroy', $type) }}" class="js-delete-form" data-confirm-title="Delete this news type?">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
        </form>
    </td>
</tr>
@empty
@endforelse
