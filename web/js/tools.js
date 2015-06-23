$.widget('custom.tableAutocomplete', $.ui.autocomplete, {
    options: {
        open: function(event, ui) {
            // Hack to prevent a 'menufocus' error when doing sequential searches using only the keyboard
            $('.ui-autocomplete .ui-menu-item:first').trigger('mouseover');
        },
        focus: function(event, ui) {
            event.preventDefault();
        }
    },
    _create: function() {
        this._super();
        // Using a table makes the autocomplete forget how to menu.
        // With this we can skip the header row and navigate again via keyboard.
        this.widget().menu('option', 'items', '.ui-menu-item');
    },
    _renderMenu: function(ul, items) {
        var self       = this,
            $table     = $('<table class="table-autocomplete">'),
            $thead     = $('<thead>'),
            $headerRow = $('<tr>'),
            $tbody     = $('<tbody>');

        $.each(self.options.columns, function(index, columnMapping) {
            $('<th>').text(columnMapping.title).appendTo($headerRow);
        });

        $thead.append($headerRow);
        $table.append($thead);
        $table.append($tbody);

        ul.html($table);

        $.each(items, function(index, item) {
            self._renderItemData(ul, ul.find('table tbody'), item);
        });
    },
    _renderItemData: function(ul, table, item) {
        return this._renderItem(table, item).data('ui-autocomplete-item', item);
    },
    _renderItem: function(table, item) {
        var self = this,
            $tr  = $('<tr class="ui-menu-item" role="presentation">');

        $.each(self.options.columns, function(index, columnMapping) {
            var cellContent = !item[columnMapping.field] ? '' : item[columnMapping.field];
            $('<td>').text(cellContent).appendTo($tr);
        });

        return $tr.appendTo(table);
    }
});
