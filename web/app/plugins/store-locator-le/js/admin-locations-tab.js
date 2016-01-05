// Setup the Location Manager namespace
var SLP_Location_Manager = SLP_Location_Manager || {};

/**
 * Table Header Class
 */
var SLP_Locations_table_header = function () {

    /**
     * Hide Column On Drag Stop
     */
    this.hide_column = function ( event , ui ) {
        var post_data = new Object();
        post_data['action']     = 'slp_hide_column';
        post_data['user_id']    = location_manager.user_id;
        post_data['data_field'] = ui.draggable.attr('data-field');

        var data_fld_selector = '[data-field="' + post_data['data_field'] + '"]';
        var column = SLP_Location_Manager.table.column( data_fld_selector  );
        column.visible( ! column.visible() );
        jQuery('th' + data_fld_selector ).hide();

        jQuery.post( ajaxurl, post_data , this.process_hide_column_reponse );
    }

    /**
     * Proces the hide column response.
     */
    this.process_hide_column_response = function ( response ) {
    }

    /**
     * Unhide Column On Click
     */
    this.unhide_column = function ( event ) {
        var post_data = new Object();
        post_data['action']     = 'slp_unhide_column';
        post_data['user_id']    = location_manager.user_id;
        post_data['data_field'] = jQuery(this).attr('data-field');

        var data_fld_selector = '[data-field="' + post_data['data_field'] + '"]';
        jQuery('th' + data_fld_selector ).show();
        jQuery('td' + data_fld_selector ).show();
        jQuery('span.unhider' + data_fld_selector ).hide();

        jQuery.post( ajaxurl, post_data , this.process_hide_column_reponse );

    }

}

/**
 * Locations Tab Admin JS
 */
jQuery(document).ready(
    function() {
        SLP_Location_Manager.table_header = new SLP_Locations_table_header();

        var dataTable_options = new Object();
        dataTable_options['stripeClasses'] = [];
        dataTable_options['paging'] = false;
        dataTable_options['searching'] = false;

        // Options: Full Data Set Displayed
        if (location_manager.all_displayed) {
            dataTable_options['ordering'] = true;

            // Options: Partial Data Set Displayed
        } else {
            dataTable_options['ordering'] = false;
        }

        // Manage Locations Table : DataTable Handler
        //
        SLP_Location_Manager.table = jQuery('#manage_locations_table').DataTable(dataTable_options);

        // Droppable Divs
        //
        var droppable_options = new Object();
        droppable_options['activeClass'] = 'drop_activated';
        droppable_options['tolerance'  ] = 'pointer';
        droppable_options['drop'       ]   = SLP_Location_Manager.table_header.hide_column;
        jQuery('#column_hider').droppable( droppable_options );

        // Draggable Headers
        //
        var draggable_options = new Object();
        draggable_options['revert'] = 'invalid';
        jQuery('th.manage-column').draggable( draggable_options );

        // Unhider links
        //
        jQuery('span.unhider').on( 'click' , SLP_Location_Manager.table_header.unhide_column );

        // No locations, show add form.
        //
        if ( jQuery('#wpcsl-option-current_locations div.section_description').is(':empty') ) {
            jQuery('#wpcsl-option-add_location_sidemenu').click();
        }
    }
);
