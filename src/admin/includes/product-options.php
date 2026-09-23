<?php
$specs = json_decode($product['technical_specs_en'] ?? '{}', true) ?: [];
if (isset($_POST['tech_spec_keys'])) {
    $specs = [];
    foreach ($_POST['tech_spec_keys'] as $index => $key) $specs[$key] = $_POST['tech_spec_values'][$index] ?? '';
}
$formVariants = $existingVariants ?? [];
if (isset($_POST['variants'])) {
    $formVariants = [];
    foreach ($_POST['variants'] as $variant) {
        $attributes = [];
        foreach ($variant['attributes']['name'] ?? [] as $index => $key) $attributes[$key] = $variant['attributes']['value'][$index] ?? '';
        $formVariants[] = ['id' => $variant['id'] ?? '', 'sku' => $variant['sku'] ?? '', 'variant_name' => $variant['name'] ?? '', 'price' => $variant['price'] ?? '', 'stock_quantity' => $variant['stock'] ?? 0, 'attributes' => $attributes];
    }
}
?>
<!-- Technical Specs Card -->
                            <div class="card border-0 shadow-sm mb-4" id="product-variants">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                                <h5 class="mb-0 fw-bold px-2"><i class="fas fa-list-ul me-2 text-success"></i> Technical Specifications</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="addTechSpec()">
                                    <i class="fas fa-plus me-1"></i> Add Spec
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <div id="techSpecsContainer">
                                    <?php 
                                                                        if (!empty($specs)):
                                        foreach ($specs as $key => $value):
                                    ?>
                                        <div class="tech-spec-item mb-3 p-3 bg-light rounded-3 border border-light-subtle">
                                            <div class="row g-2 align-items-center">
                                                <div class="col">
                                                    <input type="text" name="tech_spec_keys[]" class="form-control form-control-sm border-light-subtle shadow-none" value="<?php echo htmlspecialchars($key); ?>" placeholder="e.g., Processor">
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="tech_spec_values[]" class="form-control form-control-sm border-light-subtle shadow-none" value="<?php echo htmlspecialchars($value); ?>" placeholder="e.g., Intel i7">
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-danger btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;" onclick="this.closest('.tech-spec-item').remove(); checkTechSpecsEmpty();" aria-label="Remove field">
                                                        <i class="fas fa-trash-alt small"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach;
                                    else: ?>
                                        <div class="text-center py-4 bg-light rounded-3 border border-dashed border-2">
                                            <p class="text-muted mb-0 small">No technical specifications added yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Variants Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                                <h5 class="mb-0 fw-bold px-2"><i class="fas fa-layer-group me-2 text-info"></i> Product Variants</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="addVariant()">
                                    <i class="fas fa-plus me-1"></i> Add Variant
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small mb-4 px-2">Define variants with specific attributes (e.g., Color, Size). Each variant is a unique item.</p>
                                <div id="variants-container">
                                    <?php if (!empty($formVariants)): ?>
                                        <?php foreach ($formVariants as $index => $variant): ?>
                                            <div class="variant-card card border-light-subtle shadow-none mb-4 bg-light-subtle">
                                                <input type="hidden" name="variants[<?php echo $index; ?>][id]" value="<?php echo (int)($variant['id'] ?? 0); ?>">
                                                <input type="hidden" name="variants[<?php echo $index; ?>][sku]" value="<?php echo htmlspecialchars($variant['sku'] ?? ''); ?>">
                                                <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between border-bottom-0 rounded-top-3">
                                                    <h6 class="mb-0 fw-bold text-muted small">Variant #<?php echo $index + 1; ?> · <?php echo htmlspecialchars($variant['sku'] ?? 'New SKU'); ?></h6>
                                                    <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0 fw-bold shadow-none" onclick="this.closest('.variant-card').remove()">
                                                        <i class="fas fa-times me-1"></i> Remove
                                                    </button>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Display Name</label>
                                                            <input type="text" name="variants[<?php echo $index; ?>][name]" class="form-control form-control-sm border-light-subtle shadow-none" value="<?php echo htmlspecialchars($variant['variant_name'] ?? ''); ?>" placeholder="e.g. Red">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Price Override</label>
                                                            <input type="number" name="variants[<?php echo $index; ?>][price]" class="form-control form-control-sm border-light-subtle shadow-none" value="<?php echo admin_escape($variant['price']); ?>" step="0.01" min="0">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Stock</label>
                                                            <input type="number" name="variants[<?php echo $index; ?>][stock]" class="form-control form-control-sm border-light-subtle shadow-none" value="<?php echo admin_escape($variant['stock_quantity']); ?>" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <label class="form-label x-small fw-bold text-muted text-uppercase mb-2 d-block">Attributes</label>
                                                        <div class="attributes-list" id="attributes-container-<?php echo $index; ?>">
                                                            <?php if (!empty($variant['attributes'])): ?>
                                                                <?php foreach ($variant['attributes'] as $attrName => $attrValue): ?>
                                                                    <div class="attribute-row row g-2 mb-2 align-items-center bg-white p-2 rounded border mx-0 border-light-subtle shadow-xs">
                                                                        <div class="col">
                                                                            <input type="text" name="variants[<?php echo $index; ?>][attributes][name][]" class="form-control form-control-sm border-0 bg-light" value="<?php echo htmlspecialchars($attrName); ?>" placeholder="e.g. Color">
                                                                        </div>
                                                                        <div class="col">
                                                                            <input type="text" name="variants[<?php echo $index; ?>][attributes][value][]" class="form-control form-control-sm border-0 bg-light" value="<?php echo htmlspecialchars($attrValue); ?>" placeholder="e.g. Red">
                                                                        </div>
                                                                        <div class="col-auto">
                                                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 shadow-none" onclick="this.closest('.attribute-row').remove()" aria-label="Remove field">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 fw-bold rounded-pill px-3 shadow-xs border-light-subtle" onclick="addAttribute(<?php echo $index; ?>)">
                                                            <i class="fas fa-plus me-1 small"></i> Add Attribute
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
