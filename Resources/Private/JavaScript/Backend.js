import RegularEvent from '@typo3/core/event/regular-event.js';
import Notification from '@typo3/backend/notification.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';
import DateTimePicker from '@typo3/backend/date-time-picker.js';
import '@typo3/backend/input/clearable.js'; // Der reine Import reicht völlig aus!

function initializeDateTimePickerElements() {
    const dateTimePickerElements = document.querySelectorAll('.t3js-datetimepicker');
    dateTimePickerElements.forEach(e => DateTimePicker.initialize(e));
}

initializeDateTimePickerElements();

new RegularEvent('click', (event, target) => {
    event.preventDefault();
    const username = target.closest('tr').querySelector('.col-title').textContent.trim();
    fetch(target.getAttribute('href')).then(() => {
        Notification.success('Logout', 'User "' + username + '" has been logged out successfully');
        location.reload();
    });
}).delegateTo(document, '.logoutUser');

new RegularEvent('click', (event, target) => {
    event.preventDefault();
    const username = target.closest('tr').querySelector('.col-title').textContent.trim();
    const modal = Modal.confirm(
        target.dataset.title,
        target.dataset.content,
        Severity.error
    );
    modal.addEventListener('button.clicked', (e) => {
        if (e.target.getAttribute('name') === 'ok') {
            fetch(target.getAttribute('href')).then(() => {
                Notification.success('Delete', 'User "' + username + '" has been deleted successfully');
                location.reload();
            });
        }
        Modal.dismiss();
    });
}).delegateTo(document, '.deleteUser');
