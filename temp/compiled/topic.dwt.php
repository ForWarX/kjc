




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />

<title><?php echo $this->_var['topic']['title']; ?>_<?php echo $this->_var['page_title']; ?></title>



<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link href="<?php echo $this->_var['ecs_css_path']; ?>" rel="stylesheet" type="text/css" />
<?php if ($this->_var['topic']['css'] != ''): ?>
<style type="text/css">
  <?php echo $this->_var['topic']['css']; ?>
</style>
<?php endif; ?>
<style type="text/css">
h6{
font-family:"黑体";
background:url(<?php echo $this->_var['title_pic']; ?>) repeat-x 0 0;
text-align:left;
height:38px;
line-height:38px;
padding-left:20px;
font-weight:200;
font-size:18px;
color:#fff;
}
.goodsbox{
margin:5px;
background:#fff;
border:1px solid <?php echo $this->_var['base_style']; ?>;
width:170px;
min-height:1px;
display: -moz-inline-stack;
display: inline-block;
text-align:center;
vertical-align: top;
zoom:1;
*display:inline;
_height:1px;
}
  .goodsbox .imgbox{
	width:170px;
	margin:0 0 5px 0;
	overflow:hidden;
	} 
.sort_box{
border:1px solid #ccc;
background:#f5f5f5;
padding:10px 0 10px 10px;
}	
.sort_box a{
color:#222;
}
</style>

<?php echo $this->smarty_insert_scripts(array('files'=>'common.js,transport.js')); ?>
</head>
<body><?php echo $this->fetch('library/page_header.lbi'); ?><?php echo $this->fetch('library/ur_here.lbi'); ?><div>

<?php if ($this->_var['topic']['htmls'] == ''): ?>
  <script language="javascript">
	var topic_width  = "960";
	var topic_height = "300";
	var img_url      = "<?php echo $this->_var['topic']['topic_img']; ?>";
	
	if (img_url.indexOf('.swf') != -1)
	{
		document.write('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="'+ topic_width +'" height="'+ topic_height +'">');
		document.write('<param name="movie" value="'+ img_url +'"><param name="quality" value="high">');
		document.write('<param name="menu" value="false"><param name=wmode value="opaque">');
		document.write('<embed src="'+ img_url +'" wmode="opaque" menu="false" quality="high" width="'+ topic_width +'" height="'+ topic_height +'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent"/>');
		document.write('</object>');
	}
	else
	{
		document.write('<img width="' + topic_width + '" height="' + topic_height + '" border="0" src="' + img_url + '">');
	}
  </script>
<?php else: ?>
	<?php echo $this->_var['topic']['htmls']; ?>
<?php endif; ?>

<?php if ($this->_var['topic']['intro'] != ''): ?>
 <?php echo $this->_var['topic']['intro']; ?>
 <br /><br />
<?php endif; ?>
   
		<?php if ($this->_var['topic']['title_pic'] == ''): ?>
    
     <?php $_from = $this->_var['sort_goods_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sort_name', 'sort');if (count($_from)):
    foreach ($_from AS $this->_var['sort_name'] => $this->_var['sort']):
?>
    <div class="box">
    <div class="box_1 clearfix">
     <h3><span><?php echo $this->_var['sort_name']; ?></span></h3>
    <div class="centerPadd">
    <?php $_from = $this->_var['sort']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
    <div class="goodsItem">
       <a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['goods_thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>" class="goodsimg" /></a><br />
       <p><a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><?php echo $this->_var['goods']['short_style_name']; ?></a></p>
       <font class="f1">
       <?php if ($this->_var['goods']['promote_price'] != ""): ?>
      <?php echo $this->_var['goods']['promote_price']; ?>
      <?php else: ?>
      <?php echo $this->_var['goods']['shop_price']; ?>
      <?php endif; ?>
       </font>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
    </div>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <?php else: ?>
		
		
		 <?php $_from = $this->_var['sort_goods_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sort_name', 'sort');if (count($_from)):
    foreach ($_from AS $this->_var['sort_name'] => $this->_var['sort']):
?>
    <div class="clearfix">
    <h6><?php echo $this->_var['sort_name']; ?></h6>
		<div class="sort_box">
    <?php $_from = $this->_var['sort']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
    <div class="goodsbox">
       <div class="imgbox"><a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['goods_thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>" /></a></div>
       <a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><?php echo $this->_var['goods']['short_style_name']; ?></a><br />
       <?php if ($this->_var['goods']['promote_price'] != ""): ?>
       <?php echo $this->_var['goods']['promote_price']; ?><br />
       <?php else: ?>
       <?php echo $this->_var['goods']['shop_price']; ?><br />
       <?php endif; ?>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
    </div>

    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

  
    <?php endif; ?>    
</div><?php echo $this->fetch('library/page_footer.lbi'); ?></body>
</html>