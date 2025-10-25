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
});

/**
 * Open inspection modal with form
 */
window.openInspectionModal = function(datetime = null) {
    const modal = window.inspectionModal;
    const $modalTitle = $('#inspectionModalLabel');
    const $formContent = $('#inspectionFormContent');
    const $formErrors = $('#inspectionFormErrors');

    // Reset state
    $formErrors.addClass('d-none').html('');
    $modalTitle.text('Nowe oględziny');

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
 * Save inspection
 */
function saveInspection() {
    const $form = $('#inspectionForm');
    const $saveBtn = $('#saveInspectionBtn');
    const $btnText = $saveBtn.find('.btn-text');
    const $btnSpinner = $saveBtn.find('.spinner-border');
    const $formErrors = $('#inspectionFormErrors');

    // Disable button and show spinner
    $saveBtn.prop('disabled', true);
    $btnText.addClass('d-none');
    $btnSpinner.removeClass('d-none');
    $formErrors.addClass('d-none').html('');

    // Get form data - use serialize() for better compatibility with Symfony forms
    const formData = $form.serialize();

    // Send AJAX request
    $.ajax({
        url: '/inspection/create',
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
