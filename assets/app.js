/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Import Bootstrap
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// Import FullCalendar
import './calendar.js';

import $ from 'jquery';

$( document ).ready(function()
{
    $('#addInspectionBtn').on('click', function () {
        // TODO: Implementacja modala do dodawania oględzin
        alert('Tutaj otworzy się formularz dodawania nowych oględzin');
    });
});
