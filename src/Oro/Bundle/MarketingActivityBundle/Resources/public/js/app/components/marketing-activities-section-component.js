define(function(require) {
    'use strict';

    var MarketingActivitiesSectionComponent;
    var ActivityListComponent = require('oroactivitylist/js/app/components/activity-list-component');
    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var MultiSelectFilter = require('oro/filter/multiselect-filter');
    var DatetimeFilter = require('oro/filter/datetime-filter');
    var dataFilterWrapper = require('orofilter/js/datafilter-wrapper');

    MarketingActivitiesSectionComponent = ActivityListComponent.extend({
        /** @type DatetimeFilter */
        startDateRangeFilter: null,

        /** @type DatetimeFilter */
        endDateRangeFilter: null,

        /** @type MultiSelectFilter */
        campaignTypeFilter: null,

        /**
         * Returns filter state
         *
         * @returns {{startDateRange: (*|Object), endDateRange: (*|Object)}}
         */
        getFilterState: function() {
            return {
                startDateRange: this.startDateRangeFilter.getValue(),
                endDateRange: this.endDateRangeFilter.getValue(),
                campaigns: this.campaignTypeFilter.getValue()
            };
        },

        isFiltersEmpty: function() {
            return (
                this.startDateRangeFilter.isEmptyValue()
                && this.endDateRangeFilter.isEmptyValue()
                && this.campaignTypeFilter.isEmptyValue()
            );
        },

        /**
         * Renders filters and binds update event
         *
         * @param $el
         */
        renderFilters: function($el) {
            var DateRangeFilterWithMeta;
            var $filterContainer = $el.find('.filter-container');

            // create instance
            DateRangeFilterWithMeta = DatetimeFilter.extend(this.options.activityListOptions.dateRangeFilterMetadata);
            this.startDateRangeFilter = new DateRangeFilterWithMeta({
                'label': __('oro.marketingactivity.widget.filter.start_date_picker.title')
            });
            // tell that it should be rendered with dropdown
            _.extend(this.startDateRangeFilter, dataFilterWrapper);
            // render
            this.startDateRangeFilter.render();
            this.startDateRangeFilter.on('update', this.onFilterStateChange, this);
            $filterContainer.append(this.startDateRangeFilter.$el);
            this.startDateRangeFilter.rendered();

            this.endDateRangeFilter = new DateRangeFilterWithMeta({
                'label': __('oro.marketingactivity.widget.filter.end_date_picker.title')
            });
            // tell that it should be rendered with dropdown
            _.extend(this.endDateRangeFilter, dataFilterWrapper);
            // render
            this.endDateRangeFilter.render();
            this.endDateRangeFilter.on('update', this.onFilterStateChange, this);
            $filterContainer.append(this.endDateRangeFilter.$el);
            this.endDateRangeFilter.rendered();

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
