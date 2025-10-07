/**
 * Femanager Validation in vanilla JavaScript
 */
class FemanagerValidation {
    constructor(formElement) {
        this.element = formElement;
        this.requestCallback = null;
        this.submitFormAllowed = false;

        if (this.element.querySelectorAll('*[data-validation]').length === 0) {
            this.submitFormAllowed = true;
        }

        this.init();
    }

    init() {
        // Add event listeners for validation
        const validationFields = this.element.querySelectorAll('*[data-validation]:not([type="file"])');
        validationFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field, false);
            });
        });

        const fileFields = this.element.querySelectorAll('*[data-validation][type="file"]');
        fileFields.forEach(field => {
            field.addEventListener('change', () => {
                this.validateField(field, false);
            });
        });

        // Form submit event
        this.element.addEventListener('submit', (e) => {
            document.body.style.cursor = 'wait';
            if (!this.submitFormAllowed) {
                e.preventDefault();
                this.validateAllFields(this.element);
            }
        });
    }

    /**
     * AJAX queue function
     */
    createRequestsCompleted(options = {}) {
        const numRequestToComplete = options.numRequest || 0;
        let requestsCompleted = options.requestsCompleted || 0;
        const element = options.element || null;
        const callBacks = [];

        if (options.singleCallback) {
            callBacks.push(options.singleCallback);
        }

        const fireCallbacks = () => {
            document.body.style.cursor = 'default';
            this.submitForm(element); // submit form
            callBacks.forEach(callback => callback());
        };

        return {
            addCallbackToQueue: (isComplete, callback) => {
                if (isComplete) requestsCompleted++;
                if (callback) callBacks.push(callback);
                if (requestsCompleted === numRequestToComplete) fireCallbacks();
            },
            requestComplete: (isComplete) => {
                if (isComplete) requestsCompleted++;
                if (requestsCompleted === numRequestToComplete) fireCallbacks();
            },
            setCallback: (callback) => {
                callBacks.push(callback);
            }
        };
    }

    /**
     * Validate every field in form
     *
     * @param {HTMLElement} element - Form element
     * @return void
     */
    validateAllFields(element) {
        // Store number of ajax requests for queue function
        this.requestCallback = this.createRequestsCompleted({
            numRequest: element.querySelectorAll('*[data-validation]').length,
            element: element
        });

        // one loop for every field to validate
        element.querySelectorAll('*[data-validation]').forEach(field => {
            this.validateField(field, true);
        });
    }

    /**
     * Validate single field
     *
     * @param {HTMLElement} element - Field element
     * @param {boolean} countForSubmit - Whether to count this validation for form submission
     * @return void
     */
    validateField(element, countForSubmit) {
        if (element.disabled) {
            if (countForSubmit) {
                this.requestCallback.addCallbackToQueue(true);
            }
            return;
        }

        const form = element.closest('form');
        const plugin = form.dataset.femanagerPlugin;
        const pluginName = 'tx_' + form.dataset.femanagerPluginName;
        const user = form.querySelector('div:first-child input[name="' + pluginName + '[user][__identity]"]')?.value;
        const action = form.querySelector('div:first-child input[name="' + pluginName + '[__referrer][@action]"]')?.value;
        const url = Femanager.getBaseUrl() + '?id=' + document.getElementById('femanagerPid')?.value + '&type=1548935210';
        const storagePid = document.getElementById('femanagerStoragePid')?.value;
        const validation = element.getAttribute('data-validation');
        const validations = this.getValidations(element);

        let elementValue = element.value;
        if (element.type === 'checkbox' && !element.checked) {
            elementValue = '';
        }

        let additionalValue = '';
        if (this.indexOfArray(validations, 'sameAs')) { // search for "sameAs(password)"
            const validationSameAs = this.indexOfArray(validations, 'sameAs');
            const fieldToCompare = this.getStringInBrackets(validationSameAs);
            const fieldToCompareObject = document.querySelector('input[name="' + pluginName + '[user][' + fieldToCompare + ']"]');
            if (fieldToCompareObject) {
                additionalValue = fieldToCompareObject.value;
                if (fieldToCompareObject.type === 'checkbox' && !fieldToCompareObject.checked) {
                    additionalValue = '';
                }
            }
        }

        const formData = new URLSearchParams();
        formData.append('storagePid', storagePid || '');
        formData.append('L', document.getElementById('femanagerLanguage')?.value || '');
        formData.append('id', document.getElementById('femanagerPid')?.value || '');
        formData.append('tx_femanager_validation[validation]', validation || '');
        formData.append('tx_femanager_validation[value]', elementValue || '');
        formData.append('tx_femanager_validation[field]', this.getFieldName(element) || '');
        formData.append('tx_femanager_validation[user]', user !== undefined ? user : '');
        formData.append('tx_femanager_validation[additionalValue]', additionalValue || '');
        formData.append('tx_femanager_validation[plugin]', plugin || '');
        formData.append('tx_femanager_validation[pluginName]', pluginName || '');
        formData.append('tx_femanager_validation[referrerAction]', action || '');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(json => {
            if (countForSubmit) {
                this.requestCallback.addCallbackToQueue(true);
            }

            if (json) {
                try {
                    if (!json.validate) {
                        this.writeErrorMessage(element, json.message);
                    } else {
                        this.cleanErrorMessage(element);
                    }
                } catch(e) {
                    element.insertAdjacentHTML('beforebegin', data);
                }
            }
        })
        .catch(() => {
            this.logAjaxError();
        });
    }

    /**
     * Read fieldname
     *      get "email" out of "tx_femanager_plugin[user][email]"
     *      get "passwort_repeat" out of "tx_femanager_plugin[password_repeat]"
     *
     * @param {HTMLElement} element - Field element
     * @return {string} Field name
     */
    getFieldName(element) {
        let name = '';
        const nameParts = element.name.split('[');
        if (nameParts[2] !== undefined) {
            name = nameParts[2].replace(']', '');
        } else {
            name = nameParts[1].replace(']', '');
        }
        return name;
    }

    /**
     * Write errormessage next to the field
     *
     * @param {HTMLElement} element - Field element
     * @param {string} message - Error message
     */
    writeErrorMessage(element, message) {
        this.cleanErrorMessage(element); // remove all errors to this field at the beginning
        const validationContainer = document.querySelector('.femanager_validation_container');
        if (!validationContainer) return;

        const errorMessage = validationContainer.innerHTML.replace('###messages###', message); // get html for error
        element.insertAdjacentHTML('beforebegin', errorMessage); // add message
        element.closest('.form-group').classList.add('has-error');
        element.classList.add('error');
    }

    /**
     * Remove one error message
     *
     * @param {HTMLElement} element - Field element
     */
    cleanErrorMessage(element) {
        element.closest('.form-group').classList.remove('has-error');
        element.parentNode.querySelectorAll('.alert').forEach(alert => {
            if (alert !== element) {
                alert.remove();
            }
        });
        element.classList.remove('error');
    }

    /**
     * Check if there are errors and submit form
     *
     * @param {HTMLElement} element - Form element
     */
    submitForm(element) {
        // submit form if there are no errors
        if (element.querySelectorAll('.error').length === 0) {
            this.submitFormAllowed = true;
            element.submit();
        } else {
            const firstError = element.querySelector('.error');
            if (!firstError) return;
            firstError.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Check if part of a value exist in an array
     *
     * @param {Array} array - Array to search in
     * @param {string} string - String to search for
     * @return {string} Found value or empty string
     */
    indexOfArray(array, string) {
        for (let i = 0; i < array.length; i++) {
            if (array[i].indexOf(string) !== -1) {
                return array[i];
            }
        }
        return '';
    }

    /**
     * Get validation methods of a field
     *      data-validation="required,max(5)" => array
     *          required,
     *          max(5)
     *
     * @param {HTMLElement} element - Field element
     * @return {Array} Array of validation methods
     */
    getValidations(element) {
        return element.getAttribute('data-validation').split(',');
    }

    /**
     * Get string in brackets
     *      lala(lulu) => lulu
     *
     * @param {string} string - String with brackets
     * @return {string} String inside brackets
     */
    getStringInBrackets(string) {
        let result = '';
        if (string.indexOf('(') !== -1) {
            const parts = string.split('(');
            result = parts[1].substr(0, parts[1].length - 1);
        }
        return result;
    }

    /**
     * Log Error in Console
     *
     * @return void
     */
    logAjaxError() {
        if (typeof console === 'object') {
            console.log('Error: The called url is not available - if you use TYPO3 in a subfolder, please use config.baseURL in TypoScript');
        }
    }
}

// Initialize validation for all forms with class feManagerValidation
function initFemanagerValidation() {
    document.querySelectorAll('.feManagerValidation').forEach(form => {
        new FemanagerValidation(form);
    });
}
