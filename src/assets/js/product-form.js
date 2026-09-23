        function toggleSKUField() {
            const checkbox = document.getElementById('auto_generate_sku');
            const skuInput = document.getElementById('sku');
            
            if (checkbox.checked) {
                skuInput.placeholder = 'Will be auto-generated';
                skuInput.readOnly = true;
                skuInput.classList.add('bg-light');
            } else {
                skuInput.placeholder = 'Enter SKU manually';
                skuInput.readOnly = false;
                skuInput.classList.remove('bg-light');
                skuInput.focus();
            }
        }

        // Add Technical Specification
        function addTechSpec() {
            const container = document.getElementById('techSpecsContainer');
            if (container.querySelector('.text-center')) {
                container.innerHTML = '';
            }
            
            const item = document.createElement('div');
            item.className = 'tech-spec-item mb-3 p-3 bg-light rounded-3 border border-light-subtle';
            item.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <input type="text" name="tech_spec_keys[]" class="form-control form-control-sm border-light-subtle" placeholder="e.g., Processor, RAM">
                    </div>
                    <div class="col">
                        <input type="text" name="tech_spec_values[]" class="form-control form-control-sm border-light-subtle" placeholder="e.g., Intel i7, 16GB">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;" onclick="this.closest('.tech-spec-item').remove(); checkTechSpecsEmpty();" aria-label="Remove field">
                            <i class="fas fa-trash-alt small"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
            AdminUI.refresh(container);
            item.querySelector('input').focus();
        }

        function checkTechSpecsEmpty() {
            const container = document.getElementById('techSpecsContainer');
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 bg-light rounded-3 border border-dashed border-2">
                        <p class="text-muted mb-0 small">No technical specifications added yet.</p>
                    </div>
                `;
            }
        }
        
        let variantCount = Math.max(-1, ...Array.from(document.querySelectorAll('[id^="attributes-container-"]'), node => Number(node.id.split('-').pop()))) + 1;

        function addVariant() {
            variantCount++;
            const container = document.getElementById('variants-container');
            const newVariantCard = document.createElement('div');
            newVariantCard.className = 'variant-card card border-light-subtle shadow-none mb-4 bg-light-subtle';
            newVariantCard.innerHTML = `
                <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between border-bottom-0 rounded-top-3">
                    <h6 class="mb-0 fw-bold text-muted small">Variant #${variantCount}</h6>
                    <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0 fw-bold shadow-none" onclick="this.closest('.variant-card').remove()">
                        <i class="fas fa-times me-1"></i> Remove
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Display Name (e.g. Red, XL)</label>
                            <input type="text" name="variants[${variantCount}][name]" class="form-control form-control-sm shadow-none" placeholder="Display Name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Price Override (Leave empty for base)</label>
                            <input type="number" name="variants[${variantCount}][price]" class="form-control form-control-sm shadow-none" placeholder="Price" step="0.01" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Stock</label>
                            <input type="number" name="variants[${variantCount}][stock]" class="form-control form-control-sm shadow-none" placeholder="0" value="0" min="0">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="form-label x-small fw-bold text-muted text-uppercase mb-2 d-block">Attributes Configuration</label>
                        <div class="attributes-list" id="attributes-container-${variantCount}">
                            <!-- Attributes will be added here -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 fw-bold rounded-pill px-3 shadow-xs border-light-subtle" onclick="addAttribute(${variantCount})">
                            <i class="fas fa-plus me-1 small"></i> Add Attribute
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newVariantCard);
            AdminUI.refresh(newVariantCard);
            addAttribute(variantCount);
        }

        function addAttribute(vIndex) {
            const container = document.getElementById(`attributes-container-${vIndex}`);
            const row = document.createElement('div');
            row.className = 'attribute-row row g-2 mb-2 align-items-center bg-white p-2 rounded border mx-0 border-light-subtle shadow-xs';
            row.innerHTML = `
                <div class="col">
                    <input type="text" name="variants[${vIndex}][attributes][name][]" class="form-control form-control-sm border-0 bg-light" placeholder="e.g. Color">
                </div>
                <div class="col">
                    <input type="text" name="variants[${vIndex}][attributes][value][]" class="form-control form-control-sm border-0 bg-light" placeholder="e.g. Red">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-link btn-sm text-danger p-0 shadow-none" onclick="this.closest('.attribute-row').remove()" aria-label="Remove field">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            AdminUI.refresh(row);
            row.querySelector('input').focus();
        }

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('productForm');
    const gallery = document.getElementById('image-gallery');
    if (gallery) {
        function syncMediaButtons() {
            const items = [...gallery.querySelectorAll('.image-item')];
            items.forEach((item, index) => {
                item.querySelector('[data-move="previous"]').disabled = index === 0;
                item.querySelector('[data-move="next"]').disabled = index === items.length - 1;
            });
        }
        gallery.querySelectorAll('.image-item').forEach(item => {
            const actions = document.createElement('div');
            actions.className = 'admin-media-actions';
            for (const direction of ['previous', 'next']) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-sm btn-outline-secondary';
                button.dataset.move = direction;
                const label = 'Move image ' + (direction === 'previous' ? 'earlier' : 'later');
                const icon = document.createElement('i');
                icon.className = direction === 'previous' ? 'fas fa-arrow-left' : 'fas fa-arrow-right';
                icon.setAttribute('aria-hidden', 'true');
                button.appendChild(icon);
                button.setAttribute('aria-label', label);
                button.title = label;
                button.addEventListener('click', () => {
                    const items = [...gallery.querySelectorAll('.image-item')];
                    const index = items.indexOf(item);
                    const sibling = items[index + (direction === 'previous' ? -1 : 1)];
                    if (!sibling) return;
                    if (direction === 'previous') sibling.before(item); else sibling.after(item);
                    syncMediaButtons();
                    saveImageOrder();
                    button.focus();
                });
                actions.appendChild(button);
            }
            item.appendChild(actions);
        });
        syncMediaButtons();
        gallery.addEventListener('drop', () => queueMicrotask(syncMediaButtons));
    }
    if (!form || !window.jQuery || !jQuery.fn.summernote) return;
    jQuery('.summernote-editor').summernote({
        placeholder: 'Enter detailed description here…', height: 400, tabsize: 2,
        toolbar: [['style', ['style']], ['font', ['bold', 'underline', 'clear']], ['color', ['color']], ['para', ['ul', 'ol', 'paragraph']], ['table', ['table']], ['insert', ['link', 'picture', 'video']], ['view', ['fullscreen', 'codeview', 'help']]],
        callbacks: {onImageUpload(files) { Array.from(files).forEach(file => uploadImage(file, this)); }}
    });
    form.addEventListener('submit', event => {
        if (event.submitter?.getAttribute('onclick')?.includes('upload_media') || event.submitter?.dataset.confirm) return;
        const action = document.getElementById('form_action');
        if (action) action.value = Number(form.dataset.productId) ? 'update_product' : 'add_product';
    });
});
async function uploadImage(file, editor) {
    const data = new FormData();
    data.append('file', file);
    data.append('product_id', document.getElementById('productForm').dataset.productId);
    data.append('slug', document.getElementById('name_en').value);
    try {
        const response = await fetch('ajax/upload_editor_image.php', {method: 'POST', body: data});
        if (!response.ok) throw new Error('Upload failed. Please try again.');
        const url = (await response.text()).trim();
        if (!url.startsWith('uploads/')) throw new Error('The server could not upload this image.');
        const image = document.createElement('img');
        image.src = '../' + url;
        image.alt = '';
        jQuery(editor).summernote('insertNode', image);
        showToast('Image uploaded.');
    } catch (error) { showToast(error.message, 'error'); }
}
