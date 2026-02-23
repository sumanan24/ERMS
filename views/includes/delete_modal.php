<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage">Are you sure you want to delete this item?</p>
                <p class="text-muted" style="font-size: 14px; margin-top: 10px;">
                    <i class="bi bi-info-circle"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Delete Confirmation Modal Handler
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('deleteConfirmModal');
    if (!modalElement) return;
    
    const deleteModal = new bootstrap.Modal(modalElement);
    const deleteMessage = document.getElementById('deleteConfirmMessage');
    const deleteBtn = document.getElementById('deleteConfirmBtn');
    
    // Handle all delete links with data-delete attribute
    document.querySelectorAll('[data-delete]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const deleteUrl = this.getAttribute('href');
            const deleteText = this.getAttribute('data-delete') || 'this item';
            const itemType = this.getAttribute('data-item-type') || '';
            const itemId = this.getAttribute('data-item-id') || '';
            
            // Check dependencies for courses and versions
            if (itemType === 'course' && itemId) {
                checkCourseDependencies(itemId, deleteUrl, deleteText, deleteModal, deleteMessage, deleteBtn);
            } else if (itemType === 'version' && itemId) {
                checkVersionDependencies(itemId, deleteUrl, deleteText, deleteModal, deleteMessage, deleteBtn);
            } else {
                // For other items (users, modules), show modal directly
                deleteMessage.innerHTML = 'Are you sure you want to delete ' + deleteText + '?<br><br><strong>This action cannot be undone.</strong>';
                deleteBtn.setAttribute('href', deleteUrl);
                deleteBtn.style.display = 'inline-block';
                deleteModal.show();
            }
        });
    });
    
    // Check course dependencies
    function checkCourseDependencies(courseId, deleteUrl, deleteText, modal, messageEl, btnEl) {
        fetch('index.php?action=courses&sub=checkDependencies&id=' + courseId)
            .then(response => response.json())
            .then(data => {
                if (data.hasModules) {
                    messageEl.innerHTML = 'Cannot delete course.<br><br>This course has <strong>modules</strong> allocated to it.<br>Please delete all modules first before deleting the course.';
                    btnEl.style.display = 'none';
                    modal.show();
                    return;
                }
                if (data.hasVersions) {
                    messageEl.innerHTML = 'Cannot delete course.<br><br>This course has <strong>versions</strong> allocated to it.<br>Please delete all versions first before deleting the course.';
                    btnEl.style.display = 'none';
                    modal.show();
                    return;
                }
                // No dependencies, show confirmation modal
                messageEl.innerHTML = 'Are you sure you want to delete ' + deleteText + '?<br><br><strong>This action cannot be undone.</strong>';
                btnEl.setAttribute('href', deleteUrl);
                btnEl.style.display = 'inline-block';
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback: show modal anyway
                messageEl.innerHTML = 'Are you sure you want to delete ' + deleteText + '?<br><br><strong>This action cannot be undone.</strong>';
                btnEl.setAttribute('href', deleteUrl);
                btnEl.style.display = 'inline-block';
                modal.show();
            });
    }
    
    // Check version dependencies
    function checkVersionDependencies(versionId, deleteUrl, deleteText, modal, messageEl, btnEl) {
        fetch('index.php?action=versions&sub=checkDependencies&id=' + versionId)
            .then(response => response.json())
            .then(data => {
                if (data.hasModules) {
                    messageEl.innerHTML = 'Cannot delete version.<br><br>This version has <strong>modules</strong> allocated to it.<br>Please delete all modules first before deleting the version.';
                    btnEl.style.display = 'none';
                    modal.show();
                    return;
                }
                // No dependencies, show confirmation modal
                messageEl.innerHTML = 'Are you sure you want to delete ' + deleteText + '?<br><br><strong>This action cannot be undone.</strong>';
                btnEl.setAttribute('href', deleteUrl);
                btnEl.style.display = 'inline-block';
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback: show modal anyway
                messageEl.innerHTML = 'Are you sure you want to delete ' + deleteText + '?<br><br><strong>This action cannot be undone.</strong>';
                btnEl.setAttribute('href', deleteUrl);
                btnEl.style.display = 'inline-block';
                modal.show();
            });
    }
    
    // Clear href when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        deleteBtn.setAttribute('href', '#');
        deleteBtn.style.display = 'inline-block';
    });
});
</script>

<style>
#deleteConfirmModal .modal-header {
    border-bottom: none;
}

#deleteConfirmModal .modal-footer {
    border-top: 1px solid #dee2e6;
}

#deleteConfirmModal .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

#deleteConfirmModal .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>
