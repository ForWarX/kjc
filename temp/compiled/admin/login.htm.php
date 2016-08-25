<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->_var['lang']['cp_home']; ?><?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?><?php endif; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<link href="styles/main.css" rel="stylesheet" type="text/css" />

<!--<style type="text/css">
body {
  color: white;
}
</style>-->
<style type="text/css">
input {
	-webkit-border-radius: 8px;
	-moz-border-radius: 8px;
	border-radius: 8px;
	
	border: 1px solid #CCCCCC;
	height: 30px;
	width: 200px;
}
.button{
	height: 19px;
	width: 100%;	
}
/*#remember{
	width: 10px;
	border-color:transparent;
	float:left;
}#forRemember{
	float:right;
	margin-right: 40px;
    margin-top: 10px;
	color:#58595B;
}*/
#code{
	float: right;
	border-color: #CCCCCC;
}
#enter{
	background:none;
	background-color:transparent;
	border-color:transparent;
	font-size:24px;
	width:160px;
	height:34px;
	font-family: 'Microsoft YaHei UI','Microsoft YaHei',DengXian,SimSun,'Segoe UI',Tahoma,Helvetica,sans-serif;
	color:#1CBCE0;
	float:left;
	margin-left: -9px;
}
</style>

<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>
<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

if (window.parent != window)
{
  window.top.location.href = location.href;
}

//-->
</script>
</head>
<!--<body style="background: #278296">-->
<body>
<form method="post" action="privilege.php" name='theForm' onsubmit="return validate()">
  <table cellspacing="0" cellpadding="0" style="margin-top: 100px;" align="center">
  <tr>
    <td align="center"><a href="../index.php" target="_blank"> <img src="images/login.png" border="0" alt="KJC"/></a></td>
   </tr>
   <tr> 
    <!--<td style="padding-left: 50px">-->
    <td align="center">
      <table width="200px">
      <tr>
        <!--<td><?php echo $this->_var['lang']['label_username']; ?></td>-->
        <td><input type="text" name="username" value="<?php echo $this->_var['lang']['label_username']; ?>" onfocus="if(value=='<?php echo $this->_var['lang']['label_username']; ?>'){value=''}" onblur="if(value==''){value='<?php echo $this->_var['lang']['label_username']; ?>'}" style="color:#808080"/></td>
      </tr>
      <tr>
        <!--<td><?php echo $this->_var['lang']['label_password']; ?></td>-->
        <td><input type="password" name="password" value="<?php echo $this->_var['lang']['label_password']; ?>" onfocus="if(value=='<?php echo $this->_var['lang']['label_password']; ?>'){value=''}" onblur="if(value==''){value='<?php echo $this->_var['lang']['label_password']; ?>'}" style="color:#808080"/></td>
      </tr>
      <?php if ($this->_var['gd_version'] > 0): ?>
      <tr>
        <!--<td><?php echo $this->_var['lang']['label_captcha']; ?></td>-->
        <td><input type="text" name="captcha" class="capital" value="<?php echo $this->_var['lang']['label_captcha']; ?>" onfocus="if(value=='<?php echo $this->_var['lang']['label_captcha']; ?>'){value=''}" onblur="if(value==''){value='<?php echo $this->_var['lang']['label_captcha']; ?>'}" style="color:#808080;width:100px;" align="left"/><img src="index.php?act=captcha&<?php echo $this->_var['random']; ?>" width="95px" height="30px" alt="CAPTCHA" border="1" onclick= this.src="index.php?act=captcha&"+Math.random() style="cursor: pointer;-webkit-border-radius: 8px;-moz-border-radius: 8px;border-radius: 8px;" title="<?php echo $this->_var['lang']['click_for_another']; ?>" id="code"/></td>
      </tr>
      <?php endif; ?>
      
      <tr><td><input type="submit" value="<?php echo $this->_var['lang']['signin_now']; ?>" class="button" id="enter"/></td></tr>
      <!--<tr><td><input type="checkbox" value="1" name="remember" id="remember"/><label for="remember" id="forRemember"><?php echo $this->_var['lang']['remember']; ?></label></td></tr>-->
      <tr>
        <td style="color:#58595B;" align="left">&raquo; <a href="../" style="color:#58595B;"><?php echo $this->_var['lang']['back_home']; ?></a> &raquo; <a href="get_password.php?act=forget_pwd" style="color:#58595B;"><?php echo $this->_var['lang']['forget_pwd']; ?></a></td>
      </tr>
      </table>
    </td>
  </tr>
  </table>
  <input type="hidden" name="act" value="signin" />
</form>
<script language="JavaScript">
<!--
  //document.forms['theForm'].elements['username'].focus();
  
  /**
   * 检查表单输入的内容
   */
  function validate()
  {
    var validator = new Validator('theForm');
    validator.required('username', user_name_empty);
    //validator.required('password', password_empty);
    if (document.forms['theForm'].elements['captcha'])
    {
      validator.required('captcha', captcha_empty);
    }
    return validator.passed();
  }
  
//-->
</script>
</body>