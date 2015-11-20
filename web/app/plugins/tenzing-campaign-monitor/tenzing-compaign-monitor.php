<?php 
/*
Plugin Name: Tenzing Campaign Monitor

Plugin URI: http://gotenzing.com/wordpress-plugins/tenzing-campaign-monitor

Description: For campaign monitor

Version: 1.0

Author: Shufei Gong

License: GPLv2
*/

class campaignItemClass{   //define campaignItemClass
	private $itemUrl;                 //Define member variables
	private $itemContent;
	private $itemContentDate;
	private $itemDate;
	private $itemType;


	function __construct($itemUrl, $itemContent, $itemContentDate, $itemDate, $itemType)
	{
		$this->itemUrl=$itemUrl;
		$this->itemContent=$itemContent;
		$this->itemContentDate=$itemContentDate;
		$this->itemDate=$itemDate;
		$this->itemType=$itemType;
	}
	
	function getItemType()
	{
		return  $this->itemType;
	}
	
	function displayPerItem()
	{
		$output="";
		$output.='<li><span class="date">'.$this->itemDate.'</span> <span class="title"><a href="'.$this->itemUrl.
		'" class="campaign" data-url="'.$this->itemUrl.'" target="campaign">'.$this->itemContent.'</a></span></li>';
	    return $output;		
	}
	
}


class campaignHandlerClass{   //define campaignHandlerClass to display one item group
 	private $campaignItemArray;
 	
 	function __construct($campaignItemArray)
 	{
 		$this->campaignItemArray=$campaignItemArray;
 	}
 	
 	function displayGroupItem($itemTypes)
 	{
 		$output="";
 		
 		foreach ($itemTypes as $itemType)
 		{
 			$output.='<h2>'.$itemType.'</h2>
                      <div class="'.preg_replace('/[\s　]/', '_', $itemType).'"><ul>';
 			
 			$haveType=false;
 			
 			foreach ($this->campaignItemArray as $campaignItemObject)
 			{
 				if($itemType==$campaignItemObject->getItemType()) {$haveType=true;$output.=$campaignItemObject->displayPerItem();}
 			}
 			
 			if($haveType==false){ $output.='<li>No Newsletter found for this type!</li>';}
 			
 			$output.='</ul></div>';
 		}
 		
 		return $output;
 	}
 	
 	
}


if (!wp_next_scheduled('wpjam_daily_function_hook')) {
    wp_schedule_event( time(), 'daily', 'wpjam_daily_function_hook' );
}

add_action( 'wpjam_daily_function_hook', 'campaign_Monitor_LoadData');

function campaign_Monitor_LoadData()
{
	$str=file_get_contents("http://eblast.gotenzing.com/t/r/p/iriutl/0/1/0/1/0/");
	file_put_contents(plugin_dir_path(__FILE__).'cm.txt', $str);
}


//add_action( 'plugins_loaded', 'boj_footer_message_plugin_setup' );

//function boj_footer_message_plugin_setup() {

//	wp_enqueue_script( 'tzcmjs', plugins_url( 'js/tzcm.js' , __FILE__ ), '1.0.0', true );

//}




function campaign_Monitor_GetData()
{
	if(file_exists(plugin_dir_path(__FILE__).'cm.txt')){
		$file=file_get_contents(plugin_dir_path(__FILE__).'cm.txt');
		$file=str_replace(array("document.write('<ul>')","document.write('<li>","document.write('</ul>')"),"",$file);
	
		$filelines = explode("</li>')", $file);
	
		$campaignItemArray= array();
	
		for($i=0; $i<count($filelines)-1; $i++)
		{
			$campaignItemArray[]=parseFilelineToObject($filelines[$i]);
		}
	
		return $campaignItemArray;
	}else{
		
		$campaignItemArray= array();  //no file found, return a empty array
		return $campaignItemArray;
	}
}


function parseFilelineToObject($fileline)
{
	$itemUrl;                 //Define member variables
	$itemContent;
	$itemContentDate;
	$itemDate;
	$itemType;

	$lineSplit=explode("</a>, ", $fileline);

	if(count($lineSplit)>0)
	{
		$str=$lineSplit[0];
		$delimiter="|||";
		$str=str_replace(array('<a href="', '">', ': ', '</a> '), $delimiter, $str);

		$strList=explode($delimiter, $str);
		if(count($strList)>1){$itemUrl=$strList[1];}else{$itemUrl="";}
		if(count($strList)>2){$itemContent=$strList[2];}else{$itemContent="";}
		if(count($strList)>3){$itemContentDate=$strList[3];}else{$itemContentDate="";}
	}

	$itemDate = (count($lineSplit) > 1)? $lineSplit[1] : "";

	
	if(strpos(strtolower($itemContent),"update friday")!==false){
		$itemType="update friday";
	}else if(strpos(strtolower($itemContent),"communiqu")!==false){
		$itemType="communiqu";
	}else if(strpos(strtolower($itemContent),"memo")!==false){
		$itemType="memo";
	}else if(strpos(strtolower($itemContent),"quarterly report")!==false){
		$itemType="quarterly report";
	}else{
		$itemType="other";
	}

	$campaignItemObject= new campaignItemClass($itemUrl, $itemContent, $itemContentDate, $itemDate, $itemType);

	return $campaignItemObject;

}



function campaign_Monitor_CreateHTML($atts)
{
	$atts
	= shortcode_atts(
			[
					'newsletter_name'      => ['update friday', 'communiqu', 'memo', 'quarterly report'],
			],
			$atts
	);
	
	$itemTypes     = is_string($atts['newsletter_name']) ? array_map('trim', explode(',', strtolower($atts['newsletter_name'])))
	: $atts['newsletter_name'];
	
	$output="";
	
	$campaignItemArray=campaign_Monitor_GetData();
	
	if(count($campaignItemArray)>0){
	    $campaignHandlerObject=new campaignHandlerClass($campaignItemArray);
	
	    $output.=$campaignHandlerObject->displayGroupItem($itemTypes);

	}else{
		$output.="<h2>No Newsletters found!</h2>";
	}
	
   return $output;	
	
}

add_shortcode('campaignMonitor', 'campaign_Monitor_CreateHTML');

?>