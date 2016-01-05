/*****************************************************************
 * file: reporting.js
 *
 *****************************************************************/

var SLP_REPORT = SLP_REPORT || {

    /**
     * Chart management object.
     */
    chart: function () {

        /**
         * Draw the chart.
         */
        this.drawChart = function() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Queries');
            data.addColumn('number', 'Results');

            // Add Rows
            //
            var data_entries = slp_pro.count_dataset.length;
            for (var entrynum = 0 ; entrynum < data_entries ; entrynum++ ) {
                data.addRows([ [slp_pro.count_dataset[entrynum].TheDate , parseInt(slp_pro.count_dataset[entrynum].QueryCount) , parseInt(slp_pro.count_dataset[entrynum].ResultCount)] ]);
            }
            if ( slp_pro.chart_type === 'ColumnChart' ) {
                var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            } else {
                var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
            }
            chart.draw(data, {width: 800, height: 400, pointSize: 4});
        }
    },

    /**
     * Message management object.
     */
    message: function() {

        /**
         * Show no data message.
         */
        this.show_no_data_message = function() {
            jQuery("#chart_div").html(
                '<p>' +
                    slp_pro.message_nodata +
                    slp_pro.message_chartaftersearch +
                    '</p>'
            );
        }
    }
};


// Document Is Ready...
//
jQuery(document).ready(
    function($) {

        // Make tables sortable
        //
        var tstts = $("#topsearches_table").tablesorter( {sortList: [[1,1]]} );
        var trtts = $("#topresults_table").tablesorter( {sortList: [[5,1]]} );

        // Export Results Click
        //
        jQuery('#export_results').click(
            function(e) {
               var data = {
                  action: 'slp_download_report_csv',
                  filename: 'topresults',
                  query: jQuery("[name=topresults]").val(),
                  sort: trtts[0].config.sortList.toString(),
                  all: jQuery("[name=export_all]").is(':checked')
               };
               jQuery('#secretIFrame').attr('src',
                    ajaxurl + '?' + jQuery.param(data)
                );
            }
        );

        // Export Searches Button Click
        //
         jQuery('#export_searches').click(
            function(e) {
               var data = {
                  action: 'slp_download_report_csv',
                  filename: 'topsearches',
                  query: jQuery("[name=topsearches]").val(),
                  sort: tstts[0].config.sortList.toString(),
                  all: jQuery("[name=export_all]").is(':checked')
               };
               jQuery('#secretIFrame').attr('src',
                    ajaxurl + '?' + jQuery.param(data)
                );
            }
         );

        if (slp_pro.total_searches > 0 ) {
            var chart = new  SLP_REPORT.chart();
            google.load('visualization', '1.0', {'packages':['corechart'], 'callback': chart.drawChart });

        } else {
            var message = new SLP_REPORT.message();
            message.show_no_data_message();
        }
    }
);
