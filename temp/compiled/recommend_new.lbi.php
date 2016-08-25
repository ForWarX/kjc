<?php if ($this->_var['new_goods']): ?>
<?php if ($this->_var['cat_rec_sign'] != 1): ?>
<script type="text/javascript">
$(document).ready(function(){
 $("span.div").hide();
 $(".sis-li li").hover(function(){
  $("span.div",this).slideToggle(500);
 });
 
 $(".imgtext").hide();
 $(".zzsc").hover(function(){
  $(".imgtext",this).slideToggle(500);
 });
});
</script>


<div class="box">
 
  <div class="tit1">
    <img src="themes/ningbo/images/index-new.jpg" alt=""/>
   
 <a class="more" href="search.php?intro=new">更多</a> 
  </div>
     <div class="tit1_banner">
     

<?php 
$k = array (
  'name' => 'ads',
  'id' => '5',
  'num' => '3',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>




<script type="text/javascript" src="themes/ningbo/js/ddtabmenu.js"></script>


<link rel="stylesheet" type="text/css" href="themes/ningbo/solidblocksmenu.css" />
<style type="text/css">
#n1 {
}
</style>
<script type="text/javascript">
//SYNTAX: ddtabmenu.definemenu("tab_menu_id", integer OR "auto")
ddtabmenu.definemenu("ddtabs3", 0) //initialize Tab Menu #3 with 2nd tab selected
</script>

<div id="ddtabs3" class="solidblockmenu">
<ul style="
    margin-top: 7px;
">
<li><a href="" rel="sb1" class="current" style="
    width: 105px;
">食品-坚果</a></li>
<li><a href="" rel="sb2" class="" style="
    width: 105px;
">食品-果干</a></li>

</ul>
</div>

<DIV class="tabcontainer ieclass">

<div id="sb1" class="tabcontent">
<div id="new-scroller-1" class="img-scroller">
       <span class="prev"></span>
        <span class="next"></span>
         <div id="new-lister-1" class="img-lister">
         <ul style="margin-left: 0px;">
<?php $_from = $this->_var['new_goods_103']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_103');if (count($_from)):
    foreach ($_from AS $this->_var['goods_103']):
?>
<li>
<div class="goodsItem2">
<div class="good-img">        
<a href="<?php echo $this->_var['goods_103']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_103']['name']); ?>"><img src="<?php echo $this->_var['goods_103']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods_103']['name']); ?>" class="goodsimg" width="118px" /></a><?php if ($this->_var['is_kj_103']): ?><img src="themes/ningbo/images/kj_logo/kj85.png" class="kj-img"/><?php endif; ?></div>
<div class="good-name"><a href="<?php echo $this->_var['goods_103']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_103']['name']); ?>"><?php echo $this->_var['goods_103']['short_style_name']; ?></a></div>
      
           <div class="good-price"><font class="f1">
           <?php if ($this->_var['goods_103']['promote_price'] != ""): ?>
          <?php echo $this->_var['goods_103']['promote_price']; ?>
          <?php else: ?>
          <?php echo $this->_var['goods_103']['shop_price']; ?>
          <?php endif; ?>
           </font></div>
    </div>
    </li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>
      </div>                       
</div>


<div id="sb2" class="tabcontent">
	<div id="new-scroller-2" class="img-scroller">
       <span class="prev"></span>
        <span class="next"></span>
         <div id="new-lister-2" class="img-lister">
         <ul style="margin-left: 0px;">
        <?php $_from = $this->_var['new_goods_104']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_104');if (count($_from)):
    foreach ($_from AS $this->_var['goods_104']):
?>
        <li>
        <div class="goodsItem2">
        <div class="good-img">        
        <a href="<?php echo $this->_var['goods_104']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_104']['name']); ?>"><img src="<?php echo $this->_var['goods_104']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods_104']['name']); ?>" class="goodsimg" width="118px" /></a><?php if ($this->_var['is_kj_104']): ?><img src="themes/ningbo/images/kj_logo/kj85.png" class="kj-img"/><?php endif; ?></div>
        <div class="good-name"><a href="<?php echo $this->_var['goods_104']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_104']['name']); ?>"><?php echo $this->_var['goods_104']['short_style_name']; ?></a></div>
              
                   <div class="good-price"><font class="f1">
                   <?php if ($this->_var['goods_104']['promote_price'] != ""): ?>
                  <?php echo $this->_var['goods_104']['promote_price']; ?>
                  <?php else: ?>
                  <?php echo $this->_var['goods_104']['shop_price']; ?>
                  <?php endif; ?>
                   </font></div>
            </div>
            </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
        </div>
              </div>  
</div>

</DIV>



        </div>
  <div class="blank"></div>
  <div id="show_new_area" class="clearfix">
  <?php endif; ?>
 
  <?php if ($this->_var['cat_rec_sign'] != 1): ?>
  </div>

</div>
<div class="blank"></div>
  <?php endif; ?>
<?php endif; ?>

<script type="text/javascript">
 function DY_scroll(wraper,prev,next,img,speed,or)
 { 
  var wraper = $(wraper);
  var prev = $(wraper).find(prev);
  var next = $(wraper).find(next);
  var img = $(img).find('ul');
  var w = img.find('li').outerWidth(true);
  var s = speed;
  next.click(function()
       {
        img.animate({'margin-left':-w},function()
                  {
                   img.find('li').eq(0).appendTo(img);
                   img.css({'margin-left':0});
                   });
        });
  prev.click(function()
       {
        img.find('li:last').prependTo(img);
        img.css({'margin-left':-w});
        img.animate({'margin-left':0});
        });
  if (or == true)
  {
   ad = setInterval(function() {next.click();},s*1000);
   wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() {next.click();},s*1000);});

  }
 }
 DY_scroll('#new-scroller-1','.prev','.next','#new-lister-1',3,false);// true为自动播放，不加此参数或false就默认不自动
 DY_scroll('#new-scroller-2','.prev','.next','#new-lister-2',3,false);
 </script>

