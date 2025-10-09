document.addEventListener('DOMContentLoaded', function() {
    var labels;

    if (document.querySelector(".tx-femanager[data-labels]") != null) {
        labels = JSON.parse(document.querySelector('.tx-femanager[data-labels]').dataset['labels']);
    } else {
        labels = [];
    }

    // Initialize form validation for all forms with class feManagerValidation
    initFemanagerValidation();

    // Confirmation
    document.querySelectorAll('*[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            var message = element.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    function lcfirst(str) {
        //  discuss at: https://locutus.io/php/lcfirst/
        // original by: Brett Zamir (https://brett-zamir.me)
        //   example 1: lcfirst('Kevin Van Zonneveld')
        //   returns 1: 'kevin Van Zonneveld'

        str += '';
        var f = str.charAt(0).toLowerCase();
        return f + str.substr(1);
    }

    // Data fields
    document.querySelectorAll('[data-data-endpoint]').forEach(function(nodeElement) {
        var triggerCallback = function(event) {
            var endpoint = nodeElement.dataset['dataEndpoint'];

            var data = {
                'tx_femanager_data[action]': endpoint,
                'tx_femanager_data[controller]': 'Data',
            };

            // Convert DOMStringMap to object. https://stackoverflow.com/a/48235245/2025722
            // According to Mozilla this should work all the way back to IE 8 without a polyfill.
            var arguments = JSON.parse(JSON.stringify(nodeElement.dataset));
            for (var argument in arguments) {
                if (arguments.hasOwnProperty(argument)) {
                    if (argument.match('arguments')) {
                        var sourceField = document.getElementById(arguments[argument]);
                        if (sourceField) {
                            var argumentName = lcfirst(argument.substr(9));
                            data['tx_femanager_data[' + argumentName + ']'] = sourceField.value;
                        }
                    }
                }
            }

            var femanagerPid = document.getElementById('femanagerPid');
            var url = Femanager.getBaseUrl() + 'index.php?id=' + (femanagerPid ? femanagerPid.value : '') + '&type=1594138042';

            var selectedValue = nodeElement.dataset['selectedValue'];

            // Use fetch API instead of jQuery AJAX
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data),
                cache: 'no-cache'
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function(options) {
                nodeElement.disabled = true;
                nodeElement.options.length = 0;
                if (selectedValue !== '' && !options.hasOwnProperty(selectedValue)) {
                    nodeElement.options[nodeElement.options.length] = new Option(labels.please_choose);
                }
                for (var option in options) {
                    if (options.hasOwnProperty(option)) {
                        var isSelected = option === selectedValue;
                        nodeElement.options[nodeElement.options.length] = new Option(options[option], option, isSelected, isSelected);
                        nodeElement.disabled = false;
                    }
                }
            })
            .catch(function(error) {
                console.log('Error: The called url is not available - if you use TYPO3 in a subfolder, please use config.baseURL in TypoScript');
            });

            // Before sending request
            nodeElement.disabled = true;
            nodeElement.options.length = 0;
            nodeElement.options[0] = new Option(labels.loading_states);
        };

        var triggerFieldsString = nodeElement.dataset['triggerFields'];
        if (undefined !== triggerFieldsString) {
            triggerFieldsString.split(',').forEach(function(element) {
                var triggerElement = document.getElementById(element);
                if (undefined !== triggerElement) {
                    triggerElement.addEventListener('change', triggerCallback);
                }
            });
        } else {
            nodeElement.addEventListener('change', triggerCallback);
        }
        triggerCallback();
    });
});

window.Femanager = {};

/**
 * Return BaseUrl as prefix
 *
 * @return string Base Url
 */
window.Femanager.getBaseUrl = function() {
    var baseurl;
    var baseElement = document.querySelector('base');

    if (baseElement) {
        baseurl = baseElement.href;
    } else if (window.location.hostname.indexOf('localhost') !== -1) {
        baseurl = '';
    } else {
        var port = '';
        if (window.location.port.length > 0) {
            port = ':' + window.location.port;
        }
        if (window.location.protocol !== "https:") {
            baseurl = 'http://' + window.location.hostname + port + '/';
        } else {
            baseurl = 'https://' + window.location.hostname + port + '/';
        }
    }
    return baseurl;
};
