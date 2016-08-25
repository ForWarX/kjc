<?php if ($this->_var['best_goods']): ?>
<?php if ($this->_var['cat_rec_sign'] != 1): ?>
<div class="box">

 
  
  <div class="tit1 tit2">
    <img src="themes/ningbo/images/index-hot.jpg" alt=""/>
        <a class="more" href="search.php?intro=hot">更多</a> 
      </div>
  
  <div class="blank"></div>
  
  
  <div id="show_best_area" class="clearfix">
  <?php endif; ?>
  <script type="text/javascript">
//SYNTAX: ddtabmenu.definemenu("tab_menu_id", integer OR "auto")
ddtabmenu.definemenu("best-cat", 0) //initialize Tab Menu #3 with 2nd tab selected
</script>

<div id="best-cat" class="solidblockmenu">
<ul>
<li><a href="" rel="best-cat-1" class="current" style="
    width: 105px;
">饮料-水</a></li>
<li><a href="" rel="best-cat-2" class="" style="
    width: 105px;
">葡萄酒</a></li>

</ul>
</div>

<DIV class="tabcontainer ieclass">

<div id="best-cat-1" class="tabcontent">
	<div class="best-right">
  <?php $_from = $this->_var['best_goods_90']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
  <div class="goodsItem3">
  	<div class="best-good-img"><a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>" class="goodsimg" /></a></div>
    <div class="best-good-info"><ul><li class="name" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><a href="<?php echo $this->_var['goods']['url']; ?>"><?php echo $this->_var['goods']['short_style_name']; ?></a></li><li class="price"> <?php if ($this->_var['goods']['promote_price'] != ""): ?><?php echo $this->_var['goods']['promote_price']; ?><?php else: ?><?php echo $this->_var['goods']['shop_price']; ?><?php endif; ?></li><li class="market">市场价：</li><li><a href="<?php echo $this->_var['goods']['url']; ?>"><img src="themes/ningbo/images/btn_nowbuy.png"/></a></li></ul></div>
        </div>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
 </div>
	
</div>
<div id="best-cat-2" class="tabcontent">
<div class="best-right">
  <?php $_from = $this->_var['best_goods_86']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
  <div class="goodsItem3">
  	<div class="best-good-img"><a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>" class="goodsimg" /></a></div>
    <div class="best-good-info"><ul><li class="name" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><a href="<?php echo $this->_var['goods']['url']; ?>"><?php echo $this->_var['goods']['short_style_name']; ?></a></li><li class="price"> <?php if ($this->_var['goods']['promote_price'] != ""): ?><?php echo $this->_var['goods']['promote_price']; ?><?php else: ?><?php echo $this->_var['goods']['shop_price']; ?><?php endif; ?></li><li class="market">市场价：</li><li><a href="<?php echo $this->_var['goods']['url']; ?>"><img src="themes/ningbo/images/btn_nowbuy.png"/></a></li></ul></div>
        </div>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
 </div>
</div>                      
</div>
  
  <!--<div class="best-left hot-big"><img src="themes/ningbo/images/guanggao/infant.jpg" alt=""/></div>-->
  
  <?php if ($this->_var['cat_rec_sign'] != 1): ?>
  </div>

</div>
<div class="blank"></div>
  <?php endif; ?>
<?php endif; ?>
