define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const contactInformationFieldsCache = {};
    let $entityEl;
    let $fieldsListEl;

    const fillContactInformationFieldsCache = function(contactInformationFields) {
        _.each(contactInformationFields, function(field) {
            contactInformationFieldsCache[field.name] = field.contact_information_type;
        });
    };

    const updateContactInformationFieldsInfo = function(contactInformationFields) {
        const list = $('<ul/>');
        _.each(contactInformationFields, function(field) {
            list.append($('<li/>').html(field.label));
        });
        $fieldsListEl.html(list)
            .closest('.alert').toggleClass('has-fields', contactInformationFields.length > 0);
    };

    const updateContactInformationFields = function(contactInformationFields) {
        updateContactInformationFieldsInfo(contactInformationFields);
        fillContactInformationFieldsCache(contactInformationFields);
    };

    const loadEntityContactInformationFields = function(entity) {
        if (entity) {
            $.ajax({
                url: routing.generate('oro_api_entity_marketinglist_contact_information_fields'),
                data: {entity: entity},
                success: updateContactInformationFields
            });
        }
    };

    const contactInformationRender = function(model, element, type) {
        let icon;
        if (type) {
            if (type === 'phone') {
                icon = 'fa-phone';
            } else if (type === 'email') {
                icon = 'fa-envelope';
            }

            const item = element.find('[data-cid="' + model.cid + '"] .name-cell');

            if (!item.hasClass('has-icon') && icon) {
                item
                    .addClass('has-icon')
                    .prepend($('<span></span>', {
                        'aria-hidden': 'true',
                        'class': 'icon ' + icon
                    }));
            }
        }
    };

    const getFieldContactInformationType = function(model, element) {
        const fieldName = model.get('name');
        if (contactInformationFieldsCache.hasOwnProperty(fieldName)) {
            contactInformationRender(model, element, contactInformationFieldsCache[fieldName]);
        } else if (fieldName.indexOf(':') > -1) {
            $.ajax({
                url: routing.generate('oro_api_contact_marketinglist_information_field_type'),
                data: {
                    entity: $entityEl.select2('val'),
                    field: fieldName
                },
                success: function(type) {
                    contactInformationFieldsCache[fieldName] = type;
                    contactInformationRender(model, element, contactInformationFieldsCache[fieldName]);
                }
            });
        }
    };

    function ColumnsComponent(options) {
        const $form = $(options.formSelector);
        $entityEl = $form.find(options.entityChoiceSelector);
        $fieldsListEl = $form.find(options.fieldsChoiceSelector);

        if (!_.isEmpty(options.contactInformationFields)) {
            updateContactInformationFields(options.contactInformationFields);
        }

        $entityEl.on('change', function(e) {
            loadEntityContactInformationFields(e.val);
        });

        mediator.on(
            'items-manager:table:add:item-container items-manager:table:change:item-container',
            function(collection, model, element) {
                getFieldContactInformationType(model, element);
            }
        );
    };

    return ColumnsComponent;
});
