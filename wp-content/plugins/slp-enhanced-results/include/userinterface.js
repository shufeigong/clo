/**
 * A base class that helps add-on packs separate ui functionality.
 *
 * Add on packs should include and extend this class.
 *
 * This allows the main plugin to only include this file when rendering the front end.
 * Reduces the front-end footprint.
 *
 * @package StoreLocatorPlus\EnhancedResults\UserInterfaceJS
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2015 Charleston Software Associates, LLC
 *
 * @type {SLPER|*|{}}
 */

// Setup the Enhanced Results Namespace
var SLPER = SLPER || {};

// Enhanced Results Popup Email Form
//
SLPER.email_form = {

    //-------------------------------------
    // Methods
    //-------------------------------------

    /**
     * Build the jQuery email form.
     *
     * Blank, no from/to/subject/message.
     */
    build_form: function( ) {
        jQuery( '#email_form').dialog(
            {
                autoOpen: false ,
                minHeight: 50 ,
                minWidth: 450 ,
                title: csaslper_settings.email_form_title ,
                buttons: {
                    "Send"   : function() {
                        SLPER.email_form.send_email();
                        jQuery( this ).dialog( "close" );
                        } ,
                    "Cancel" : function() { jQuery( this).dialog( "close" ); }
                }
            }
            );
    },

    /**
     * Send the form content to the AJAX email handler.
     */
    send_email: function() {
        var ajax = new slp.Ajax();
        var action = {
            action: 'email_form' ,
            formdata: jQuery('#the_email_form').serialize()
        };
        ajax.send( action , function ( response ) { } );
        return true;
    },

    /**
     * Fill in the from/to/subject/message.
     *
     * @param sl_id the store ID
     */
    set_form_content: function( sl_id ) {
        jQuery( '#the_email_form input[name=sl_id]').val( sl_id );
    },

    /**
     * Show the email form.
     *
     * @param sl_id the store ID
     */
    show_form: function( sl_id ) {
        SLPER.email_form.set_form_content( sl_id );
        jQuery( '#email_form').dialog( "open" );
        return false;
    }

};


SLPER.location_list = {

    first_load: true, // Is this the first time loading the directory?

    /**
     * Disable Initial Directory
     */
    disable_directory: function () {
        if ( SLPER.location_list.first_load &&
            ( typeof( slplus.options.disable_initial_directory ) !== 'undefined' ) &&
            ( slplus.options.disable_initial_directory == '1' )
        )  {
            jQuery('div#map_sidebar').hide();
        } else {
            jQuery('div#map_sidebar').show();
            jQuery('#map_sidebar').off( 'contentchanged' , SLPER.location_list.disable_directory );
        }
        SLPER.location_list.first_load = false;
    }
};

// Document Ready
jQuery( document ).ready(
    function () {
            SLPER.email_form.build_form();

        jQuery('#map_sidebar').on( 'contentchanged' , SLPER.location_list.disable_directory );
    }
);
