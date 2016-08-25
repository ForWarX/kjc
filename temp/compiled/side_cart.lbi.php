<?php if ($this->_var['goods1']): ?>
		<p class="good_cart"><?php echo $this->_var['goods_number1']; ?></p>
		<span class="fixeBoxSpan"></span>
		<strong>购物车</strong>
		<div class="cartBox">
			<div class="bjfff"></div>
		
			<div class="cartBoxC ">
				<ul>
					<?php $_from = $this->_var['goods1']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'goods1_0_13835300_1430172424');$this->_foreach['goods1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods1']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['goods1_0_13835300_1430172424']):
        $this->_foreach['goods1']['iteration']++;
?>
					<li>
						<div class="p-img">
							<a href="<?php echo $this->_var['goods1_0_13835300_1430172424']['url']; ?>" target="_blank"><img src="<?php echo $this->_var['goods1_0_13835300_1430172424']['goods_thumb']; ?>"> </a>
						</div>
						<div class="p-name">
							<a href="<?php echo $this->_var['goods1_0_13835300_1430172424']['url']; ?>" target="_blank"><?php echo $this->_var['goods1_0_13835300_1430172424']['short_name']; ?></a> 
						</div>
						<div class="p-detail"> 
							<span class="p-price"> 			<strong><?php echo $this->_var['goods1_0_13835300_1430172424']['goods_price']; ?></strong> × <?php echo $this->_var['goods1_0_13835300_1430172424']['goods_number']; ?> 
							</span><a class="del" href="javascript:" onClick="deleteCartGoods(<?php echo $this->_var['goods1_0_13835300_1430172424']['rec_id']; ?>)">[删除]</a>
						</div>
					</li>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</ul>
			</div>
		
			<div class="cartBoxFoot">
				<span>小计(不含运费)：</span>
				<em>￥</em> 
				<strong><?php echo $this->_var['order_amount1']; ?></strong> 
				<a href="flow.php" id="btn-payforgoods">去购物车结算</a> 
			</div>
		
		</div>
			
<?php else: ?>
		<p class="good_cart">0</p>
		<span class="fixeBoxSpan"></span> 
		<strong>购物车</strong>
		<div class="cartBox">
			<div class="bjfff"></div>
			<div class="message">购物车内暂无商品，赶紧选购吧</div>
		</div>
            
<?php endif; ?>