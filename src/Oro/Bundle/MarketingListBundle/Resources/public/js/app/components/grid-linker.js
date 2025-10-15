import _ from 'underscore';
import mediator from 'oroui/js/mediator';

export default function(options) {
    _.each(options, function(grids) {
        mediator.on('datagrid:afterRemoveRow:' + grids.main, function() {
            mediator.trigger('datagrid:doRefresh:' + grids.secondary);
        });
    });
};
