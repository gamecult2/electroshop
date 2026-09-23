<?php
// admin/footer.php
// Shared footer for admin pages
?>
            </main> <!-- End of Page Content -->
        </div> <!-- End of Main Content Area -->
    </div> <!-- End of container-fluid -->

    <div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-labelledby="adminConfirmTitle" aria-describedby="adminConfirmMessage" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title h5" id="adminConfirmTitle">Confirm action</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><p id="adminConfirmMessage" class="mb-0"></p></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="adminConfirmAccept">Confirm</button></div>
        </div></div>
    </div>
    <!-- Bootstrap 5 Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="statusToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Updated successfully
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateProduct(element, id, field) {
            let value;
            if (element.type === 'checkbox') {
                value = element.checked ? 1 : 0;
            } else if (element.dataset.current !== undefined) {
                value = element.dataset.current === '1' ? 0 : 1;
            } else {
                value = element.value;
            }

            if (element.disabled) return;
            const previous = element.dataset.savedValue ?? element.defaultValue;
            AdminUI.busy(element, true);

            fetch('api/update_product_quick.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({id, field, value})
            })
            .then(response => response.json())
            .then(data => {
                AdminUI.busy(element, false);

                if (data.success) {
                    element.dataset.savedValue = String(value);
                    showToast('Updated successfully', 'success');

                    // Update badge style using Bootstrap classes
                    if (element.dataset.current !== undefined) {
                        const newValue = value;
                        element.dataset.current = String(newValue);
                        element.setAttribute('aria-pressed', newValue ? 'true' : 'false');
                        element.classList.remove('bg-success', 'bg-secondary', 'bg-warning', 'bg-info', 'bg-danger', 'bg-light', 'text-dark', 'text-white', 'text-muted', 'border', 'opacity-50');
                        if (field === 'is_active') {
                            element.classList.add(newValue ? 'bg-success' : 'bg-secondary');
                            element.textContent = newValue ? 'Active' : 'Inactive';
                        } else {
                            if (!newValue) {
                                element.classList.add('bg-light', 'text-muted', 'border', 'opacity-50');
                            } else if (field === 'is_featured') {
                                element.classList.add('bg-warning', 'text-dark');
                            } else if (field === 'is_new_arrival') {
                                element.classList.add('bg-info', 'text-white');
                            } else {
                                element.classList.add('bg-danger', 'text-white');
                            }
                        }
                    } else if (element.type === 'checkbox') {
                        const badge = element.nextElementSibling;
                        if (badge && badge.classList.contains('badge')) {
                            if (field === 'is_active') {
                                badge.className = 'badge rounded-pill py-2 px-3 fw-bold ' + (value ? 'bg-success' : 'bg-secondary');
                                badge.textContent = value ? 'Active' : 'Inactive';
                            } else {
                                if (!value) {
                                    badge.classList.add('opacity-50');
                                } else {
                                    badge.classList.remove('opacity-50');
                                }
                            }
                        }
                    }
                } else {
                    if (element.type === 'checkbox') element.checked = !element.checked;
                    else if (element.dataset.current === undefined) element.value = previous;
                    showToast('Update failed: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                AdminUI.busy(element, false);
                if (element.type === 'checkbox') element.checked = !element.checked;
                else if (element.dataset.current === undefined) element.value = previous;
                showToast('Error connecting to server', 'danger');
                console.error('Error:', error);
            });
        }

        function toggleStatus(element, id, field) {
            updateProduct(element, id, field);
        }

    </script>
</body>
</html>
