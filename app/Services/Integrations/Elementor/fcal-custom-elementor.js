document.addEventListener('DOMContentLoaded', function() {
    elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
        var controlContainer = document.querySelector('.elementor-control-selected_cal_id select');

        function fetchEvents(calId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    var response = JSON.parse(xhr.responseText);
                    var options = response.data;
                    var eventControl = document.querySelector('.elementor-control-selected_event_ids select');

                    if (eventControl) {
                        // Clear existing options
                        eventControl.innerHTML = '';

                        // Add new options
                        for (var key in options) {
                            if (options.hasOwnProperty(key)) {
                                var option = document.createElement('option');
                                option.value = key;
                                option.text = options[key];
                                eventControl.appendChild(option);
                            }
                        }

                        // Trigger change event
                        // var event = new Event('change');
                        // eventControl.dispatchEvent(event);
                    }
                } else {
                    console.error('Error fetching events: ', xhr);
                }
            };

            xhr.onerror = function() {
                console.error('Error fetching events: ', xhr);
            };

            xhr.send('action=get_calendar_events&cal_id=' + encodeURIComponent(calId));
        }

        if (controlContainer) {
            var selectedCalId = controlContainer.value;
            fetchEvents(selectedCalId);
            controlContainer.addEventListener('change', function() {
                fetchEvents(this.value);
            });
        } else {
            console.log('Control container not found ' + controlContainer);
        }

    });
});
