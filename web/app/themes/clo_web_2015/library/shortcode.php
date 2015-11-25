<?php
//use Codeception\Lib\Console\Output;
date_default_timezone_set('America/Toronto');
function eventsShortcodeHandler($atts)
{
	$atts =
	shortcode_atts(
			[
					'post_type'      => ['incsub_event'],
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
					'number_posts' => -1,
					'template'       =>'block',
					
			],
			$atts
	);

	$today = current_time('Y-m-d h:i:s');

	$postType     = $atts['post_type'];
	$orderBy      = $atts['orderby'];
	$order        = $atts['order'];
	$postsPerPage = $atts['number_posts'];
	$template     = $atts['template'];
	//$catName      = $atts['cat_name'];

	//$catIds = '';

	//if ($catName) {
	//	$temp = explode(',', $catName);

	//	$catIds = array_map(
		//		function ($cat) {
			//		return get_cat_ID(trim($cat));
			//	},
			//	$temp
		//);
	//}

	$args = [
			'post_type'      => $postType, /* Change with your custom post type name */
			'orderby'        => $orderBy,
			'order'          => $order,
			'posts_per_page' => $postsPerPage,
			//'category__in'   => $catIds,
			'meta_query'     => [
					[
							//'key'     => 'incsub_event_start',
                            //'value'   => $today,
                            //'compare' => '>=',
                            //'type'    => 'DATETIME'
					]
			]
	];

	$results = get_posts($args);
	
	$colors=array("#DCE9F7","#B5D3EF","#EFF5DC","#DDEBB9");
	
	$output='';
	
	if (count($results) > 0&&$template=='block') {
		$key=0;
		foreach ($results as $post) : setup_postdata($post);
		//DCE9F7 0075C9 EFF5DC 82BC00
		
		if(isHomepageEvents($post)&&isVideoEvents($post))
		{
			if(find_video($post->post_content)!=null){
				$output.=createVideoPost($post, $colors[$key%4]);$key++;
			}
			else{
				$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
			}
		
		}
		else if(isHomepageEvents($post)&&!isVideoEvents($post)){
			
			$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
		}
		
		
		endforeach;
		wp_reset_postdata();
		
	}else if(count($results) > 0&&$template=='video-gallery'){
		$output.='<ul class="content-videolist">';
		foreach ($results as $post) : setup_postdata($post);
		if(isVideoEvents($post)){
			if(find_video($post->post_content)!=null){
				$output.=createVideoGallery($post);
			}
			else{
				$output.=createNoVideoGallery($post);
			}
		}
		
		endforeach;
		wp_reset_postdata();
		$output.='</ul>';
	}else {
		$output .= '<div id="events-wrap" class="block-wrap events-wrap">';
		$output .= '<p>No Upcoming Events Found".</p>';
		$output .= '</div>';
	}

	return $output;

}

add_shortcode('events', 'eventsShortcodeHandler');

///////////news////////////

function newsShortcodeHandler($atts)
{
	$atts =
	shortcode_atts(
			[
					'post_type'      => ['news'],
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
					'number_posts' => -1,
					'template'       =>'block',
						
			],
			$atts
	);

	$today = current_time('Y-m-d h:i:s');

	$postType     = $atts['post_type'];
	$orderBy      = $atts['orderby'];
	$order        = $atts['order'];
	$postsPerPage = $atts['number_posts'];
	$template     = $atts['template'];
	//$catName      = $atts['cat_name'];

	//$catIds = '';

	//if ($catName) {
	//	$temp = explode(',', $catName);

	//	$catIds = array_map(
	//		function ($cat) {
	//		return get_cat_ID(trim($cat));
		//	},
		//	$temp
	//);
	//}

	$args = [
			'post_type'      => $postType, /* Change with your custom post type name */
			'orderby'        => $orderBy,
			'order'          => $order,
			'posts_per_page' => $postsPerPage,
			//'category__in'   => $catIds,
			'meta_query'     => [
					[
							//'key'     => 'incsub_event_start',
							//'value'   => $today,
							//'compare' => '>=',
							//'type'    => 'DATETIME'
					]
			]
	];

	$results = get_posts($args);

	$colors=array("#DCE9F7","#B5D3EF","#EFF5DC","#DDEBB9");

	$output='';
    //var_dump($results);
	if (count($results) > 0 && $template=='block') {
		$key=0;
		foreach ($results as $post) : setup_postdata($post);
		//DCE9F7 0075C9 EFF5DC 82BC00
		
		if(isHomepageNews($post)&&isVideoNews($post))
		{
			if(find_video($post->post_content)!=null){
				$output.=createVideoPost($post, $colors[$key%4]);$key++;
			}
			else{
				$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
			}

		}
		else if(isHomepageNews($post)&&!isVideoNews($post)){
				
			$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
		}


		endforeach;
		wp_reset_postdata();

	}else if(count($results) > 0 && $template=='video-gallery'){
		$output.='<ul class="content-videolist">';
		foreach ($results as $post) : setup_postdata($post);
		if(isVideoNews($post)){
			if(find_video($post->post_content)!=null){
				$output.=createVideoGallery($post);
			}
			else{
				$output.=createNoVideoGallery($post);
			}
		}

		endforeach;
		wp_reset_postdata();
		$output.='</ul>';
	}else {
		$output .= '<div id="events-wrap" class="block-wrap events-wrap">';
		$output .= '<p>No Upcoming News Found".</p>';
		$output .= '</div>';
	}

	return $output;

}

add_shortcode('news', 'newsShortcodeHandler');

/////Timeline////////////
function timeLineShortcodeHandler($atts)
{
	$atts =
	shortcode_atts(
			[
					'title'      => 'My TimeLine',
					'lang'       => 'en',
			],
			$atts
	);

	$title     = $atts['title'];
	if($atts['lang']=='en'): ////////////////////////english timelne
		$args = [
				'post_type'      => "timeline", /* Change with your custom post type name */
				'posts_per_page' => -1,
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				
				'meta_query'     => [
						[
							'key'     => 'EventDate',
						]
				]
		];
	
	$currentyear=date("Y");
	$results = get_posts($args);
	
	if (count($results) > 0) {
		
		$timelinecontent="";
		$uniqueyear=1945;
		$counter=0;
		foreach ($results as $post) : setup_postdata($post);
		++$counter;
		if($post->EventDate>=$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div yearid="'.$uniqueyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDate.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
			//$uniqueyear=$post->EventDate;
			$uniqueyear+=5;
		}else if($post->EventDate<$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div class="oneyear">
				                    <div class="yeartitle">'.$post->EventDate.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}
		
		if($counter==count($results)){
			$timelinecontent.='<div yearid="'.$currentyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDate.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}
		
		//$timelinecontent.='<div></div>'.$post->EventDate.$post->post_content;
		endforeach;wp_reset_postdata();
	}
	
	$yearlist="";
	
	for($lineyear=1945; $lineyear<$currentyear; $lineyear+=5)
	{
		$yearlist.='<li><div class="timemark"></div><a class="'.$lineyear.'">'.$lineyear.'</a><div class="timearrow"></div></li>';
	}
	
	$yearlist.='<li><div class="timemark" style="border-left:none;"></div><a class="'.$currentyear.'" style="color:#6D6F71">Now</a><div class="timearrow"></div></li>';
	
	$output='<a class="btn_show">'.$title.'</a>
				
			   <div id="timeline" style="display:none;">
		            <div class="timelinebox">
				        <div class="yearlinebox">  
			              <ul>
				           '.$yearlist.'
	                      </ul>
				        </div>   		
			           	<div class="bluebox">	
				          '.$timelinecontent.'
				        </div>
			        </div>
		        </div>';
	elseif($atts['lang']=='fr'): ///////////////////////french timeline
	$args = [
			'post_type'      => "timeline", /* Change with your custom post type name */
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
				
			'meta_query'     => [
					[
							'key'     => 'EventDateFr',
					]
			]
	];
	$currentyear=date("Y");
	$results = get_posts($args);
	
	if (count($results) > 0) {
	
		$timelinecontent="";
		$uniqueyear=1945;
		$counter=0;
		foreach ($results as $post) : setup_postdata($post);
		++$counter;
		if($post->EventDateFr>=$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div yearid="'.$uniqueyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
			//$uniqueyear=$post->EventDate;
			$uniqueyear+=5;
		}else if($post->EventDateFr<$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}
	
		if($counter==count($results)){
			$timelinecontent.='<div yearid="'.$currentyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}
	
		//$timelinecontent.='<div></div>'.$post->EventDate.$post->post_content;
		endforeach;wp_reset_postdata();
	}
	
	
	
	
	$yearlist="";
	
	for($lineyear=1945; $lineyear<$currentyear; $lineyear+=5)
	{
		$yearlist.='<li><div class="timemark"></div><a class="'.$lineyear.'">'.$lineyear.'</a><div class="timearrow"></div></li>';
	}
	
	$yearlist.='<li><div class="timemark" style="border-left:none;"></div><a class="'.$currentyear.'" style="color:#6D6F71">Now</a><div class="timearrow"></div></li>';
	
	$output='<a class="btn_show">'.$title.'</a>
	
			   <div id="timeline" style="display:none;">
		            <div class="timelinebox">
				        <div class="yearlinebox">
			              <ul>
				           '.$yearlist.'
	                      </ul>
				        </div>
			           	<div class="bluebox">
				          '.$timelinecontent.'
				        </div>
			        </div>
		        </div>';
	
	endif;
	return $output;

}

function timeLineShortcodeHandlerFr($atts)
{
	$atts =
	shortcode_atts(
			[
					'title'      => 'My TimeLine',
			],
			$atts
	);

	$title     = $atts['title'];

	$args = [
			'post_type'      => "timeline", /* Change with your custom post type name */
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
				
			'meta_query'     => [
					[
							'key'     => 'EventDateFr',
					]
			]
	];

	$currentyear=date("Y");
	$results = get_posts($args);

	if (count($results) > 0) {

		$timelinecontent="";
		$uniqueyear=1945;
		$counter=0;
		foreach ($results as $post) : setup_postdata($post);
		++$counter;
		if($post->EventDateFr>=$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div yearid="'.$uniqueyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
			//$uniqueyear=$post->EventDate;
			$uniqueyear+=5;
		}else if($post->EventDateFr<$uniqueyear && $counter!=count($results)){
			$timelinecontent.='<div class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}

		if($counter==count($results)){
			$timelinecontent.='<div yearid="'.$currentyear.'" class="oneyear">
				                    <div class="yeartitle">'.$post->EventDateFr.'</div>
				               		<div class="yearcontent">'.$post->post_content.'</div>
				               </div>';
		}

		//$timelinecontent.='<div></div>'.$post->EventDate.$post->post_content;
		endforeach;wp_reset_postdata();
	}




	$yearlist="";

	for($lineyear=1945; $lineyear<$currentyear; $lineyear+=5)
	{
		$yearlist.='<li><div class="timemark"></div><a class="'.$lineyear.'">'.$lineyear.'</a><div class="timearrow"></div></li>';
	}

	$yearlist.='<li><div class="timemark" style="border-left:none;"></div><a class="'.$currentyear.'" style="color:#6D6F71">Now</a><div class="timearrow"></div></li>';

	$output='<a class="btn_show">'.$title.'</a>

			   <div id="timeline" style="display:none;">
		            <div class="timelinebox">
				        <div class="yearlinebox">
			              <ul>
				           '.$yearlist.'
	                      </ul>
				        </div>
			           	<div class="bluebox">
				          '.$timelinecontent.'
				        </div>
			        </div>
		        </div>';

	return $output;

}


add_shortcode('timeline', 'timeLineShortcodeHandler');
add_shortcode('timelinefr', 'timeLineShortcodeHandlerFr');

///////////shortcode for post_list/////
function postlistShortcodeHandler($atts)
{
	$atts
            = shortcode_atts(
            [
                'post_type'      => ['news', 'incsub_event'],
                'orderby'        => 'date',
				'order'          => 'DESC',
				'number_posts' => -1,
				'template'       =>'block',
            	'lang'           =>'en',
                
            ],
            $atts
        );
	
	if($atts['lang']=='en'): ////////////////////////English version postlist////////
	
		$postType     = is_string($atts['post_type']) ? array_map('trim', explode(',', $atts['post_type']))
		: $atts['post_type'];
		//$postType     = $atts['post_type'];
	
		
		$orderBy      = $atts['orderby'];
		$order        = $atts['order'];
		$postsPerPage = $atts['number_posts']==-1? -1: $atts['number_posts']+4;
		$template     = $atts['template'];
		
		$args = [
				'post_type'      => $postType, /* Change with your custom post type name */
				'orderby'        => $orderBy,
				'order'          => $order,
				'posts_per_page' => $postsPerPage,
				'post_status'    => 'publish',
		];
		
		
		
		
		$results = get_posts($args);
		
		$colors=array("#DCE9F7","#B5D3EF","#EFF5DC","#DDEBB9");
		
		$output='';
		$key=0;
		if (count($results) > 0&&$template=="block") {// for animated block
			
			foreach ($results as $post) : setup_postdata($post);
			//DCE9F7 0075C9 EFF5DC 82BC00
		    if($post->post_type=="news"||$post->post_type=="blog"){             // for animated block news and blog
		    	if(isHomepageNews($post)&&isVideoNews($post))
		    	{
		    		if(find_video($post->post_content)!=null){
		    			$output.=createVideoPost($post, $colors[$key%4]);$key++;
		    		}
		    		else{
		    			$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
		    		}
		    	
		    	}
		    	else if(isHomepageNews($post)&&!isVideoNews($post)){
		    	
		    		$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
		    	}
		    }
			else if($post->post_type=="incsub_event")    // for animated block event   
			{
				if(isHomepageEvents($post)&&isVideoEvents($post))
				{
					if(find_video($post->post_content)!=null){
						$output.=createVideoPost($post, $colors[$key%4]);$key++;
					}
					else{
						$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
					}
				
				}
				else if(isHomepageEvents($post)&&!isVideoEvents($post)){
						
					$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
				}
			}
		   
		
			endforeach;
			wp_reset_postdata();
		
		}else if(count($results) > 0 && $template=="video-gallery"){ //for video-gallery in web pages   
			$output.='<ul class="content-videolist">';
			foreach ($results as $post) : setup_postdata($post);
			
			if($post->post_type=="news"||$post->post_type=="blog"){ //for video-gallery news and block in web pages
				if(isVideoNews($post)){
					if(find_video($post->post_content)!=null){
						$output.=createVideoGallery($post);
					}
					else{
						$output.=createNoVideoGallery($post);
					}
				}
			}
			else if($post->post_type=="incsub_event"){    //for video-gallery event in web pages
				if(isVideoEvents($post)){
					if(find_video($post->post_content)!=null){
						$output.=createVideoGallery($post);
					}
					else{
						$output.=createNoVideoGallery($post);
					}
				}
				
				
				
			}
			endforeach;
			wp_reset_postdata();
			$output.='</ul>';
		}else if(count($results) > 0 && $template=="list"){
			$output.='<ul>';
			
			foreach ($results as $post) : setup_postdata($post);
			     $output.='<li><a href="' . get_permalink($post->ID) . '">'.$post->post_title.'</a></li>';
			endforeach;
			
			wp_reset_postdata();
			$output.='</ul>';
		}
		else{
			$output .= '<div id="events-wrap" class="block-wrap events-wrap">';
			$output .= '<p>No Upcoming News or events Found.</p>';
			$output .= '</div>';
		}
	elseif($atts['lang']=='fr')://////////French version postlist//////////
	
		//$output='french version postlist';
		$postType     = is_string($atts['post_type']) ? array_map('trim', explode(',', $atts['post_type']))
		: $atts['post_type'];
		//$postType     = $atts['post_type'];
		
		
		$orderBy      = $atts['orderby'];
		$order        = $atts['order'];
		$postsPerPage = $atts['number_posts']==-1? -1: $atts['number_posts']+4;
		$template     = $atts['template'];
		
		$args = [
				'post_type'      => $postType, /* Change with your custom post type name */
				'orderby'        => $orderBy,
				'order'          => $order,
				'posts_per_page' => $postsPerPage,
				'post_status'    => 'publish',
		];
		
		
		
		
		$results = get_posts($args);
		
		$colors=array("#DCE9F7","#B5D3EF","#EFF5DC","#DDEBB9");
		
		$output='';
		$key=0;
		if (count($results) > 0&&$template=="block") {// for animated block
				
			foreach ($results as $post) : setup_postdata($post);
			//DCE9F7 0075C9 EFF5DC 82BC00
			if($post->post_type=="news"||$post->post_type=="blog"){             // for animated block news and blog
				if(isHomepagefrNews($post)&&isVideofrNews($post))
				{
					if(find_video($post->post_content)!=null){
						$output.=createVideoPost($post, $colors[$key%4]);$key++;
					}
					else{
						$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
					}
				  
				}
				else if(isHomepagefrNews($post)&&!isVideofrNews($post)){
				  
					$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
				}
			}
			else if($post->post_type=="incsub_event")    // for animated block event
			{
				if(isHomepagefrEvents($post)&&isVideofrEvents($post))
				{
					if(find_video($post->post_content)!=null){
						$output.=createVideoPost($post, $colors[$key%4]);$key++;
					}
					else{
						$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
					}
		
				}
				else if(isHomepagefrEvents($post)&&!isVideofrEvents($post)){
		
					$output.=createNoVideoPost($post, $colors[$key%4]); $key++;
				}
			}
			 
		
			endforeach;
			wp_reset_postdata();
		
		}else if(count($results) > 0 && $template=="video-gallery"){ //for video-gallery in web pages
			$output.='<ul class="content-videolist">';
			foreach ($results as $post) : setup_postdata($post);
				
			if($post->post_type=="news"||$post->post_type=="blog"){ //for video-gallery news and blog in web pages
				if(isVideofrNews($post)){
					if(find_video($post->post_content)!=null){
						$output.=createVideoGallery($post);
					}
					else{
						$output.=createNoVideoGallery($post);
					}
				}
			}
			else if($post->post_type=="incsub_event"){    //for video-gallery event in web pages
				if(isVideofrEvents($post)){
					if(find_video($post->post_content)!=null){
						$output.=createVideoGallery($post);
					}
					else{
						$output.=createNoVideoGallery($post);
					}
				}
		
		
		
			}
			endforeach;
			wp_reset_postdata();
			$output.='</ul>';
		}else if(count($results) > 0 && $template=="list"){
			$output.='<ul>';
				
			foreach ($results as $post) : setup_postdata($post);
			$output.='<li><a href="' . get_permalink($post->ID) . '">'.$post->post_title.'</a></li>';
			endforeach;
				
			wp_reset_postdata();
			$output.='</ul>';
		}
		else{
			$output .= '<div id="events-wrap" class="block-wrap events-wrap">';
			$output .= '<p>No Upcoming News or events Found.</p>';
			$output .= '</div>';
		}
	
	endif;
	return $output;
	
}

add_shortcode('post_list', 'postlistShortcodeHandler');

///////////////////////siteMap shortcode///////////


function sitemapShortcodeHandler()
{
	$output=mainMenuSiteMap();
	return $output;
}

add_shortcode('sitemap', 'sitemapShortcodeHandler');

?>