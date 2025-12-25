<?php
// admin/footer.php
// Shared footer for admin pages
?>
            </div> <!-- End of Page Content -->
        </div> <!-- End of Main Content Area -->
    </div> <!-- End of container-fluid -->

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
        function toggleSidebar() {
            const body = document.body;
            const isMobile = window.innerWidth < 992;
            
            if (isMobile) {
                body.classList.toggle('sidebar-show');
            } else {
                body.classList.toggle('sidebar-collapsed');
                // Save preference
                localStorage.setItem('admin_sidebar_collapsed', body.classList.contains('sidebar-collapsed'));
            }
        }

        // Apply saved preference on load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth >= 992) {
                const collapsed = localStorage.getItem('admin_sidebar_collapsed') === 'true';
                if (collapsed) {
                    document.body.classList.add('sidebar-collapsed');
                }
            }
        });

        function updateProduct(element, id, field) {
            let value;
            if (element.type === 'checkbox') {
                value = element.checked ? 1 : 0;
            } else if (element.tagName === 'SPAN') {
                value = element.dataset.current === '1' ? 0 : 1;
            } else {
                value = element.value;
            }

            // Visual feedback - opacity
            element.classList.add('opacity-50');

            fetch('api/update_product_quick.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&field=${field}&value=${value}`
            })
            .then(response => response.json())
            .then(data => {
                element.classList.remove('opacity-50');

                if (data.success) {
                    showToast('Updated successfully', 'success');

                    // Update badge style using Bootstrap classes
                    if (element.tagName === 'SPAN') {
                        const newValue = value;
                        element.dataset.current = newValue;
                        
                        // Default badge classes
                        element.className = 'badge rounded-pill py-2 px-3 fw-bold clickable';
                        
                        if (field === 'is_active') {
                            element.classList.add(newValue ? 'bg-success' : 'bg-secondary');
                            element.textContent = newValue ? 'Active' : 'Inactive';
                        } else {
                            const bgClass = newValue ? 'bg-danger' : 'bg-light text-dark border';
                            element.classList.add(bgClass);
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
                    showToast('Update failed: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                element.classList.remove('opacity-50');
                showToast('Error connecting to server', 'danger');
                console.error('Error:', error);
            });
        }

        function toggleStatus(element, id, field) {
            updateProduct(element, id, field);
        }

        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('statusToast');
            const toastMsg = document.getElementById('toastMessage');
            
            toastMsg.textContent = message;
            
            // Remove previous type classes
            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-dark', 'bg-info', 'bg-warning');
            
            // Add current type class
            const bgClass = type === 'success' ? 'bg-success' : (type === 'danger' || type === 'error' ? 'bg-danger' : 'bg-dark');
            toastEl.classList.add(bgClass);
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>
</body>
</html>
