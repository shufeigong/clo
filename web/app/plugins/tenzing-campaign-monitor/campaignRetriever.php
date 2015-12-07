<a href="/">Back to archives</a>
<?php 
    $campaignUrl=$_GET['campaignURL'];
    $urlList=explode('r/', $campaignUrl);
    $sitemapUrl=$urlList[1];
    
    if(strlen($sitemapUrl)>49){
    	$my_var = file_get_contents($_GET['campaignURL']);
    	print($my_var);
    }else{
    	$sitemapUrl=substr($sitemapUrl, 0, 32) . '/' . substr($sitemapUrl, 32);
    	
    	$campaignUrl=$urlList[0].'r/'.$sitemapUrl;
    	$my_var = file_get_contents($campaignUrl);
    	print($my_var);
    }
    
    
 ?>