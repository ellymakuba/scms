<?php
include ('LanguageSetup.php');
if ($allow_demo_mode == True and !isset($demo_text)) {
	$demo_text = _('login as user') .': <i>' . _('admin') . '</i><BR>' ._('with password') . ': <i>' . _('weberp') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}
?>
<html>
<head>
    <title>Webafriq School Software</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />    
    <link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</head>
<body>
<?php
if (get_magic_quotes_gpc()){
	echo '<p style="background:white">';
	echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
	echo '</p>';
}
?>
<div id="container">
<div id="header"><img src="webafriq.png"></div>
<hr size=4 width=98%>
<div id="lower">
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	
	<label><?php echo _('School'); ?>:</label><br />
	<?php
		if ($AllowCompanySelectionBox == true){
			echo '<select name="CompanyNameField">';
			$DirHandle = dir('companies/');
			while (false != ($CompanyEntry = $DirHandle->read())){
				if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != '' AND $CompanyEntry!='.svn' AND $CompanyEntry!='.'){
					if ($CompanyEntry==$DefaultCompany) {
						echo "<option selected value='$CompanyEntry'>$CompanyEntry</option>";
					} else {
						echo "<option  value='$CompanyEntry'>$CompanyEntry</option>";
					}
				}
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="CompanyNameField"  value="' . $DefaultCompany . '" readonly=""/>';
		}
	?>
	<br />
	
	<label><?php echo _('User name'); ?>:</label><br />
	<input type="TEXT" name="UserNameEntryField"/><br />
	<label><?php echo _('Password'); ?>:</label><br />
	<input type="PASSWORD" name="Password"><br />
	<input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" /><br /><br>
	
	</form>
	</div>
</div>
    <script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
                  document.loginform.UserNameEntryField.focus();
            //-->
    //]]>
    </script>
</body>
</html>