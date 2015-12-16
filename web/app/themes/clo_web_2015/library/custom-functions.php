<?php
function find_video($markup) {
    global $video_thumbnails;
    return $video_thumbnails->find_videos( $markup );
}

function grab_url($text) {
    $regex = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
    
    preg_match_all($regex, $text, $matches);
    
    return $matches[0];
}
////function for judging video, homepage or button events
function isVideoEvents($post){
	
	$query = new WP_Query( array( 'eab_events_category' => 'video' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;
	
	
}

function isVideofrEvents($post){

	$query = new WP_Query( array( 'eab_events_category' => 'videofr' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;


}

function isHomepageEvents($post){

	$query = new WP_Query( array( 'eab_events_category' => 'homepage' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;
}

function isHomepagefrEvents($post){

	$query = new WP_Query( array( 'eab_events_category' => 'homepagefr' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;
}

function isButtonEvents($post){
	$query = new WP_Query( array( 'eab_events_category' => 'button' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;
}

function isButtonfrEvents($post){
	$query = new WP_Query( array( 'eab_events_category' => 'buttonfr' ));
	if(in_array($post, $query->posts))
		return true;
	else
		return false;
}

////function for judge video, homepage or button news and other normal posts including blog news and page.
function isVideoNews($post){
    $returnvalue=false;
       	
	foreach (get_the_category($post) as $thiscat)
	{
	   if($thiscat->name=="video")
	     {$returnvalue=true;break;}
	}
    
	return $returnvalue;
}

function isVideofrNews($post){
	$returnvalue=false;

	foreach (get_the_category($post) as $thiscat)
	{
		if($thiscat->name=="videofr")
		{$returnvalue=true;break;}
	}

	return $returnvalue;
}

function isHomepageNews($post){

	$returnvalue=false;
       	
	foreach (get_the_category($post) as $thiscat)
	{
		if($thiscat->name=="homepage")
	      {$returnvalue=true;break;}
	}
    
	return $returnvalue;
}

function isHomepagefrNews($post){

	$returnvalue=false;

	foreach (get_the_category($post) as $thiscat)
	{
		if($thiscat->name=="homepagefr")
		{$returnvalue=true;break;}
	}

	return $returnvalue;
}

function isButtonPosts($post){

	$returnvalue=false;

	foreach (get_the_category($post) as $thiscat)
	{
		if($thiscat->name=="button")
		{$returnvalue=true;break;}
	}

	return $returnvalue;
}

function isButtonfrPosts($post){

	$returnvalue=false;

	foreach (get_the_category($post) as $thiscat)
	{
		if($thiscat->name=="buttonfr")
		{$returnvalue=true;break;}
	}

	return $returnvalue;
}




///function for create video animated block 
function createVideoPost($post, $color){
	$videoInfo     = find_video($post->post_content);
	
	foreach($videoInfo as $video)
	{
		$videoId       = $video["id"];
		$videoProvider = $video['provider'];
		//$outputvideo.= '<div style="background-image: url('.get_video_thumbnail($post->ID).'); background-size:contain; background-repeat:no-repeat;"><div class="play-button"><span class="arrow"></span></div></div>';
		break;
	}
	
	$output = '<li><a href="#" class="slvj-link-lightbox" data-videoid="'.$videoId.'" data-videosite="'.$videoProvider.'"><div class="news-item has-video" style="background:'.$color.';"><div class="arrow"></div>';
	
	/*$st_time   = date(
	 'D M d',
	 strtotime($post->incsub_event_start == '' ? $post->due_date : $post->incsub_event_start)
	);
		
	$en_time   = date(
	'D M d',
	strtotime($post->incsub_event_end == '' ? $post->due_date : $post->incsub_event_end)
	);*/
		
	if(mb_strlen($post->post_title)>10){
		$post->post_title = strtoupper(mb_substr($post->post_title,0,10,"UTF8")).'...';
	}else{
		$post->post_title = strtoupper($post->post_title);
	}
		
	$output .= '<div class="content-box"><h2 class="post_title">' . $post->post_title. '</h2>';
		
	$urls=grab_url($post->post_content);
		
	foreach ($urls as $url )
	{
		$post->post_content=str_replace($url, '', $post->post_content);
	}
	
	$post->post_content = strip_tags($post->post_content);
	
    if(mb_strlen($post->post_content)>70){
		$post->post_content = mb_substr($post->post_content,0,70,"UTF8")."...";
	}
	
	
	$output .= '<p class="post-content">' . $post->post_content.'</p>
					    </div>';//end of content-box
	
	$output.='<div class="video-box" style="background-image: url('.get_video_thumbnail($post->ID).'); background-size:cover; background-repeat:no-repeat;"><div class="play-button"><span class="arrow"></span></div></div>';//end of video box
		
	$output .= '<div class="clearfix"></div>
                        </div>
                       </a></li>';//end of news item
	return $output;
}

///function for creating no video animated block
function createNoVideoPost($post, $color){
	$output= '<li><a href="' . get_permalink($post->ID) . '"><div class="news-item no-video" style="background:'.$color.';">
                        <div class="arrow"></div>';
	
	if(mb_strlen($post->post_title)>20){
		$post->post_title = strtoupper(mb_substr($post->post_title,0,20,"UTF8")).'...';
	}else{
		$post->post_title = strtoupper($post->post_title);
	}
		
	$output.='<div class="content-box"><h2 class="post_title">' . $post->post_title . '</h2>';
	
	$urls=grab_url($post->post_content);
	
	foreach ($urls as $url )
	{
		$post->post_content=str_replace($url, '', $post->post_content);
	}
	
	$post->post_content = strip_tags($post->post_content);
	
	if(mb_strlen($post->post_content)>77){
		$post->post_content = mb_substr($post->post_content,0,77,"UTF8")."...";
	}
		$output.='<p class="post-content">' . $post->post_content.'</p>
					    </div>';
		//$output.='<div class="video-box" style=" width:1px;visibility:hidden;background-size:100% 100%; background-repeat:no-repeat;"></div>';//end of video box
		
	
	//$output.=mb_strlen($post->post_content);
	
	
	//$output.='<div class="video-box" style="background-size:100% 100%; background-repeat:no-repeat;visibility:hidden;"></div>';//end of video box
	
	$output .= '<div class="clearfix"></div>
                        </div>
                       </a></li>';//end of news itemreturn $output;
	return $output;
}

///function for creating button animated block
function createButtonPost($post, $color){
	$output= '<li><a href="' . get_permalink($post->ID) . '"><div class="news-item no-video" style="background:'.$color.';">
                        <div class="arrow"></div>';
	
	
	if(mb_strlen($post->post_title)>6){
		$post->post_title = strtoupper(mb_substr($post->post_title,0,6,"UTF8")).'<div class="apos">...</div>';
	}else{
		$post->post_title = strtoupper($post->post_title);
	}

	$output.='<div class="content-box"><div class="button_title">' . $post->post_title. '</div>';

	$urls=grab_url($post->post_content);

	
	$output.='<p class="post-content"></p>
					    </div>';
	//$output.='<div class="video-box" style=" width:1px;visibility:hidden;background-size:100% 100%; background-repeat:no-repeat;"></div>';//end of video box


	//$output.=mb_strlen($post->post_content);


	//$output.='<div class="video-box" style="background-size:100% 100%; background-repeat:no-repeat;visibility:hidden;"></div>';//end of video box

	$output .= '<div class="clearfix"></div>
                        </div>
                       </a></li>';//end of news itemreturn $output;
	return $output;
}



///function for creating video gallery
function createVideoGallery($post){
	$videoInfo     = find_video($post->post_content);
	
	foreach($videoInfo as $video)
	{
		$videoId       = $video["id"];
		$videoProvider = $video['provider'];
		break;
	}
	
	$output = '<li class="content-video-item"><a href="#" class="slvj-link-lightbox" data-videoid="'.$videoId.'" data-videosite="'.$videoProvider.'">';
	$output.='<div class="content-video-box" style="background-image: url('.get_video_thumbnail($post->ID).'); background-size:cover; background-repeat:no-repeat;"><div class="content-play-button"><span class="content-arrow"></span></div></div>';		       
	$output.='<div class="content-video-title">'.$post->post_title.'</div>';
	$output .= '</a></li>';//end of news item
	return $output;
	
}

///function for creating no video gallery
function createNoVideoGallery($post){
	$output = '<li class="content-video-item"><a href="' . get_permalink($post->ID) . '" class="content-video-link" style="text-decoration:none;">';
	$output.='<div class="content-video-box">Video Not Found</div>';
	$output.='<div class="content-video-title">'.$post->post_title.'</div>';
	
	$output .= '</a></li>';//end of news item
	return $output;
}

//////function for sitmap///
//call the menu and use our custom walker

function admin_bar_fix() {
	if(!is_admin() && is_admin_bar_showing()) {
		remove_action('wp_head', '_admin_bar_bump_cb');
		$output  = '<style type="text/css">'."\n\t";
		$output .= 'body.admin-bar { padding-top: 28px; }'."\n";
		$output .= '</style>'."\n";
		echo $output;
	}
}
add_action('wp_head', 'admin_bar_fix', 5);


?>