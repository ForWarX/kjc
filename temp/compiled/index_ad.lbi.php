

<?php if ($this->_var['index_ad'] == 'sys'): ?>
<script type="text/javascript">
var swf_width=760;
var swf_height=420;
</script>
<script type="text/javascript" src="data/flashdata/<?php echo $this->_var['flash_theme']; ?>/cycle_image.js"></script>
<?php elseif ($this->_var['index_ad'] == 'cus'): ?>
<?php if ($this->_var['ad']['ad_type'] == 0): ?>
<a href="<?php echo $this->_var['ad']['url']; ?>" target="_blank"><img src="<?php echo $this->_var['ad']['content']; ?>" width="760" height="420" border="0"></a>
<?php elseif ($this->_var['ad']['ad_type'] == 1): ?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="760" height="420">
<param name="movie" value="<?php echo $this->_var['ad']['content']; ?>" />
<param name="quality" value="high" />
<embed src="<?php echo $this->_var['ad']['content']; ?>" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="760" height="420"></embed>
</object>
<?php elseif ($this->_var['ad']['ad_type'] == 2): ?>
<div class="pb_slider_box" id="pb_slider">
  <div class="pb_slider_con_box">
  	<?php $_from = $this->_var['playerdb']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
    	<div><a style="background: url('<?php echo $this->_var['item']['src']; ?>') no-repeat scroll center center transparent; position: absolute; z-index: 9; opacity: 1;" href="<?php echo $this->_var['item']['url']; ?>" title="<?php echo $this->_var['item']['text']; ?>" target="_blank"></a></div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>		 
  </div>
  <div class="pb_slider_switcher">
  	<?php $_from = $this->_var['playerdb']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
  		<a class="cur" href="<?php echo $this->_var['item']['url']; ?>" title="<?php echo $this->_var['item']['text']; ?>" target="_blank"></a>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>	     
   </div>
</div>
<script type="text/javascript">
<?php echo $this->_var['ad']['content']; ?>
</script>
<?php elseif ($this->_var['ad']['ad_type'] == 3): ?>
<a href="<?php echo $this->_var['ad']['url']; ?>" target="_blank"><?php echo $this->_var['ad']['content']; ?></a>
<?php endif; ?>
<?php else: ?>
<?php endif; ?>
<br/>
