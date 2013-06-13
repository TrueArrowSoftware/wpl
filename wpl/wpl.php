<?php
/*
Plugin Name: WP Login Only
Plugin URI: https://github.com/Vikasumit/wpl
Description: Plugin allows you to set access to wordpress to member only.
Version: 1.0
Author: Vikasumit
Author URI: http://www.vikasumit.com/
*/

		// this gets the current full url of the current page
	function GetCurrentUrl() 
	{
		$protocol = 'http';
		if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) 
		{
			$protocol .= 's';
			$protocol_port = $_SERVER['SERVER_PORT'];
		} 
		else 
		{
			$protocol_port = 80;
		}
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		$request = $_SERVER['PHP_SELF'];
		$Path=$_SERVER['REQUEST_URI'];
		$query = substr($_SERVER['argv'][0], strpos($_SERVER['argv'][0], ';') + 1);
		//$toret = $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $Path. (empty($query) ? '' : '?' . $query);
		$toret = $protocol . '://' . $host . $Path;
		return $toret;
	}
		// this checks whether the person is logged in or not and then if not and they are not on an admin or the login page it redirects them to wp-login.php or static page
	function CheckLogin() 
	{
		$loginurl = wp_login_url();
		$currenturl = GetCurrentUrl();
		$PageId=get_option('WpLoginPageId');
		if(is_numeric($PageId)== true){
		$permalink =  get_permalink( $PageId );
		}
		else
		{
		$permalink =  get_bloginfo('url') . "/$PageId";
		}
		if($currenturl == $loginurl) 
		{
			// do nothing
		}
		elseif(is_admin()) 
		{
			// do nothing
		} 
		elseif(is_feed()) 
		{
			// do nothing
		}
		elseif($currenturl == $permalink) 
		{
			// do nothing
		}
		else 
		{
			$loggedin = is_user_logged_in();
			if($loggedin  == false)
			{	
				$siteurl = $permalink;
				wp_redirect($siteurl);
				exit();
			}
	
		}
	}
	
	add_action('template_redirect', 'CheckLogin');
		// this redirects users to the front page of the site from wp-login.php rather than going to the dashboard
	function RedirectToFrontPage()
	{
		global $redirect_to;
		if (!isset($_GET['redirect_to'])) 
		{
			$redirect_to = get_option('siteurl');
		}
	}
	
	
	
	
	function WpL() 
	{
		add_options_page( 'Plugin Options', 'Wp Login ', 'manage_options', 'wp-login-redirect', 'WpLoginOption' );
	}
	
	function WpLoginOption()
	{
		$check=get_option('WpLoginPageId');
		if($_POST['selectpage'] == "login" )
		{
			if($check == NULL)
			{
				 add_option('WpLoginPageId','wp-login.php');
			}
			else
			{
				 update_option('WpLoginPageId','wp-login.php');
			}
		}
		elseif($_POST['selectpage'] == "static" )
		{
			if($check == NULL)
			{
				 add_option('WpLoginPageId',(int)$_POST['page_id']);
			}
			else
			{
				 update_option('WpLoginPageId',(int)$_POST['page_id']);
			}
		}
		$check=get_option('WpLoginPageId');
		if ( !current_user_can( 'manage_options' ) )  
		{
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<script type="text/javascript">
		jQuery(function(){
			jQuery("#page_id").val("'.get_option("WpLoginPageId").'");			
			if(jQuery("#loginradio").is(":checked")) {
				jQuery("#page_id").attr("disabled","disabled");
			}
			
			jQuery("#selectpage").change(function(){
				jQuery("#page_id").removeAttr("disabled");
			});
			
			jQuery("#loginradio").change(function(){
				jQuery("#page_id").attr("disabled","disabled");
			});
		});
		</script>';
		echo '<div class="wrap"><h2>Redirect Settings</h2><form method="post" action="" name="form"><table class="form-table"><tr valign="top">';
		echo '<th scope="row">Set Here Static/login page for redirect</th>';
		echo '<td id="front-static-pages"><p><label><input type="radio" id="loginradio" name="selectpage" value="login"';
		if(is_numeric($check)== false || $_POST['selectpage']=="login" ){
		echo 'checked="checked"';
		}
		echo '>Login page</lable></p>';
		echo '<p><label><input type="radio" name="selectpage" id="selectpage" value="static"';
		if(is_numeric($check)== true || $_POST['selectpage']=="static" ){
		echo 'checked="checked"';
		}
		echo ' > Set a Static page :</label></p><ul><li>';
			wp_dropdown_pages(); 
		echo '</li></ul></tr></table><p class="submit"><input type="submit" value="Set page" id="setredirect" name="setredirect" class="button button-primary"></p>';
		echo '<form></div>';
		
	}
	
	add_action('login_form', 'RedirectToFrontPage');
	add_action( 'admin_menu', 'WpL' );
	
	
?>