<?php
/*
 Plugin Name: Tenzing Album

 Plugin URI: http://gotenzing.com/wordpress-plugins/tenzing-album

 Description: For tenzing album show

 Version: 1.0

 Author: Shufei Gong

 License: GPLv2
 */
 
/* 注册激活插件时要调用的函数 */
//register_activation_hook( __FILE__, 'tz_album_install');

/* 注册停用插件时要调用的函数 */
//register_deactivation_hook( __FILE__, 'tz_album_remove' );


// Set up our WordPress Plugin
//function tz_album_install()
//{
	//session_start();
	//$_SESSION['albumId']=0;
//}


//function tz_album_remove() {
	//unset($_session['albumId']);
//}





//////define imgClass//////

class imgClass{
	
	public $imgUrl;
	public $imgAlt;
	public $imgCaption;
	public $imgTitle;
	public $imgDescription;
	
	function __construct($imgUrl, $imgAlt, $imgCaption, $imgTitle, $imgDescription)
	{
		$this->imgUrl = $imgUrl;
		$this->imgAlt = $imgAlt;
		$this->imgCaption = $imgCaption;
		$this->imgTitle = $imgTitle;
		$this->imgDescription = $imgDescription;
	}
	
	function createGridItem($i)
	{
		$output="";
		$output.='<a href="#" onclick="gridClick(this); return false;" gridindex="'.$i.'"><img src="'.$this->imgUrl.'" alt="'.$this->imgAlt.'" cap="'.$this->imgCaption.'"/></a>';
		return $output;
	}
	
	function createSelectedGridItem($i)
	{
		$output="";
		$output.='<a href="#" onclick="gridClick(this); return false;" class="imgSelected" gridindex="'.$i.'"><img src="'.$this->imgUrl.'" alt="'.$this->imgAlt.'" cap="'.$this->imgCaption.'"/></a>';
		return $output;
	}
	
	
}

////define imgHandlerClass//////

class imgHandlerClass{
	
	public $albumId;
	public $albumName;
	public $imgObjectArray;
	
	function __construct($albumId, $albumName, $imgObjectArray)
	{
		$this->albumId = $albumId;
		$this->albumName = $albumName;
		$this->imgObjectArray = $imgObjectArray;
	}
	
	function createAlbumTrigger()
	{
		$output="";
		$output.='<div class="albumTrigger"><a href="#" onclick="return false;" class="album_show" albumid="'.$this->albumId.'">';
		$output.='<div class="content-video-box" style="background-image: url('.$this->imgObjectArray[0]->imgUrl.'); background-size:cover; background-position:center; background-repeat:no-repeat;"></div>';
		$output.='<div class="content-video-title">'.$this->albumName.'</div>';
		$output.='</a></div>';
		return $output;
	}
	
	function createAlbumContent()
	{
		$output="";
		$output.='<div id="'.$this->albumId.'" style="display:none;">
		              <div class="albumBox">  
				        <div class="albumGrid">'.$this->createAlbumGrid().'</div>
			           	<div class="imgShowBox"> 
			             	 <a class="leftArrow" href="#" onclick="leftArrowClick(this); return false;"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
					                            viewBox="0 0 26 67" style="enable-background:new 0 0 26 67;" xml:space="preserve">
				                                <metadata><?xpacket begin="﻿" id="W5M0MpCehiHzreSzNTczkc9d"?>
				                                <x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.6-c067 79.157747, 2015/03/30-23:40:42        ">
				                                <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
				                                <rdf:Description rdf:about=""/>
                                                </rdf:RDF>
												</x:xmpmeta>
												<?xpacket end="w"?></metadata>
												<polygon class="albumst0" points="7.7,33.7 25.7,0 18.1,0 0,33.7 18.1,67.5 25.7,67.5 "/>
                               </svg></a>
				             <div class="imgShow"><img src="'.$this->imgObjectArray[0]->imgUrl.'" alt="'.$this->imgObjectArray[0]->imgAlt.'"/></div>	
				             <div class="imgCaption">'.$this->imgObjectArray[0]->imgCaption.'</div>
				             <a class="rightArrow" href="#" onclick="rightArrowClick(this); return false;"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	                                                                                                       viewBox="0 0 26 67" style="enable-background:new 0 0 26 67;" xml:space="preserve">
												<metadata><?xpacket begin="﻿" id="W5M0MpCehiHzreSzNTczkc9d"?>
												<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.6-c067 79.157747, 2015/03/30-23:40:42        ">
												   <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
												      <rdf:Description rdf:about=""/>
												   </rdf:RDF>
												</x:xmpmeta>                    
												<?xpacket end="w"?></metadata>
												<polygon class="albumst0" points="18.1,33.7 0,67.5 7.7,67.5 25.7,33.7 7.7,0 0,0 "/>
                             </svg></a>
		             	</div>
				      </div>
		         </div>';
		
		return $output;
	}
	
	function createAlbumGrid()
	{
		$output="";
		$output.='<table>';
		
		for($i=0; $i<count($this->imgObjectArray); $i++)
		{
			if($i%4==0){$output.='<tr>';}
			if($i==0){$output.='<td class="tdSelected">'.$this->imgObjectArray[$i]->createGridItem($i).'</td>';}else{$output.='<td>'.$this->imgObjectArray[$i]->createGridItem($i).'</td>';}
			if($i%4==3||$i==count($this->imgObjectArray)-1){$output.='</tr>';}
		}
		
		$output.='</table>';
		return $output;
		
	}
	
	
}




///define parseImgtToObject function//////
function parseImgToObject($img)
{   
	$imgUrl = wp_get_attachment_url($img->ID);
	$imgAlt = get_post_meta($img->ID, '_wp_attachment_image_alt', true);  
	$imgCaption = $img->post_excerpt;
	$imgTitle =  $img->post_title;
	$imgDescription = $img->post_content;
	
	$imgObject = new imgClass($imgUrl, $imgAlt, $imgCaption, $imgTitle, $imgDescription);
	
	return $imgObject;
		
}







//Override gallery shortcode
add_shortcode( 'gallery', 'my_post_gallery');


function my_post_gallery($atts) {

	$atts =
	shortcode_atts(
			[
					'ids'       => '',
					'album_name' => 'album',
			],
			$atts
	);
	
	$ids     = $atts['ids'];
	
	$albumName = $atts['album_name'];
	
	$imgIds     = is_string($ids) ? array_map('trim', explode(',', $ids)):$ids;
	
	$output="";
	
	
	$imgObjectArray = array();
	
	foreach ($imgIds as $imgId)
	{
		$img=get_post($imgId);
		
		$imgObject = parseImgToObject($img);
	    
		$imgObjectArray[] = $imgObject;
	  
	}
	
	if(count($imgObjectArray)>0){
		
		$albumId = uniqid();
		
		$imgHandlerObject = new imgHandlerClass($albumId, $albumName, $imgObjectArray);
		
		$output.=$imgHandlerObject->createAlbumTrigger().$imgHandlerObject->createAlbumContent();
		
	}else{
		$output.="No images found!";
	}
	
	
	
	
	return $output;

}





?>