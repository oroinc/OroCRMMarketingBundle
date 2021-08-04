define(function(require) {
    'use strict';

    const ActivityListComponent = require('oroactivitylist/js/app/components/activity-list-component');
    const __ = require('orotranslation/js/translator');
    const MultiSelectFilter = require('oro/filter/multiselect-filter');

    const MarketingActivitiesSectionComponent = ActivityListComponent.extend({
        /** @type MultiSelectFilter */
        campaignTypeFilter: null,

        /**
         * @inheritdoc
         */
        constructor: function MarketingActivitiesSectionComponent(options) {
            MarketingActivitiesSectionComponent.__super__.constructor.call(this, options);
        },

        /**
         * Returns filter state
         *
         * @returns {{startDateRange: (*|Object), endDateRange: (*|Object)}}
         */
        getFilterState: function() {
            return {
                campaigns: this.campaignTypeFilter.getValue()
            };
        },

        isFiltersEmpty: function() {
            return this.campaignTypeFilter.isEmptyValue();
        },

        /**
         * Renders filters and binds update event
         *
         * @param $el
         */
        renderFilters: function($el) {
            const $filterContainer = $el.find('.filter-container');

            // prepare choices
            const campaignChoices = this.options.activityListOptions.campaignFilterValues;

            // create and render
            this.campaignTypeFilter = new MultiSelectFilter({
                label: __('oro.marketingactivity.widget.filter.campaign.title'),
                choices: campaignChoices || {}
            });

            this.campaignTypeFilter.render();
            this.campaignTypeFilter.on('update', this.onFilterStateChange, this);
            $filterContainer.append(this.campaignTypeFilter.$el);
            this.campaignTypeFilter.rendered();
        }
    });

    return MarketingActivitiesSectionComponent;
});
