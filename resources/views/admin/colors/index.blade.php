@extends('admin.layouts.app')

@section('title', 'Colors - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Colors</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.colors.index') }}">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 style="margin-bottom:6px;" id="form-card-title">Add Color</h3>
    <p class="hint" style="margin-bottom:12px;">Pick a color — name fills automatically. You can still edit the name.</p>
    <form method="POST"
          action="{{ route('admin.colors.store') }}"
          class="inline-form"
          id="color-form"
          novalidate
          data-store-action="{{ route('admin.colors.store') }}"
          data-add-title="Add Color"
          data-edit-title="Edit Color"
          data-title-id="form-card-title">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" placeholder="Black">
        </div>
        <div class="form-group">
            <label>Color Code</label>
            <div class="color-code-row">
                <input type="color" id="color-picker" value="#000000" title="Pick a color" aria-label="Pick a color">
                <input type="text" name="code" id="color-code-input" placeholder="#000000" maxlength="7" value="#000000">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-light js-edit-cancel" hidden>Cancel</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Color</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.colors._items')
            </tbody>
        </table>
    </div>
    @if($colors->isEmpty())
        <div class="empty-state">No colors found.</div>
    @endif
</div>

<div class="pagination-wrap">
    {{ $colors->links() }}
</div>
@endsection

@push('scripts')
<script>
(function () {
    const picker = document.getElementById('color-picker');
    const codeInput = document.getElementById('color-code-input');
    const nameInput = document.querySelector('#color-form [name="name"]');
    if (!picker || !codeInput || !nameInput) return;

    const COLOR_NAMES = [
        ['Black', '#000000'], ['White', '#FFFFFF'], ['Red', '#FF0000'], ['Lime', '#00FF00'],
        ['Blue', '#0000FF'], ['Yellow', '#FFFF00'], ['Cyan', '#00FFFF'], ['Magenta', '#FF00FF'],
        ['Silver', '#C0C0C0'], ['Gray', '#808080'], ['Maroon', '#800000'], ['Olive', '#808000'],
        ['Green', '#008000'], ['Purple', '#800080'], ['Teal', '#008080'], ['Navy', '#000080'],
        ['Orange', '#FFA500'], ['Pink', '#FFC0CB'], ['Brown', '#A52A2A'], ['Beige', '#F5F5DC'],
        ['Ivory', '#FFFFF0'], ['Khaki', '#F0E68C'], ['Lavender', '#E6E6FA'], ['Coral', '#FF7F50'],
        ['Salmon', '#FA8072'], ['Tomato', '#FF6347'], ['Gold', '#FFD700'], ['Indigo', '#4B0082'],
        ['Violet', '#EE82EE'], ['Orchid', '#DA70D6'], ['Plum', '#DDA0DD'], ['Tan', '#D2B48C'],
        ['Wheat', '#F5DEB3'], ['Chocolate', '#D2691E'], ['Peru', '#CD853F'], ['Sienna', '#A0522D'],
        ['Crimson', '#DC143C'], ['Hot Pink', '#FF69B4'], ['Deep Pink', '#FF1493'], ['Light Blue', '#ADD8E6'],
        ['Sky Blue', '#87CEEB'], ['Dodger Blue', '#1E90FF'], ['Royal Blue', '#4169E1'], ['Steel Blue', '#4682B4'],
        ['Turquoise', '#40E0D0'], ['Aquamarine', '#7FFFD4'], ['Sea Green', '#2E8B57'], ['Forest Green', '#228B22'],
        ['Lime Green', '#32CD32'], ['Olive Drab', '#6B8E23'], ['Dark Gray', '#A9A9A9'], ['Dim Gray', '#696969'],
        ['Slate Gray', '#708090'], ['Charcoal', '#36454F'], ['Cream', '#FFFDD0'], ['Off White', '#FAF9F6'],
        ['Burgundy', '#800020'], ['Mustard', '#FFDB58'], ['Peach', '#FFE5B4'], ['Mint', '#98FF98'],
        ['Baby Blue', '#89CFF0'], ['Lilac', '#C8A2C8'], ['Rose', '#FF007F'], ['Wine', '#722F37'],
        ['Rust', '#B7410E'], ['Copper', '#B87333'], ['Bronze', '#CD7F32'], ['Champagne', '#F7E7CE'],
        ['Mauve', '#E0B0FF'], ['Fuchsia', '#FF00FF'], ['Azure', '#F0FFFF'], ['Alice Blue', '#F0F8FF'],
        ['Midnight Blue', '#191970'], ['Dark Green', '#006400'], ['Dark Red', '#8B0000'], ['Dark Orange', '#FF8C00'],
        ['Light Gray', '#D3D3D3'], ['Gainsboro', '#DCDCDC'], ['Snow', '#FFFAFA'], ['Honeydew', '#F0FFF0'],
        ['Sand', '#C2B280'], ['Taupe', '#483C32'], ['Espresso', '#3C1414'], ['Denim', '#1560BD'],
        ['Cobalt', '#0047AB'], ['Emerald', '#50C878'], ['Jade', '#00A86B'], ['Amber', '#FFBF00'],
        ['Apricot', '#FBCEB1'], ['Blush', '#DE5D83'], ['Berry', '#8E4585'], ['Grape', '#6F2DA8'],
    ];

    function hexToRgb(hex) {
        const h = hex.replace('#', '');
        return {
            r: parseInt(h.slice(0, 2), 16),
            g: parseInt(h.slice(2, 4), 16),
            b: parseInt(h.slice(4, 6), 16),
        };
    }

    function colorDistance(a, b) {
        return Math.sqrt(
            Math.pow(a.r - b.r, 2) +
            Math.pow(a.g - b.g, 2) +
            Math.pow(a.b - b.b, 2)
        );
    }

    function closestColorName(hex) {
        const target = hexToRgb(hex);
        let bestName = 'Custom';
        let bestDist = Infinity;
        COLOR_NAMES.forEach(function (item) {
            const dist = colorDistance(target, hexToRgb(item[1]));
            if (dist < bestDist) {
                bestDist = dist;
                bestName = item[0];
            }
        });
        return bestName;
    }

    function normalizeHex(value) {
        let v = (value || '').trim();
        if (!v) return '';
        if (v[0] !== '#') v = '#' + v;
        if (/^#[0-9A-Fa-f]{3}$/.test(v)) {
            v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3];
        }
        return v.toUpperCase();
    }

    function applyPickerColor(autofillName) {
        const hex = picker.value.toUpperCase();
        codeInput.value = hex;
        if (autofillName) {
            nameInput.value = closestColorName(hex);
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    function syncPickerFromText(autofillName) {
        const hex = normalizeHex(codeInput.value);
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            picker.value = hex;
            codeInput.value = hex;
            if (autofillName) {
                nameInput.value = closestColorName(hex);
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }

    picker.addEventListener('input', function () { applyPickerColor(true); });
    codeInput.addEventListener('change', function () { syncPickerFromText(true); });
    codeInput.addEventListener('blur', function () { syncPickerFromText(false); });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.js-edit-btn[data-form="color-form"]')) {
            setTimeout(function () { syncPickerFromText(false); }, 0);
        }
        if (e.target.closest('#color-form .js-edit-cancel')) {
            setTimeout(function () {
                picker.value = '#000000';
                codeInput.value = '#000000';
                nameInput.value = '';
            }, 0);
        }
    });
})();
</script>
@endpush
