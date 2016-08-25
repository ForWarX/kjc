
<div style="float:left">
<?php if ($this->_var['user_info']): ?>

<span>亲爱的 <span class="f7_b"><b class="f7"><?php echo $this->_var['user_info']['username']; ?></b></span> ，欢迎来到跨境城！&nbsp;</span>    
[ <a href="user.php"><?php echo $this->_var['lang']['user_center']; ?></a> /  
 <a href="user.php?act=logout"><?php echo $this->_var['lang']['user_logout']; ?></a>  ] 

<?php else: ?>
<span><?php echo $this->_var['lang']['hello']; ?>，欢迎来到跨境城！ &nbsp;| </span>
  <a href="user.php">请登录</a> | 
 <a href="user.php?act=register">免费注册</a> |  
<?php endif; ?>

 
</div>