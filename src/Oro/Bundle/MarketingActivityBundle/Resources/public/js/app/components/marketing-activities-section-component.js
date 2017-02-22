define(function(require) {
    'use strict';

    var MarketingActivitiesSectionComponent;
    var ActivityListComponent = require('oroactivitylist/js/app/components/activity-list-component');
    var __ = require('orotranslation/js/translator');
    var MultiSelectFilter = require('oro/filter/multiselect-filter');

    MarketingActivitiesSectionComponent = ActivityListComponent.extend({
        /** @type MultiSelectFilter */
        campaignTypeFilter: null,

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
            var $filterContainer = $el.find('.filter-container');

            // prepare choices
            var campaignChoices = this.options.activityListOptions.campaignFilterValues;

            // create and render
            this.campaignTypeFilter = new MultiSelectFilter({
                'label': __('oro.marketingactivity.widget.filter.campaign.title'),
                'choices': campaignChoices || {}
            });

            this.campaignTypeFilter.render();
            this.campaignTypeFilter.on('update', this.onFilterStateChange, this);
            $filterContainer.append(this.campaignTypeFilter.$el);
            this.campaignTypeFilter.rendered();
        }
    });

    return MarketingActivitiesSectionComponent;
});
