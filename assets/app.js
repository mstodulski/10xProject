/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Import Bootstrap
import * as bootstrap from 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// Import FullCalendar
import './calendar.js';

import $ from 'jquery';

// Make jQuery and Bootstrap globally available
window.$ = window.jQuery = $;
window.bootstrap = bootstrap;

$( document ).ready(function()
{
    // Initialize Bootstrap modals
    const inspectionModal = new bootstrap.Modal(document.getElementById('inspectionModal'));

    // Make modal globally available for calendar.js
    window.inspectionModal = inspectionModal;

    $('#addInspectionBtn').on('click', function () {
        openInspectionModal();
    });

    // Handle form submission
    $('#inspectionForm').on('submit', function(e) {
        e.preventDefault();
        saveInspection();
    });

    // Handle delete button click
    $(document).on('click', '#deleteInspectionBtn', function(e) {
        e.preventDefault();
        deleteInspection();
    });
});

/**
 * Open inspection modal for creating new inspection
 */
window.openInspectionModal = function(datetime = null) {
    const modal = window.inspectionModal;
    const $modal = $('#inspectionModal');
    const $modalTitle = $('#inspectionModalLabel');
    const $formContent = $('#inspectionFormContent');
    const $formErrors = $('#inspectionFormErrors');
    const $form = $('#inspectionForm');
    const $saveBtn = $('#saveInspectionBtn');
    const $deleteBtn = $('#deleteInspectionBtn');

    // Set mode to create
    $modal.attr('data-mode', 'create');
    $modal.attr('data-inspection-id', '');

    // Reset state
    $formErrors.addClass('d-none').html('');
    $modalTitle.text('Nowe oględziny');
    $form.attr('action', '/inspection/create');

    // Update button text
    $saveBtn.find('.btn-text').text('Utwórz oględziny');
    $saveBtn.show();

    // Hide delete button
    $deleteBtn.hide();

    // Show loading spinner
    $formContent.html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Ładowanie...</span>
            </div>
        </div>
    `);

    // Open modal
    modal.show();

    // Load form via AJAX
    const url = datetime
        ? `/inspection/form?datetime=${encodeURIComponent(datetime)}`
        : '/inspection/form';

    $.ajax({
        url: url,
        method: 'GET',
        success: function(html) {
            $formContent.html(html);
        },
        error: function() {
            $formContent.html(`
                <div class="alert alert-danger">
                    Wystąpił błąd podczas ładowania formularza.
                </div>
            `);
        }
    });
};

/**
 * Open inspection modal for editing/viewing existing inspection
 */
window.openInspectionEditModal = function(inspectionId) {
    const modal = window.inspectionModal;
    const $modal = $('#inspectionModal');
    const $modalTitle = $('#inspectionModalLabel');
    const $formContent = $('#inspectionFormContent');
    const $formErrors = $('#inspectionFormErrors');
    const $form = $('#inspectionForm');
    const $saveBtn = $('#saveInspectionBtn');
    const $deleteBtn = $('#deleteInspectionBtn');

    // Set mode to edit (will be updated based on response)
    $modal.attr('data-mode', 'edit');
    $modal.attr('data-inspection-id', inspectionId);

    // Reset state
    $formErrors.addClass('d-none').html('');
    $modalTitle.text('Oględziny');
    $form.attr('action', `/inspection/${inspectionId}/update`);

    // Show loading spinner
    $formContent.html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Ładowanie...</span>
            </div>
        </div>
    `);

    // Open modal
    modal.show();

    // Load form via AJAX
    $.ajax({
        url: `/inspection/${inspectionId}`,
        method: 'GET',
        success: function(html) {
            $formContent.html(html);

            // Read mode information from form data attributes
            const $formData = $('#inspectionFormData');
            const isReadonly = $formData.attr('data-readonly') === '1';
            const canDelete = $formData.attr('data-can-delete') === '1';

            if (isReadonly) {
                // View/readonly mode
                $modal.attr('data-mode', 'view');
                $saveBtn.hide();
                $deleteBtn.hide();
            } else {
                // Edit mode
                $modal.attr('data-mode', 'edit');
                $saveBtn.find('.btn-text').text('Zapisz zmiany');
                $saveBtn.show();

                // Show delete button only if user can delete
                if (canDelete) {
                    $deleteBtn.show();
                } else {
                    $deleteBtn.hide();
                }
            }
        },
        error: function() {
            $formContent.html(`
                <div class="alert alert-danger">
                    Wystąpił błąd podczas ładowania formularza.
                </div>
            `);
        }
    });
};

/**
 * Save inspection (create or update)
 */
function saveInspection() {
    const $form = $('#inspectionForm');
    const $modal = $('#inspectionModal');
    const $saveBtn = $('#saveInspectionBtn');
    const $btnText = $saveBtn.find('.btn-text');
    const $btnSpinner = $saveBtn.find('.spinner-border');
    const $formErrors = $('#inspectionFormErrors');

    // Get mode and URL
    const mode = $modal.attr('data-mode');
    const url = $form.attr('action');

    // Disable button and show spinner
    $saveBtn.prop('disabled', true);
    $btnText.addClass('d-none');
    $btnSpinner.removeClass('d-none');
    $formErrors.addClass('d-none').html('');

    // Get form data - use serialize() for better compatibility with Symfony forms
    const formData = $form.serialize();

    // Send AJAX request
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Close modal
                window.inspectionModal.hide();

                // Show success toast
                showToast(response.message);

                // Refresh calendar
                if (window.calendar) {
                    window.calendar.refetchEvents();
                }
            } else {
                // Show errors
                displayFormErrors(response.errors || [response.message]);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Wystąpił błąd podczas zapisywania oględzin.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            const errors = xhr.responseJSON?.errors || [errorMessage];
            displayFormErrors(errors);
        },
        complete: function() {
            // Re-enable button and hide spinner
            $saveBtn.prop('disabled', false);
            $btnText.removeClass('d-none');
            $btnSpinner.addClass('d-none');
        }
    });
}

/**
 * Delete inspection with confirmation
 */
function deleteInspection() {
    const $modal = $('#inspectionModal');
    const inspectionId = $modal.attr('data-inspection-id');

    if (!inspectionId) {
        alert('Nie można usunąć oględzin - brak ID.');
        return;
    }

    // Show confirmation dialog
    if (!confirm('Czy na pewno chcesz usunąć te oględziny? Ta operacja jest nieodwracalna.')) {
        return;
    }

    const $deleteBtn = $('#deleteInspectionBtn');
    const $btnText = $deleteBtn.find('.btn-text');
    const $btnSpinner = $deleteBtn.find('.spinner-border');

    // Disable button and show spinner
    $deleteBtn.prop('disabled', true);
    $btnText.addClass('d-none');
    $btnSpinner.removeClass('d-none');

    // Send DELETE request
    $.ajax({
        url: `/inspection/${inspectionId}/delete`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                // Close modal
                window.inspectionModal.hide();

                // Show success toast
                showToast(response.message);

                // Refresh calendar
                if (window.calendar) {
                    window.calendar.refetchEvents();
                }
            } else {
                alert(response.message || 'Wystąpił błąd podczas usuwania oględzin.');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Wystąpił błąd podczas usuwania oględzin.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            alert(errorMessage);
        },
        complete: function() {
            // Re-enable button and hide spinner
            $deleteBtn.prop('disabled', false);
            $btnText.removeClass('d-none');
            $btnSpinner.addClass('d-none');
        }
    });
}

/**
 * Display form errors
 */
function displayFormErrors(errors) {
    const $formErrors = $('#inspectionFormErrors');

    if (!Array.isArray(errors)) {
        errors = [errors];
    }

    const errorHtml = errors.map(error => `<li>${error}</li>`).join('');

    $formErrors.html(`
        <ul class="mb-0">
            ${errorHtml}
        </ul>
    `).removeClass('d-none');
}

/**
 * Show success toast
 */
function showToast(message) {
    const $toast = $('#inspectionToast');
    const $toastBody = $('#inspectionToastBody');

    $toastBody.text(message);

    const toast = new bootstrap.Toast($toast[0]);
    toast.show();
}
