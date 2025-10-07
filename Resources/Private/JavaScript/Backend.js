document.addEventListener("DOMContentLoaded", () => {
    const hideUnhideUser = (event) => {
        const currentElement = event.currentTarget;
        const moduleUri = currentElement.parentElement.querySelector('.container_module_uri').value;
        const uid = currentElement.parentElement.querySelector('.container_uid').value;
        const table = currentElement.parentElement.querySelector('.container_table').value;

        let hidden = 0;

        if (currentElement.classList.contains('hideUser')) {
            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-status-status-permission-granted')
                .style.display = "none";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-status-user-frontend-disabled')
                .style.display = "block";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-edit-hide')
                .style.display = "none";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-edit-unhide')
                .style.display = "block";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch')
                .style.display = "none";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled')
                .style.display = "none";

            hidden = 1;
        } else {
            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-status-status-permission-granted')
                .style.display = "block";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-status-user-frontend-disabled')
                .style.display = "none";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-edit-hide')
                .style.display = "block";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-edit-unhide')
                .style.display = "none";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch')
                .style.display = "block";

            currentElement
                .closest('tr')
                .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled')
                .style.display = "block";
        }

        const url = moduleUri + '&data[' + table + '][' + uid + '][disable]=' + hidden;
        fetch(url);
    };

    const deleteUser = (event) => {
        const currentElement = event.currentTarget;
        const moduleUri = currentElement.parentElement.querySelector('.container_module_uri').value;
        const uid = currentElement.parentElement.querySelector('.container_uid').value;
        const table = currentElement.parentElement.querySelector('.container_table').value;
        const confirmationMessage = currentElement.parentElement.querySelector('.container_label_delete_confirmation').value;
        const url = moduleUri + '&cmd[' + table + '][' + uid + '][delete]=1';

        if (confirm(confirmationMessage)) {
            currentElement.closest('tr').remove();
            fetch(url);
        }
    };

    const logoutUser = (event) => {
        event.preventDefault();

        const currentElement = event.currentTarget;
        const url = currentElement.getAttribute('href');

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-status-status-permission-granted')
            .style.display = "none";

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-status-status-permission-denied')
            .style.display = "block";

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled')
            .style.display = "none";

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-actions-system-backend-user-switch')
            .style.display = "block";

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-apps-pagetree-drag-place-denied')
            .style.display = "none";

        currentElement
            .closest('tr')
            .querySelector('.tx-feusermanager-icon-apps-pagetree-drag-place-denied-disabled')
            .style.display = "block";

        fetch(url);
    };

    document.addEventListener('click', (event) => {
        if (event.target.matches('.hideUser, .unhideUser')) {
            hideUnhideUser(event)
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target.matches('.deleteUser')) {
            deleteUser(event)
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target.matches('.logoutUser')) {
            logoutUser(event)
        }
    });
});
