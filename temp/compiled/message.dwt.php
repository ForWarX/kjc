<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<?php if ($this->_var['auto_redirect']): ?>
<meta http-equiv="refresh" content="3;URL=<?php echo $this->_var['message']['back_url']; ?>" />
<?php endif; ?>


<?php if ($this->_var['message']['content'] != $this->_var['lang']['ws_user_rank']): ?>
<title><?php echo $this->_var['page_title']; ?></title>
<?php else: ?>
<title>申请成为B2B会员</title>
<?php endif; ?>

<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link href="<?php echo $this->_var['ecs_css_path']; ?>" rel="stylesheet" type="text/css" />

<?php echo $this->smarty_insert_scripts(array('files'=>'common.js,user.js,transport.js')); ?>
<style type="text/css">
p a{color:#006acd; text-decoration:underline;}
</style>
</head>
<body><?php echo $this->fetch('library/page_header.lbi'); ?><div class="blank"></div>
<div class="block">
  <div class="box">
   <div class="box_1">
   	<?php if ($this->_var['message']['content'] != $this->_var['lang']['ws_user_rank']): ?>
    <h3><span><?php echo $this->_var['lang']['system_info']; ?></span></h3>
    <?php endif; ?>
    <div class="boxCenterList RelaArticle" align="center">
      <div style="margin:20px auto;">
      <?php if ($this->_var['message']['content'] != $this->_var['lang']['ws_user_rank']): ?>
      <p style="font-size: 14px; font-weight:bold; color: red;"><?php echo $this->_var['message']['content']; ?></p>
      <?php else: ?>
      
    
        <?php echo $this->smarty_insert_scripts(array('files'=>'utils.js')); ?>
        <div class="usBox">
          <div class="usBox_2 clearfix">
           <div class="logtitle3b"></div>
            <form action="wholesale.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
              <table width="100%"  border="0" align="left" cellpadding="5" cellspacing="3">
                <tr>
                  <td width="13%" align="right">姓名</td>
                  <td width="87%">
                  <input name="name" type="text" size="25" id="name" class="inputBg"/>
                    <span id="name_notice" style="color:#EF5A2C"> *</span>
                  </td>
                </tr>
                <tr>
                  <td align="right">公司名称</td>
                  <td>
                  <input name="company" type="text" size="25" id="company" class="inputBg"/>
                    <span id="company_notice" style="color:#EF5A2C"> *</span>
                  </td>
                </tr>
                <tr>
                  <td align="right">职位</td>
                  <td>
                  <input name="position" type="text" size="25" id="position" class="inputBg"/>
                    <span style="color:#EF5A2C" id="position_notice"> *</span>
                  </td>
                </tr>
                <tr>
                  <td align="right">电话</td>
                  <td>
                  <input name="phone" type="text" size="25" id="phone" class="inputBg"/>
                    <span style="color:#EF5A2C" id="email_notice"> *</span>
                  </td>
                </tr>
                <tr>
                  <td align="right">Email</td>
                  <td>
                  <input name="email" type="text" size="25" id="email" class="inputBg"/>
                    <span style="color:#EF5A2C" id="email_notice"> *</span>
                  </td>
                </tr>
                <tr>
                  <td align="right">营业执照</td>
                  <td>
                  <input type="file" name="yyzz" id="yyzz" accept="image/*" size="35"/>
                  </td>
                </tr>
                <tr>
                  <td align="right">组织机构代码证</td>
                  <td>
                  <input type="file" name="zzjgdmz" id="zzjgdmz" accept="image/*" size="35"/>
                  </td>
                </tr>
                <tr>
                  <td align="right">税务登记证</td>
                  <td>
                  <input type="file" name="swdjz" id="swdjz" accept="image/*" size="35"/>
                  </td>
                </tr>
                <tr>
                  <td align="right">求购商品</td>
                  <td>
                  <textarea name="demand" class="inputBg" style="height:50px; width:165px;"></textarea>
                    <span style="color:#EF5A2C" id="demand_notice"> *</span>
                  </td>
                </tr><!--
              <tr>
              <td align="right"><?php echo $this->_var['lang']['comment_captcha']; ?></td>
              <td><input type="text" size="8" name="captcha" class="inputBg" />
              <img src="captcha.php?<?php echo $this->_var['rand']; ?>" alt="captcha" style="vertical-align: middle;cursor: pointer;" onClick="this.src='captcha.php?'+Math.random()" /> </td>
              </tr>-->
                <tr>
                  <td>&nbsp;</td>
                  <td align="left">
                  
          		  <input name="act" type="hidden" value="submit_user" >
                  <input name="" type="submit" value="提交申请" onclick="return validateForm()">
                  <script>
					function validateForm(){
						var yyzz = document.getElementById("yyzz").value;
						var zzjgdmz = document.getElementById("zzjgdmz").value;
						var swdjz = document.getElementById("swdjz").value;
						if (yyzz =='')
						{
							return false;
						}
						if(zzjgdmz =='')
						{
							return false;
						}
						if(swdjz =='')
						{
							return false;
						}  
						else 
						{
							return true;
						} 
						return false;
					}
				</script>
                  </td>
                </tr>
                
                <tr>
                  <td></td>
                  <td align="left"><a href="/store/user.php">已是B2B会员？请点此登录</a></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table>
            </form>
          </div>
        </div>
        
      
      <?php endif; ?>
        
        <div class="blank"></div>
        <?php if ($this->_var['message']['url_info']): ?>
          <?php $_from = $this->_var['message']['url_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('info', 'url');if (count($_from)):
    foreach ($_from AS $this->_var['info'] => $this->_var['url']):
?>
          <p><a href="<?php echo $this->_var['url']; ?>"><?php echo $this->_var['info']; ?></a></p>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php endif; ?>
      </div>
    </div>
   </div>
  </div>
</div><?php echo $this->fetch('library/page_footer.lbi'); ?></body>
</html>
