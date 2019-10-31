define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');

    return function(options) {
        const $schedule = options._sourceElement.find(options.scheduleEl);
        const $scheduledFor = options._sourceElement.find(options.scheduledForEl);
        const $transportEl = options._sourceElement.find(options.transportEl);
        const $label = $scheduledFor.find('label');
        const hideOn = options.hideOn || [];
        const showOn = options.showOn || [];

        $transportEl.on('change', function() {
            mediator.execute('showLoading');

            const $form = $transportEl.closest('form');
            const data = $form.serializeArray();
            const url = $form.attr('action');
            data.push({name: 'formUpdateMarker', value: 1});

            const event = {formEl: $form, data: data, reloadManually: true};
            mediator.trigger('integrationFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
            }
        });

        $schedule.on('change', function() {
            if (_.contains(hideOn, $(this).val())) {
                $scheduledFor.addClass('hide');
                $scheduledFor.find('input').each(function() {
                    $(this).rules('remove', 'NotBlank');
                });

                if ($label.hasClass('required')) {
                    $label
                        .removeClass('required')
                        .find('em').html('&nbsp;');
                }
            }
            if (_.contains(showOn, $(this).val())) {
                $scheduledFor.removeClass('hide');

                $scheduledFor.find('input').each(function() {
                    $(this)
                        .removeClass('hide')
                        .rules('add', 'NotBlank');
                });

                if (!$label.hasClass('required')) {
                    $label
                        .addClass('required')
                        .find('em').html('*');
                }
            }
        });
    };
});
