@foreach($brands as $brand)
<tr>
    <td>{{ $brands->firstItem() + $loop->index }}</td>
    <td>{{ $brand->name }}</td>
    <td>{{ $brand->slug }}</td>
    <td>
        <form method="POST" action="{{ route('admin.brands.toggle', $brand) }}">
            @csrf
            @method('PATCH')
            <label class="switch">
                <input type="checkbox" onchange="this.form.submit()" {{ $brand->is_active ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </form>
    </td>
    <td class="actions-cell">
        <button type="button"
                class="action-sq js-edit-btn" title="Edit" aria-label="Edit"
                data-form="brand-form"
                data-action="{{ route('admin.brands.update', $brand) }}"
                data-field-name="{{ $brand->name }}">
            Edit
        </button>
        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="js-delete-form" data-confirm-title="Are you sure?">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
        </form>
    </td>
</tr>
@endforeach
