<?php if ($this->_var['goods']): ?>
<div class="cat_ul" onMouseOver="this.className='cat_ul on'" onMouseOut="this.className='cat_ul'">
	<div class="clearfix divt">
		<p class="clearfix ptt">
			<span class="cart_sp">
            	<em class="left">购物车：<span id="qty_on_top"><?php echo $this->_var['goods_number']; ?> </span>件</em>
                <em class="right"><span id="amount_on_top"> $<?php echo $this->_var['order_amount']; ?></span> 元</em>
            </span>
		</p>
     </div>
	<ul class="cart_box clearfix">
		<?php $_from = $this->_var['goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'goods_0_25950600_1481915980');$this->_foreach['goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['goods_0_25950600_1481915980']):
        $this->_foreach['goods']['iteration']++;
?>
		<li <?php if ($this->_var['key'] % 2 != 0): ?>class="clearfix nobk"<?php else: ?> class="clearfix"<?php endif; ?>>
			<span class="sgood left">
            	<a href="<?php echo $this->_var['goods_0_25950600_1481915980']['url']; ?>" >
                	<img src="<?php echo $this->_var['goods_0_25950600_1481915980']['goods_thumb']; ?>" width="50px" alt="<?php echo $this->_var['goods_0_25950600_1481915980']['goods_name']; ?>">
                </a>
            </span>
			<p class="left sgoodc">
            	<a class="name" href="<?php echo $this->_var['goods_0_25950600_1481915980']['url']; ?>"><?php echo $this->_var['goods_0_25950600_1481915980']['short_name']; ?></a>
				<span><?php echo $this->_var['goods_0_25950600_1481915980']['goods_jj']; ?></span>
			</p>
			<p class="right sgoodt">
				<span><span id="unit_price"><?php echo $this->_var['goods_0_25950600_1481915980']['goods_price']; ?></span><span id="times"><br/>×</span><span id="quantity"><?php echo $this->_var['goods_0_25950600_1481915980']['goods_number']; ?></span></span>
                <br/><a class="del" href="javascript:" onClick="deleteCartGoods(<?php echo $this->_var['goods_0_25950600_1481915980']['rec_id']; ?>)">[删除]</a>
            </p>
		</li>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		<li class="clearfix zllcart">
			<span class="ie6left">共 
            	<em><?php echo $this->_var['goods_number']; ?></em> 件 
                <span class="pipe">|</span>
                 价格总计:
                 <em> $<?php echo $this->_var['order_amount']; ?> </em><span>元</span>
            </span>
            <a href="flow.php" class="right">去购物车结算>></a>
		</li>
	</ul>
</div>
<?php else: ?>
<ul class="car_ul onp clearfix">
	<p class="clearfix ptt">购物车内暂无商品</p>
<ul>
<?php endif; ?>
