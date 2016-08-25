<link href="themes/ningbo/qq/images/qq.css" rel="stylesheet" type="text/css" />
<link href="themes/ningbo/style2.css" type="text/css" rel="stylesheet">
<!--<script language='javascript' src='themes/ningbo/qq/ServiceQQ.js' type='text/javascript' charset='utf-8'></script>-->
<script type="text/javascript">
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js,jquery.json.js')); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'transport.js,region.js')); ?>
<div class="header">
<div class="topNav ">
<div id="topNavContent" align="center">
	<span style="font-size:16px;float:left;"><span id="this_region" ><?php echo $this->_var['this_region']; ?></span><a id="city_switch" style="cursor:pointer;"><span style="font-size:12px;">[切换城市]</span></a></span><span id="region_switch"></span>
	<span style="float:left;">| 客服热线：<span style="color:#f45f53;font-weight:bold;"><?php if ($this->_var['service_phone']): ?>
<?php echo $this->_var['service_phone']; ?> <?php endif; ?></span></span>
			<div class="cart" id="ECS_CARTINFO">
			<?php 
$k = array (
  'name' => 'cart_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
            </div>
            
		   <div class="f_r log">
  <ul class="ul1" onmouseover="this.className='ul1 ul1_on'" onmouseout="this.className='ul1'">
 <a class="a1" href="user.php">我的账户</a>
  <div class="ul1_float">
  <ul> 
     <a href="user.php?act=order_list">我的订单</a>
     <a href="user.php?act=collection_list">我的收藏</a>
     <a href="user.php?act=profile">用户信息</a>
     <a href="user.php?act=address_list">收货地址</a>
     <?php if ($this->_var['cfg']['use_integral']): ?>
     <a href="user.php?act=pcoin_deposit"><img src="themes/ningbo/images/pcoin.gif" width="12px">币充值</a>
     <?php endif; ?>    
 </ul>    
  </div>
    <div class="dang"></div>
   
 </ul>
 </div>
           <div class="f_r" style=" margin-top:0;_margin-top:7px;">
              <?php echo $this->smarty_insert_scripts(array('files'=>'utils.js')); ?>
               <font id="ECS_MEMBERZONE"><?php 
$k = array (
  'name' => 'member_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?> </font>
                  <?php if ($this->_var['navigator_list']['top']): ?>
                  <?php $_from = $this->_var['navigator_list']['top']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['nav_top_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_top_list']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['nav_top_list']['iteration']++;
?>  
                  <a href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?> target="_blank" <?php endif; ?>><?php echo $this->_var['nav']['name']; ?></a>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> <?php endif; ?>
          </div>
          </div>
                   </div>
<div class="block" style=" position:relative; z-index:9999;">

<?php 
$k = array (
  'name' => 'ads',
  'id' => '13',
  'num' => '1',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>

<div style="height: 60px;padding: 15px 0;">
<div style="position:absolute;"><a href="index.php"><img src="themes/ningbo/images/logo.png" width="230px"></a></div>

<div style="left: 22%;position: absolute;"><img src="themes/ningbo/images/kj_mark.jpg"></div>

<?php echo $this->fetch('/library/change_city2.lbi'); ?>

<div id="search"  style="position:absolute; right:0px;">
   
  <form id="searchForm" name="searchForm" method="get" action="search.php" onSubmit="return checkSearchForm()"  >
  <div class="B_input_box">
   <input name="keywords" type="text" id="keyword" value="搜索  商品" onclick="javascript:this.value=''" class="B_input"/>
   </div>
   
   <input type="hidden" id="sc_ds" value="1" name="sc_ds">
   <?php if ($this->_var['is_kj'] == 1): ?>
   <input type="hidden" id="category" value="78" name="category">
   <?php elseif ($this->_var['is_xp'] == 1): ?>
   <input type="hidden" id="category" value="77" name="category">
   <?php elseif ($this->_var['is_cn'] == 1): ?>
   <input type="hidden" id="category" value="122" name="category">
   <?php else: ?>
   <input type="hidden" id="category" value="0" name="category">
   <?php endif; ?>
   <input type="hidden" id="brand" value="0" name="brand">
   <input id="min_price" type="hidden" value="0" name="min_price">
   <input id="max_price" type="hidden" value="0" name="max_price">
   <input type="hidden" id="outstock" value="0" name="outstock">
   <input type="hidden" value="form" name="action">
   
   
   
   <input name="imageField" type="submit" value="搜索" class="go" style="cursor:pointer;padding:0;" />
   <div class="hot-search">
   	<ul>
    	<li><a href="search.php?keywords=坚果&imageField=搜索">坚果</a></li>
        <li><a href="search.php?keywords=果汁&imageField=搜索">果汁</a></li>
        <li><a href="search.php?keywords=蜂蜜&imageField=搜索">蜂蜜</a></li>
        <li><a href="search.php?keywords=尿不湿&imageField=搜索">尿不湿</a></li>
        <li><a href="search.php?keywords=婴儿湿巾&imageField=搜索">婴儿湿巾</a></li>
        <li><a href="search.php?keywords=冰酒&imageField=搜索">冰酒</a></li>
        <li><a href="search.php?keywords=枫糖浆&imageField=搜索">枫糖浆</a></li>
        <li><a href="search.php?keywords=饼干&imageField=搜索">饼干</a></li>
        <li><a href="search.php?keywords=巧克力&imageField=搜索">巧克力</a></li>
     </ul>
    </div>
  
   </form>
   
</div>
</div> 
 
 </div> 
<div class="g-menu">
<div class="menu-shadow-hack"></div>
  <div class="g-menu-wrap">
    <div class="all-catalog" >
      <a href="catalog.php" class="all-btn">所有商品分类<i class="arrow"></i></a>        
    <div class="IndexAreaL">
    <?php echo $this->fetch('/library/new_cat.lbi'); ?> 
  </div>
    </div>
	
  <div class="channel">
    <ul>
      <li  <?php if ($this->_var['navigator_list']['config']['index'] == 1): ?>  class="m-home" <?php endif; ?>> <a style="background:none;" href="index.php" ><?php echo $this->_var['lang']['home']; ?></a></li>
        <?php $_from = $this->_var['navigator_list']['middle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['no'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['no']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['no']['iteration']++;
?>
      <li <?php if ($this->_var['nav']['active'] == 1): ?>  class="m-home" <?php endif; ?>><a href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?>target="_blank" <?php endif; ?>    ><?php echo $this->_var['nav']['name']; ?></a>
      
     </li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>


</ul>

</div>

<div id="search-form">
					<form action="search.php" method="get" id="searchForm" name="searchForm">
						<input type="text" class="search-text-box" id="keywords" name="keywords" value="">
					</form>
				</div>
              <script type="text/javascript">
              	jQuery(document).ready(function(){
	
	jQuery('.search-text-box').click(function(){
		
		jQuery(this).animate({
		width:'140px',	
		},240,function(){
			jQuery('.search-text-box').delay(100).queue(function(next){
				jQuery('.search-text-box').css('color','#FFF');
				next();
			});
		});
		
	});
	
	jQuery(document).click(function(ev){
		
		var myID = ev.target.id;
		
		if(myID != 'keywords'){
			jQuery('.search-text-box').animate({
			width:'1px',
			
			},200,function(){
				
				
				jQuery('.search-text-box').css('color','transparent');
				
			
				
			});
		}
	});
	
});
              </script>
</div>
</div>
<script language="javascript">
	var obj11 = document.getElementsByClassName("g-menu")[0];
	var srch = document.getElementById("search-form");
	var top11 = getTop(obj11);
	
	var isIE6 = /msie 6/i.test(navigator.userAgent);
window.onscroll = function(){
var bodyScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
if (bodyScrollTop > top11){
	

obj11.style.position = (isIE6) ? "absolute" : "fixed";
//alert(bodyScrollTop);
obj11.style.top = (isIE6) ? (bodyScrollTop) + "px" : "0px";
	obj11.style.background = "none repeat scroll 0 0 rgba(249, 106, 100, 0.8)";
	srch.style.visibility = "visible";
	srch.style.opacity = "1";
	} else {
obj11.style.position = "inherit";
obj11.style.top = "155px";
obj11.style.zIndex = "9999";
srch.style.visibility = "hidden";
obj11.style.background = "none repeat scroll 0 0 rgba(249, 106, 100, 1)";
}
}
function getTop(e){
var offset = e.offsetTop;
if(e.offsetParent != null) offset += getTop(e.offsetParent);
return offset;
} 
</script>

<script>
$('#city_switch').click(function(){document.getElementById('region_switch').innerHTML = '<form enctype="multipart/form-data" action="" method="post" name="IndexForm2" autocomplete="off" style="position: absolute;top: 25px;background-color:#eee;padding:3px 20px 3px 5px;"><select name="province" onchange="region.changed(this, 2, \x27selCities\x27)"><option value="0">1.选择省份</option><?php echo $this->_var['provinces']; ?></select><select name="city" id="selCities" onChange="jump_to_city(this);"><option value="0">2.选择城市</option></select><span style="position:absolute;top:-3px;right:2px;cursor:pointer;" class="f7" onClick="document.getElementById(\x27region_switch\x27).style.display = \x27none\x27;">[x]</span></form>';document.getElementById("region_switch").style.display = "block";});
function jump_to_city(obj) {
  var region = obj.form.elements['city'].value;
  if(region != 0){
  	window.document.location.href= '?region=' + region;
  }

}

</script>
</div>
