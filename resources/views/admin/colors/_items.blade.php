@foreach($colors as $color)
<tr>
    <td>{{ $colors->firstItem() + $loop->index }}</td>
    <td>
        <span class="color-dot" style="background:{{ $color->code ?: '#ccc' }}"></span>
        {{ $color->name }}
    </td>
    <td>{{ $color->code }}</td>
    <td>
        <form method="POST" action="{{ route('admin.colors.toggle', $color) }}">
            @csrf
            @method('PATCH')
            <label class="switch">
                <input type="checkbox" onchange="this.form.submit()" {{ $color->is_active ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </form>
    </td>
    <td class="actions-cell">
        <button type="button"
                class="action-sq js-edit-btn" title="Edit" aria-label="Edit"
                data-form="color-form"
                data-action="{{ route('admin.colors.update', $color) }}"
                data-field-name="{{ $color->name }}"
                data-field-code="{{ $color->code }}">
            Edit
        </button>
        <form method="POST" action="{{ route('admin.colors.destroy', $color) }}" class="js-delete-form" data-confirm-title="Are you sure?">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
        </form>
    </td>
</tr>
@endforeach
