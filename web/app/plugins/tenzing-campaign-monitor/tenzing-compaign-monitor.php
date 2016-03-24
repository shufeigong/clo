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
	
	date_default_timezone_set('America/Toronto');
	
	$campaignItemArray=campaign_Monitor_GetData('DESC');
	
	foreach($campaignItemArray as $campaignItemObject)
	{
		$cm_post = array(
				'post_title' => $campaignItemObject->getSitemapUrl(),
				'post_type' =>'campaign',
				'post_status' => 'publish',
		);
	
		wp_insert_post( $cm_post );
	}
	
}



add_action('init', function() {

	register_post_type( 'campaign',
			array(
					'labels' => array(
							'name' => __( 'campaign' ),
							'singular_name' => __( 'campaign' ),
							'parent_item_colon'=>''
					),
					'public'=>true,
					'publicly_queryable' => true,
					'query_var' => true,
					'exclude_from_search' => false,
					'show_ui' => false,
					//'menu_position' => 23,
					'supports' => array(
							'title'
					),
					//'taxonomies' => array('post_tag'),
					'show_in_nav_menus' => false,
					'rewrite' => array('pages' => false, 'slug'=>'app/plugins/tenzing-campaign-monitor/campaignRetriever.php?campaignURL=http://eblast.gotenzing.com/t/ViewEmailArchive/r', 'with_frot'=>false),
					'hierarchical'=>true,
					'capability_type'=>'post',
					'has_archive' => true
			)
	);

});

	
function tz_campaign_monitor_remove() {
	//delete wp_options 
	delete_option('cm_actid');
	
	//delete all cm posts
	$args = ['post_type'      => 'campaign', /* Change with your custom post type name */
			 'posts_per_page' => -1,
	        ];
	$results = get_posts($args);
    foreach ($results as $post) :  setup_postdata($post);
	   wp_delete_post( $post->ID, true );
	endforeach;
	wp_reset_postdata();
	
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
	if ( !current_user_can( 'set_campaign_monitor' ) )
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
		
		//delete all cm posts
		$args = ['post_type'      => 'campaign', /* Change with your custom post type name */
				'posts_per_page' => -1,
		];
		$results = get_posts($args);
		foreach ($results as $post) :  setup_postdata($post);
		wp_delete_post( $post->ID, true );
		endforeach;
		wp_reset_postdata();
		
		
		$campaignItemArray=campaign_Monitor_GetData('DESC');
		
		foreach($campaignItemArray as $campaignItemObject)
		{
			$cm_post = array(
					'post_title' => $campaignItemObject->getSitemapUrl(),
					'post_type' =>'campaign',
					'post_status' => 'publish',
			        );
		
			wp_insert_post( $cm_post );
		}
		
	}
	
}



/////////////define campaignItemClass and Handle Class///////

class campaignItemClass{   //define campaignItemClass
	private $itemUrl;                 //Define member variables
	private $itemContent;
	private $itemDate;
	private $sitemapUrl;
	
	function __construct($itemUrl, $itemContent, $itemDate, $sitemapUrl)
	{
		$this->itemUrl=$itemUrl;
		$this->itemContent=$itemContent;
		$this->itemDate=$itemDate;
		$this->sitemapUrl=$sitemapUrl;
	}
	
	
	
	function getItemDate()
	{
		return  $this->itemDate;
	}
	
	
	function getItemContent()
	{
		return $this->itemContent;
	}
	
	function getSitemapUrl()
	{
		return $this->sitemapUrl;
	}
	
	function displayPerItem()
	{
		$output="";
		$output.='<li><span class="date">'.date("d M Y",strtotime($this->itemDate)).'</span> <span class="title"><a href="'.$this->itemUrl.
		'" class="campaign" data-url="'.$this->itemUrl.'" target="campaign">'.$this->itemContent.'</a></span></li>';
	    return $output;		
	}
	
	function displayNoPop()
	{
		$output="";
		$url = WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );
		$output.='<li><span class="date">'.date("d M Y",strtotime($this->itemDate)).'</span> <span class="title"><a href="'.$url.'campaignRetriever.php?campaignURL='.$this->itemUrl.
		'">'.$this->itemContent.'</a></span></li>';
		return $output;
	}
	
	
	
}


class campaignHandlerClass{   //define campaignHandlerClass to display one item group
 	private $campaignItemArray;
 	
 	function __construct($campaignItemArray)
 	{
 		$this->campaignItemArray=$campaignItemArray;
 	}
 	
 	function displayAllItem($number_posts, $is_pop_up_window)
 	{
 		$output='<div class="cm"><ul>';
 		$count=0; 			
 		
 		if($number_posts>0){
 			foreach ($this->campaignItemArray as $campaignItemObject)
 			{ 
 				++$count;
 				if($count>$number_posts){break;}
 			    $output.=($is_pop_up_window=="true")?$campaignItemObject->displayPerItem():$campaignItemObject->displayNoPop();
 			}
 		}else{
 			foreach ($this->campaignItemArray as $campaignItemObject)
 			{
 				$output.=($is_pop_up_window=="true")?$campaignItemObject->displayPerItem():$campaignItemObject->displayNoPop();
 			}
 		}	
 			
 		$output.='</ul></div>';
 		
 		
 		return $output;
 	}
 	
 	function displayMatchItems($newsletter_name, $number_posts, $is_pop_up_window)
 	{
 		$output='<div class="cm"><ul>';
 		$haveItems=false;
 		$count=0;
 		
 		if($number_posts>0){
 			foreach($this->campaignItemArray as $campaignItemObject)
 			{
 				if($this->judgeMatch($newsletter_name, $campaignItemObject->getItemContent())){
 					$haveItems=true;
 					++$count;
 					if($count>$number_posts){break;}
 					$output.=($is_pop_up_window=="true")?$campaignItemObject->displayPerItem():$campaignItemObject->displayNoPop();
 						
 				}
 			}
 		}else{
 			foreach($this->campaignItemArray as $campaignItemObject)
 			{
 				if($this->judgeMatch($newsletter_name, $campaignItemObject->getItemContent())){$haveItems=true;$output.=($is_pop_up_window=="true")?$campaignItemObject->displayPerItem():$campaignItemObject->displayNoPop();}
 			}
 		}
 		
 		
 		if($haveItems==false){ $output.='<li>No Newsletter found according to you search!</li>';}
 		$output.='</ul></div>';
 		
 		return $output;
 	}
 	
 	function judgeMatch($newsletter_name, $itemContent)
 	{
 		$isMatch=false;
 		foreach ($newsletter_name as $one_name)
 		{
 			if(stripos($itemContent, $one_name)!==false){$isMatch=true;}
 		}
 	
 		return $isMatch;
 	}
 	
 	
 	
}





function campaign_Monitor_GetData($order)
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
	    
		
		 if($order=='DESC'){usort($campaignItemArray, 'descDate');}else if($order=='ASC'){usort($campaignItemArray, 'ascDate');}
		return $campaignItemArray;
	}else{
		
		$campaignItemArray= array();  //no file found, return a empty array
		return $campaignItemArray;
	}
}


function descDate($a, $b) {
	return -(strtotime($a->getItemDate()) - strtotime($b->getItemDate()));
}


function ascDate($a, $b) {
	return strtotime($a->getItemDate()) - strtotime($b->getItemDate());
}

function parseFilelineToObject($fileline)
{
	$itemUrl;                 //Define member variables
	$itemContent;
	$itemDate;
    $sitemapUrl;
	
	$lineSplit=explode("</a>, ", $fileline);

	if(count($lineSplit)>0)
	{
		$str=$lineSplit[0];
		$delimiter="|||";
		$str=str_replace(array('<a href="', '">', '</a> '), $delimiter, $str);

		$strList=explode($delimiter, $str);
		if(count($strList)>1){
			$itemUrl=$strList[1];
		    $urlList=explode('r/', $itemUrl);
		    $sitemapUrl=$urlList[1];
		}else{$itemUrl="";}
		if(count($strList)>2){$itemContent=$strList[2];}else{$itemContent="";}
	}

	$itemDate = (count($lineSplit) > 1)? $lineSplit[1] : "";

	$campaignItemObject= new campaignItemClass($itemUrl, $itemContent, $itemDate, $sitemapUrl);

	return $campaignItemObject;

}



function campaign_Monitor_CreateHTML($atts)
{
	$atts
	= shortcode_atts(
			[
					'newsletter_name'      => '',
					'orderby'        => 'date',
					'order'          => 'DESC',
					'number_posts' => -1,
					'template'       =>'list',
					'is_pop_up_window' => 'true',
					'start_date'       =>'',
					'end_date'         =>''
			],
			$atts
	);
	
	
	if(preg_replace("/\s|　/","",$atts['newsletter_name'])!=''){
			$newsletter_name     = is_string($atts['newsletter_name']) ? array_map('trim', explode(',', strtolower($atts['newsletter_name'])))
			: $atts['newsletter_name'];
	}else{
		$newsletter_name='';
	}
	
	$orderBy      = $atts['orderby'];
	$order        = $atts['order'];
	$number_posts = $atts['number_posts'];
	$template     = $atts['template'];
	$is_pop_up_window = $atts['is_pop_up_window'];
	$startDate = $atts['start_date'];
	$endDate  = $atts['end_date'];
	
	$output="";
	
	$campaignItemArray=campaign_Monitor_GetData($order);
	
	if($startDate!=''&&$endDate!=''){
		$startPoint = strtotime($startDate);
		$endPoint = strtotime("+1 day".$endDate);
		
		foreach($campaignItemArray as $key=>$campaignItem):
			if(strtotime($campaignItem->getItemDate())<$startPoint || strtotime($campaignItem->getItemDate())>=$endPoint){
			 unset($campaignItemArray[$key]);
		    }
		endforeach;
	}
	
	
	if(count($campaignItemArray)>0){
	    $campaignHandlerObject=new campaignHandlerClass($campaignItemArray);
	
	    $output.=($newsletter_name=='')?$campaignHandlerObject->displayAllItem($number_posts, $is_pop_up_window):$campaignHandlerObject->displayMatchItems($newsletter_name, $number_posts, $is_pop_up_window);

	}else{
		$output.='<h2>No Newsletters found under this url:<a href="'.get_option('cm_actid').'">'.get_option('cm_actid').'</a></h2>';
	}
	//$output.=get_option('cm_actid');
   return $output;	
	
}

add_shortcode('campaign_monitor', 'campaign_Monitor_CreateHTML');

?>