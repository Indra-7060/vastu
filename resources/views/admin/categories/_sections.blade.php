{{-- Sections shown on every product page of this category (e.g. "How to use Rudraksh"). --}}
@php
    $sectionRows = old('product_sections');
    if (! is_array($sectionRows)) {
        $sectionRows = isset($category) ? ($category->product_sections ?? []) : [];
    }
    $sectionRows = array_values(array_map(static fn ($row) => (array) $row, $sectionRows));
@endphp
<div class="category-sections">
    <h4 style="margin-bottom:4px;">Sections for every product</h4>
    <p class="hint" style="margin:0 0 12px;">Write a section once and it appears on <strong>every product page in this category</strong> (e.g. “How to use Rudraksh”, “How to wear”, “Care”). If a product has its own section with the same title, the product’s version is shown instead. Leave a section empty or remove it to hide it.</p>

    <div class="detail-repeater" data-cat-sections>
        @foreach($sectionRows as $i => $row)
            <div class="detail-repeater__row" data-cat-section>
                <div class="detail-repeater__head">
                    <strong>Section</strong>
                    <button type="button" class="btn btn-light btn-sm" data-cat-section-remove>Remove</button>
                </div>
                <div class="product-pricing-row" style="grid-template-columns: 1fr 2fr;">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="product_sections[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" maxlength="120" placeholder="e.g. How to use">
                    </div>
                    <div class="form-group">
                        <label>Text</label>
                        <textarea name="product_sections[{{ $i }}][text]" rows="5" placeholder="One paragraph per line">{{ $row['text'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-light" data-cat-section-add>+ Add Section</button>

    <template data-cat-section-template>
        <div class="detail-repeater__row" data-cat-section>
            <div class="detail-repeater__head">
                <strong>Section</strong>
                <button type="button" class="btn btn-light btn-sm" data-cat-section-remove>Remove</button>
            </div>
            <div class="product-pricing-row" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="product_sections[__I__][title]" maxlength="120" placeholder="e.g. How to use">
                </div>
                <div class="form-group">
                    <label>Text</label>
                    <textarea name="product_sections[__I__][text]" rows="5" placeholder="One paragraph per line"></textarea>
                </div>
            </div>
        </div>
    </template>
</div>
<script>
(function () {
    var box = document.currentScript.previousElementSibling;
    var list = box.querySelector('[data-cat-sections]');
    var tpl = box.querySelector('[data-cat-section-template]');
    var next = list.children.length; // always-increasing index, so names never collide
    box.querySelector('[data-cat-section-add]').addEventListener('click', function () {
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__I__/g, String(next++)));
        var input = list.lastElementChild.querySelector('input');
        if (input) input.focus();
    });
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-cat-section-remove]');
        if (btn) btn.closest('[data-cat-section]').remove();
    });
})();
</script>
