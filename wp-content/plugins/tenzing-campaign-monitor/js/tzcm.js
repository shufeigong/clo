$(document).ready(function()
        {
				function campaignMonitor()
				{
					$('a.campaign').click(function(e) {
					    e.preventDefault(); 
					    var url= "/app/plugins/tenzing-campaign-monitor/campaignRetriever.php" + "?" + "campaignURL=" + $(this).data('url'); //
					    var myWindow = window.open(url, "MsgWindow", "width=650, height=800");
					});
				}
	        
        });
            

