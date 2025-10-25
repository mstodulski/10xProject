/**
 * FullCalendar Configuration
 * System Zarządzania Oględzinami Pojazdów Powypadkowych
 *
 * Features according to PRD:
 * - Weekly view (Mon-Sun) on desktop (≥768px)
 * - Daily view on mobile (<768px)
 * - Working hours: 07:00-16:00
 * - Time slots: every 15 minutes
 * - Weekends blocked (Saturday, Sunday)
 * - Auto-refresh every minute
 */

import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';
import plLocale from '@fullcalendar/core/locales/pl';

// Note: FullCalendar v6 automatically injects CSS - no CSS imports needed!

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) {
        return; // Calendar element not found on this page
    }

    // Determine initial view based on screen width
    const isMobile = window.innerWidth < 768;
    const initialView = isMobile ? 'timeGridDay' : 'timeGridWeek';

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin, bootstrap5Plugin],
        themeSystem: 'bootstrap5',

        // Initial view
        initialView: initialView,

        // Header toolbar
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },

        // Week starts on Monday
        firstDay: 1,

        // Locale (Polish)
        locale: plLocale,
        locales: [plLocale],

        // Time settings
        slotMinTime: '07:00:00',  // Start at 07:00
        slotMaxTime: '16:00:00',  // End at 16:00
        slotDuration: '00:15:00', // 15-minute slots
        slotLabelInterval: '01:00', // Show labels every hour

        // Height settings
        height: 'auto',
        contentHeight: 'auto',

        // Hide weekends
        weekends: false,

        // Enable time slot selection
        selectable: true,
        selectMirror: true,

        // Event settings
        editable: true,
        eventStartEditable: true,
        eventDurationEditable: false,

        // All day slot hidden (we only do timed events)
        allDaySlot: false,

        // Date and time format
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        // Business hours (07:00-16:00, Mon-Fri)
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
            startTime: '07:00',
            endTime: '16:00'
        },

        // Callbacks
        select: function(info) {
            handleDateSelect(info);
        },

        eventClick: function(info) {
            handleEventClick(info);
        },

        // Load events from server
        events: '/inspections',

        // Responsive: change view on window resize
        windowResize: function(view) {
            const isMobile = window.innerWidth < 768;
            const newView = isMobile ? 'timeGridDay' : 'timeGridWeek';

            if (calendar.view.type !== newView) {
                calendar.changeView(newView);
            }
        }
    });

    calendar.render();

    // Make calendar globally available
    window.calendar = calendar;

    // Auto-refresh every minute (as per PRD requirement)
    setInterval(function() {
        calendar.refetchEvents();
    }, 60000); // 60000ms = 1 minute
});

/**
 * Handle time slot selection (create new inspection)
 */
function handleDateSelect(info) {
    // Validate: only future dates
    const now = new Date();
    if (info.start < now) {
        alert('Nie można utworzyć oględzin w przeszłości');
        return;
    }

    // Validate: max 2 weeks ahead
    const twoWeeksAhead = new Date();
    twoWeeksAhead.setDate(twoWeeksAhead.getDate() + 14);
    if (info.start > twoWeeksAhead) {
        alert('Nie można rezerwować terminów dalej niż 2 tygodnie do przodu');
        return;
    }

    // Validate: only working hours and weekdays
    const hour = info.start.getHours();
    const dayOfWeek = info.start.getDay();

    if (dayOfWeek === 0 || dayOfWeek === 6) {
        alert('Nie można umówić oględzin w weekend');
        return;
    }

    if (hour < 7 || hour >= 16) {
        alert('Oględziny można umówić tylko w godzinach 07:00-16:00');
        return;
    }

    // Open modal with selected datetime
    const datetimeString = info.start.toISOString();
    if (window.openInspectionModal) {
        window.openInspectionModal(datetimeString);
    } else {
        console.error('openInspectionModal function not found');
    }
}

/**
 * Handle event click (view/edit inspection)
 */
function handleEventClick(info) {
    // For now, show alert with event details
    const event = info.event;
    // TODO: Replace with modal - edit inspection
    alert(`Szczegóły oględzin:\nTytuł: ${event.title}\nData: ${event.start.toLocaleDateString('pl-PL')}\nGodzina: ${event.start.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })}`);
}
