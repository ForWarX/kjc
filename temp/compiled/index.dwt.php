<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />

<title><?php echo $this->_var['page_title']; ?></title>



<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link href="<?php echo $this->_var['ecs_css_path']; ?>" rel="stylesheet" type="text/css" />
<link rel="alternate" type="application/rss+xml" title="RSS|<?php echo $this->_var['page_title']; ?>" href="<?php echo $this->_var['feed_url']; ?>" />

<?php echo $this->smarty_insert_scripts(array('files'=>'common.js,index.js')); ?>
</head>
<body><?php echo $this->fetch('library/page_header.lbi'); ?><?php echo $this->fetch('library/index_ad.lbi'); ?><div class="block clearfix">




<div class="AreaL">
<?php echo $this->fetch('library/new_articles.lbi'); ?>



<?php $this->assign('ads_id','2'); ?><?php $this->assign('ads_num','3'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>


<?php 
$k = array (
  'name' => 'ads',
  'id' => '22',
  'num' => '1',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>

    

</div>

<div class="AreaR">


  
  


<?php echo $this->fetch('library/recommend_new.lbi'); ?>
 <?php echo $this->fetch('library/recommend_hot.lbi'); ?>
 <?php echo $this->fetch('library/recommend_best.lbi'); ?>



    <div class="blank"></div> 
</div>

<?php echo $this->fetch('library/twelveads.lbi'); ?>
<div class="blank"></div>

<?php echo $this->fetch('library/ten_ads_section.lbi'); ?>

<?php echo $this->fetch('library/vertical_promo.lbi'); ?>
<div class="blank"></div>

<?php echo $this->fetch('library/our_advantage.lbi'); ?>




</div>
  
  




    <?php echo $this->fetch('library/help.lbi'); ?><?php echo $this->fetch('library/page_footer.lbi'); ?>
<script type="text/javascript" src="themes/ningbo/js/jquery.Xslider.js"></script>
<script>
	$(document).ready(function(){
		if ($('#this_region').text() == '天津'){
			alert("天津的顾客朋友您好，现在为您跳转至美市库...");
			window.location.replace("http://www.americosale.com");
		}
	});
</script>
</body>
</html>
