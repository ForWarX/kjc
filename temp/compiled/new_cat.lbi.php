
<div class="cat-tree">

<?php $_from = $this->_var['all-categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');if (count($_from)):
    foreach ($_from AS $this->_var['cat']):
?>
<div class="cat-tree-inner">
<div class="cat-tree-left">
<a href="<?php echo $this->_var['cat']['url']; ?>"><span><?php echo htmlspecialchars($this->_var['cat']['name']); ?></span></a>
</div>
<div class="cat-tree-right">
	<ul>
		<?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
		
		<li onmouseover="this.className='sub-cat-open'" onmouseout="this.className=''"><a href="<?php echo $this->_var['child']['url']; ?>"><?php echo htmlspecialchars($this->_var['child']['name']); ?></a>
		
		<div class="sub-cat">
			<div class="sub-cat-wrap">
				<dl>
					<?php $_from = $this->_var['child']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'grandchild');if (count($_from)):
    foreach ($_from AS $this->_var['grandchild']):
?>
                    <dd> <a href="<?php echo $this->_var['grandchild']['url']; ?>"><?php echo htmlspecialchars($this->_var['grandchild']['name']); ?></a> </dd>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</dl>
			</div>
		</div>
		</li>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		<li></li>
	</ul>	
</div>
</div>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
</div>