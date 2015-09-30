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
					'posts_per_page' => 8,
					'template'       =>'list',
					
			],
			$atts
	);

	$today = current_time('Y-m-d h:i:s');

	$postType     = $atts['post_type'];
	$orderBy      = $atts['orderby'];
	$order        = $atts['order'];
	$postsPerPage = $atts['posts_per_page'];
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
	
	$output='';
	
	if (count($results) > 0&&$template=='list') {
		
		foreach ($results as $post) : setup_postdata($post);
		
		if(isHomepageEvents($post)&&isVideoEvents($post))
		{
			if(find_video($post->post_content)!=null){
				$output.=createVideoPost($post);
			}
			else{
				$output.=createNoVideoPost($post);
			}
		
		}
		else if(isHomepageEvents($post)&&!isVideoEvents($post)){
			
			$output.=createNoVideoPost($post);
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





?>