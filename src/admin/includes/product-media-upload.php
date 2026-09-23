<div class="bg-light p-4 rounded-3 border text-center">
    <label for="product-media" class="form-label">Product images and videos</label>
    <input id="product-media" type="file" name="product_media[]" multiple accept="image/jpeg,image/png,image/webp,video/mp4" class="form-control" aria-describedby="product-media-help">
    <p id="product-media-help" class="admin-meta text-muted mt-2 mb-0">Choose JPG, PNG, WEBP or MP4 files. Selected files upload when you save the product.</p>
    <?php if (!empty($productId)): ?>
        <button type="submit" class="btn btn-outline-primary mt-3" onclick="document.getElementById('form_action').value='upload_media'">Upload selected media</button>
    <?php endif; ?>
</div>
