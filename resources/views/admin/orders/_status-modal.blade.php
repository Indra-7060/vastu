<div class="confirm-overlay" id="order-status-overlay" aria-hidden="true"></div>
<div class="order-status-modal" id="order-status-modal" role="dialog" aria-modal="true" hidden>
    <div class="order-status-modal-head">
        <h3>Update Order Status</h3>
        <button type="button" class="modal-close" id="order-status-close" aria-label="Close">×</button>
    </div>

    <div class="order-status-modal-body">
        <div class="delivery-date-row">
            <label>Expected Delivery Date:</label>
            <div class="delivery-date-controls">
                <input type="text" id="expected-delivery-date" placeholder="DD-MM-YYYY" autocomplete="off">
                <button type="button" class="btn btn-delivery-update" id="btn-update-delivery" title="Save the delivery date (and the status, if one is selected)">@include('admin.partials.icon', ['name' => 'calendar', 'size' => 15]) Update</button>
            </div>
        </div>

        <div class="order-timeline" id="order-timeline"></div>

        <form id="order-status-form" novalidate>
            <div class="offer-form-row">
                <label class="offer-label">Status <span>:-</span></label>
                <div class="offer-field form-group">
                    <select name="status" id="order-status-select">
                        <option value="">---Select Status---</option>
                    </select>
                    <p class="hint" id="status-hint" style="color:#888;"></p>
                </div>
            </div>
            <div class="offer-form-row offer-form-row-top">
                <label class="offer-label">Short Description <span>:-</span></label>
                <div class="offer-field form-group">
                    <textarea name="description" id="order-status-description" rows="4" placeholder="Enter short description"></textarea>
                </div>
            </div>
            <div class="offer-form-actions order-status-actions">
                <p class="order-status-actions__hint">Saves the new status and any change to the delivery date.</p>
                <button type="submit" class="btn btn-primary order-status-save" id="order-status-save">@include('admin.partials.icon', ['name' => 'truck', 'size' => 16]) Save status</button>
            </div>
        </form>
    </div>
</div>
