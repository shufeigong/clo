<?php 
/*
Plugin Name: Tenzing Campaign Monitor

Plugin URI: http://gotenzing.com/wordpress-plugins/tenzing-campaign-monitor

Description: For campaign monitor

Version: 1.0

Author: Shufei Gong

License: GPLv2
*/

/* 注册激活插件时要调用的函数 */
register_activation_hook( __FILE__, 'tz_campaign_monitor_install');

/* 注册停用插件时要调用的函数 */
register_deactivation_hook( __FILE__, 'tz_campaign_monitor_remove' );


// Set up our WordPress Plugin
function tz_campaign_monitor_install()
{
  if ( get_option( 'cm_actid' ) === false )
	{
		add_option("cm_actid", "http://eblast.gotenzing.com/t/r/p/iriutl/0/1/0/1/0/", '', 'yes');
	}
}


function tz_campaign_monitor_remove() {
	/* 删除 wp_options 表中的对应记录 */
	delete_option('cm_actid');
}

if(isset($_GET['m']) && $_GET['m']== '1')
{
	echo '<div id="message" class="updated fade"><p><strong style="font-size:15px;">You have successfully updated your Campaign Monitor ID.</strong></p></div>';
}
else if(isset($_GET['m']) && $_GET['m']== '2'){
	echo '<div id="message" class="updated fade"><p><strong style="font-size:15px;color:red;">You must enter a valid URL for Campaign Monitor ID.</strong></p></div>';
}
else if(isset($_GET['m']) && $_GET['m']== '3')
{
	echo '<div id="message" class="updated fade"><p><strong style="font-size:15px;color:red;">You must enter your Campaign Monitor ID.</strong></p></div>';
}


if( is_admin() ) {
	/*  利用 admin_menu 钩子，添加菜单 */
	add_action('admin_menu', 'display_cm_menu');
}

////////////////////////////////////////////////
function display_cm_menu() {
	add_options_page('Set Campaign Monitor', 'Campaign Monitor Menu', 'set_campaign_monitor','display_cm', 'display_cm_html_page');
}

add_action( 'admin_post_cm_actid_save_hook', 'process_cm_actid' );

function process_cm_actid()
{
	if ( !current_user_can( 'manage_options' ) )
   {
      wp_die( 'You are not allowed to be on this page.' );
   }
   // Check that nonce field
   check_admin_referer( 'cm_actid_verify' );
 
   //$options = get_option( 'cm_actid' );
   
   if(isset($_POST['cm_actid']))
   {
   	  if(!empty($_POST['cm_actid']) && isUrl($_POST['cm_actid']))
   	  {
   	  	update_option( 'cm_actid', $_POST['cm_actid'] );
   	  	campaign_Monitor_LoadData();
   	  	wp_redirect(admin_url('options-general.php?page=display_cm&m=1'));
   	  	exit;
   	  }
   	  else if(!empty($_POST['cm_actid']) && !isUrl($_POST['cm_actid'])){
   	  	wp_redirect(admin_url('options-general.php?page=display_cm&m=2'));
   	  	exit;
   	  }else{
   	  	wp_redirect(admin_url('options-general.php?page=display_cm&m=3'));
   	  	exit;
   	  }
   	  
   }
}

function isUrl($s)  
{  
    return preg_match('/^http[s]?:\/\/'.  
        '(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184  
        '|'. // 允许IP和DOMAIN（域名）  
        '([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.  
        '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证  
        '[a-z]{2,6})'.  // 顶级域验证.com or .museum  
        '(:[0-9]{1,4})?'.  // 端口- :80  
        '((\/\?)|'.  // 如果含有文件对文件部分进行校验  
        '(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/',  
        $s) == 1;  
}  


function display_cm_html_page() {
	?>
    <div class="wrap">  
        <h2>Set Campaign Monitor</h2>  
        
        <form method="post" action="admin-post.php">  
            <input type="hidden" name="action" value="cm_actid_save_hook" />  
            
            <?php wp_nonce_field('cm_actid_verify'); ?>  
 
            <p>  
                <label for="cm_actid">Campaign Monitor ID: </label>
                <input type="text" name="cm_actid" id="cm_actid" value="<?php echo get_option('cm_actid'); ?>" style="width:500px"/>
            </p>  
 
            <p>                  
                <input type="submit" value="Save" class="button-primary" />  
            </p>  
        </form>  
    </div>  
<?php  
}  




if (!wp_next_scheduled('wpjam_daily_function_hook')) {
	wp_schedule_event( time(), 'daily', 'wpjam_daily_function_hook' );
}

add_action( 'wpjam_daily_function_hook', 'campaign_Monitor_LoadData');

function campaign_Monitor_LoadData()
{
	if(get_option( 'cm_actid' ) !== false){
		$cm_actid = get_option( 'cm_actid' );
		$str=file_get_contents($cm_actid);
		file_put_contents(plugin_dir_path(__FILE__).'cm.txt', $str);
	}
	
}



/////////////define campaignItemClass and Handle Class///////

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
		$output.='<h2>No Newsletters found under this url:<a href="'.get_option('cm_actid').'">'.get_option('cm_actid').'</a></h2>';
	}
	//$output.=get_option('cm_actid');
   return $output;	
	
}

add_shortcode('campaignMonitor', 'campaign_Monitor_CreateHTML');

?>