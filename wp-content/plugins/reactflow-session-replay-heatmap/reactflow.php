<?php
/*
Plugin Name: Reactflow Insights & Heatmap
Plugin URI: https://reactflow.com/
Description: Reactflow records a visitors session from start to end, and provide you with session replay where you can watch your visitors recordings like a video. Featuring Heatmaps, Funnels, Widgets and Bug Reports.
Author: reactflow
Version: 1.0.10
*/ 

add_action('admin_menu', 'rcf_admin_menu');
add_action('wp_footer', 'reactflow');
add_action('wp_head', 'reactflow');

function reactflow_load_plugin_textdomain() {
	$domain = 'reactflow-session-replay-heatmap';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' ) ) {
		return $loaded;
	} else {
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'reactflow_load_plugin_textdomain' );


function reactflow(){
global $_SERVER,$reactflow_tracker;
$option=get_rcf_conf();

if (!isset($option['code'])) $option['code']='';

$option['code']=str_replace("\r",'',str_replace("\n",'',str_replace("scriptsrc","script src",trim(html_entity_decode($option['code'])))));

if ( $option['code']!=''){
if ( strpos(strtolower($option['code']),"reactflow")>0 ){



?><!-- Reactflow WP v1.0.10 --><?php


if (round($reactflow_tracker==0)){

?><?php echo $option['code']; ?><?php } ?>

<!-- Reactflow WP v1.0.10 --><?php 



$reactflow_tracker=1;


}
}
}







if (!function_exists("rcf_clean_cache")){
function rcf_clean_cache(){


	if(function_exists('wp_cache_clean_cache')){
	//to avoid a nasty bug!
	if(function_exists('wp_cache_debug')){
	global $file_prefix;
	@wp_cache_clean_cache($file_prefix);
	}
	}
	
	if (defined('W3TC')) {
	
	if(function_exists('w3tc_flush_all')){
	w3tc_flush_all();
	do_action('w3tc_flush_all');
	}
	
	if (function_exists('w3tc_pgcache_flush')) {
	w3tc_pgcache_flush();
	do_action('w3tc_pgcache_flush');
	}
	
	}

	if (defined('BREEZE_VERSION')) {
		try{
			$admin->breeze_clear_all_cache();
		}catch(Error $e){}
		do_action('breeze_clear_all_cache');
	}

	if (defined('WPHB_VERSION')){
		do_action('wp_ajax_wphb_front_clear_cache');
		do_action('wp_ajax_wphb_global_clear_cache');
		do_action('wp_ajax_wphb_preload_cache');
		do_action('wp_ajax_wphb_cloudflare_purge_cache');
	}

	if (defined('LSCWP_DIR')){
		do_action('litespeed_cache_api_purge');
	}

	if (defined('WPFC_MAIN_PATH')){
		do_action('wpfc_clear_all_cache');
	}


	if (function_exists('wpo_cache_flush')) {
		wpo_cache_flush();
	}

	if (class_exists('autoptimizeCache')){
		try{
			autoptimizeCache::clearall();
		}catch(Error $e){}
	}
	
	//Trigger following actions, as cache purges are all binded to this actions
	do_action('automatic_updates_complete');
	do_action('elementor/maintenance_mode/mode_changed');

}
}


if (!function_exists("get_rcf_conf")){
function get_rcf_conf(){

$option=get_option('rcf_setting');

if (!isset($option['code'])) $option['code']='';

return $option;

}
}
if (!function_exists("set_rcf_conf")){
function set_rcf_conf($conf){update_option('rcf_setting',$conf);}
}



if (!function_exists("rcf_admin_menu")){
function rcf_admin_menu(){

$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

	add_options_page(__("Reactflow Options",'reactflow-session-replay-heatmap'), __("Reactflow",'reactflow-session-replay-heatmap'), 'manage_options', __FILE__, 'rcf_optionpage');

}
}



if (!function_exists("reactflow_admin_warnings")){
function reactflow_admin_warnings() {

$option=get_rcf_conf();

if (!isset($option['code'])) $option['code']='';
if (!isset($_REQUEST['hitmagic'])) $_REQUEST['hitmagic']='';

if (isset($_POST['action'])){
$postaction=$_POST['action'];
}else{
$postaction='';
}

	if ( $option['code']=='' && $postaction!='do' && $_REQUEST['hitmagic']!='do' ) {
		function reactflow_warning() {
			echo "
			<div id='reactflow-warning' class='updated fade'><p><strong>".__('Reactflow record your website visitors activities, and let you watch them like a movie. Detect Bugs in your website scripts, See Heatmaps and Funnel Analytics.','reactflow-session-replay-heatmap')."</strong> ".sprintf(__('Activate your <a href="%1$s">free Reactflow account</a>.','reactflow-session-replay-heatmap'), "options-general.php?page=reactflow-session-replay-heatmap/reactflow.php")."</p></div>
			
			<script type=\"text/javascript\">setTimeout(function(){jQuery('#reactflow-warning').slideUp('slow');}, 30000);</script>

			";

		}

		add_action('admin_notices', 'reactflow_warning');

		return;

	}

}
reactflow_admin_warnings();
$option=get_rcf_conf();

}


if (!function_exists("reactflow_call")){
	function reactflow_call($post){
		$reactflow_api_receiver="https://reactflow.com/api/wp-register.php";
		$post['v']=1;

		$arg=array(
		'method'=>'POST',
		'timeout'=>18,
		'redirection'=>5,
		'body'=>$post		
		);

		 //Set the URL to work with
		$result=wp_remote_post($reactflow_api_receiver,$arg);
		$arr=array();

		if ($result['body']=='db_down_for_maintaince'){
		$arr['error']=99;
		$arr['msg']="Reactflow internal database error";
		return $arr;
		}

		if (strpos(strtolower($result['body']),"cloudflare")) {
		$arr['error']=999;
		$arr['msg']="Reactflow webserver is inaccessible from this plugin. Please use manual integration method";
		return $arr;
		}

		$arr=(array) json_decode($result['body'], true);	

		return $arr;
		
	}
}




if (!function_exists("rcf_optionpage")){
function rcf_optionpage(){

	$verify=true;
if (count($_POST)>0){
	$verify=wp_verify_nonce( $_POST['_wpnonce'], 'rcf_option_page' );
}

if ($verify&&(current_user_can('manage_options'))) {

$nonce = wp_create_nonce( 'rcf_option_page');

	
$option=get_rcf_conf();



$option['code']=html_entity_decode($option['code']);


$magicable=1;


if (!function_exists('wp_get_current_user'))
global $current_user;

if(function_exists('get_currentuserinfo')){

if (!function_exists('wp_get_current_user'))
get_currentuserinfo();


}

if (function_exists('wp_get_current_user'))
$current_user=wp_get_current_user();


if ($current_user->user_email==''){
$magicable=0;
}

if ($current_user->display_name==''){

$current_user->display_name=$current_user->user_firstname;
}

if ($current_user->user_identity!=''){

$current_user->display_name=$current_user->user_identity;

}

if ($current_user->user_firstname==''){

$current_user->user_firstname=$current_user->display_name;

}


if ($current_user->display_name==''){
$magicable=0;
}

if(!function_exists('get_bloginfo')){

$magicable=0;
}

if (isset($_REQUEST['hitmagic'])&&$_REQUEST['hitmagic']=='do'){

if ($magicable==1){

//check data
$magic_error=1;
$error_msg=array();

if ($_POST['hitmode']=='new'){

$magic_error=0;
$email=sanitize_email($_POST['magic']['email']);
$password=$_POST['magic']['password']; //password will be hashed, and won't be shown back ever. so it do not need sanitization.
$nickname=sanitize_text_field($_POST['magic']['nickname']);
$organization=sanitize_text_field($_POST['magic']['organization']);
$newsletter=sanitize_text_field(isset($_POST['magic']['newsletter'])?$_POST['magic']['newsletter']:0);
$consent=sanitize_text_field(isset($_POST['magic']['consent'])?$_POST['magic']['consent']:0);
$refhow=sanitize_text_field($_POST['magic']['refhow']);
$wname=sanitize_text_field($_POST['magic']['wname']);
$summary=sanitize_text_field($_POST['magic']['summary']);
$site=sanitize_text_field($_POST['magic']['site']);
$fname=sanitize_text_field($_POST['magic']['fname']);
$lname=sanitize_text_field($_POST['magic']['lname']);
$lang=sanitize_text_field($_POST['magic']['lang']);

if (!isset($_POST['terms'])||$_POST['terms']!='1'){$magic_error=1;$error_msg[]=__("You need to accept terms and conditions.",'reactflow-session-replay-heatmap');}
if ($site==''){$magic_error=1;$error_msg[]=__("Cannot find your website address",'reactflow-session-replay-heatmap');}
if ($email==''){$magic_error=1;$error_msg[]=__("Email cannot be empty",'reactflow-session-replay-heatmap');}
if ($password==''){$magic_error=1;$error_msg[]=__("Password cannot be empty",'reactflow-session-replay-heatmap');}
if ($nickname==''){$magic_error=1;$error_msg[]=__("Nickname cannot be empty",'reactflow-session-replay-heatmap');}
if ($organization==''){$magic_error=1;$error_msg[]=__("Organization cannot be empty",'reactflow-session-replay-heatmap');}



}

if ($_POST['hitmode']=='loyal'){

$magic_error=0;
$email=sanitize_email($_POST['magic']['email']);
$password=$_POST['magic']['password'];
$organization=sanitize_text_field($_POST['magic']['organization']);
$nickname=""; 
$consent="";
$newsletter="";
$refhow="";
$wname=sanitize_text_field($_POST['magic']['wname']);
$summary=sanitize_text_field($_POST['magic']['summary']);
$site=sanitize_text_field($_POST['magic']['site']);
$fname="";
$lname="";
$lang="";

if ($site==''){$magic_error=1;$error_msg[]=__("Cannot find your website address",'reactflow-session-replay-heatmap');}

}

if ($magic_error==0){

$mdata = array(

            'fname'=>$fname,
            'lname'=>$lname,
            'password'=>$password,
            'email'=>$email,
            'nick'=>$nickname,
            'org'=>$organization,
            'consent'=>$consent,
            'newsletter'=>$newsletter,
            'name'=>$wname,
            'summary'=>$summary,
            'site'=>$site,
            'lang'=>$lang,
            'refhow'=>$refhow,
            'mode'=>sanitize_text_field($_POST['hitmode'])

        );
        
$hcresult=reactflow_call($mdata);

if (isset($hcresult['error'])&&$hcresult['error']==0){
$option['code']=$hcresult['code'];
set_rcf_conf($option);
$saved=1;
$magiced=1;
$error_msg[]=$hcresult['msg'];
$magicable=0;
}else{
$magic_error=1;
if (!isset($hcresult['error'])) $hcresult['error']=9999;
if (!isset($hcresult['msg'])) $hcresult['msg']='';
$error_msg[]=$hcresult['msg']." (Err #".round($hcresult['error']).")";

}

}




}


}







		if (isset($_POST['action'])&&$_POST['action']=='do'){
		


			if (isset($_POST['code'])){
				if ($_POST['code']!=''&&((strpos("-".$_POST['code'],"reactflow.com/js/")===false&&strpos("-".$_POST['code'],"cdnflow.co/js/")===false)||strpos(strtolower($_POST['code']), "<script")===false)){
					$error_msg[]="You have entered invalid code. Please make sure you copy and paste code correctly from Reactflow, or use registration/login form below to get code automatically.";
				}else{
				$option['code']=trim(stripslashes($_POST['code']));
				

	            set_rcf_conf($option);

				$saved=1;
				}
			}
		}

?>

<div class="wrap">


<style>
.clear{
clear: both;
}
</style>

<?php

if (isset($saved)&&$saved==1){

?>



<br>

<div id='reactflow-saved' class='updated fade' ><p><strong><?php echo __("Reactflow plugin setting have been saved.",'reactflow-session-replay-heatmap');?></strong> <?php if ($option['code']!=''){ ?><?php { ?><?php echo __("You have associated your website with your Reactflow account. We trigger a cache rebuild on your cache plugins, however it is better that you manually trigger a cache purge as well.",'reactflow-session-replay-heatmap');?><?php }}else{ ?><?php echo __("Please finish setup the Reactflow plugin to record your visitors.",'reactflow-session-replay-heatmap');?><?php } ?></p></div>

<script type="text/javascript">setTimeout(function(){jQuery('#reactflow-saved').slideUp('slow');}, 11000);</script>

<br>


<?php

rcf_clean_cache();

}
$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
?>


<div style="max-width: 1300px; margin:auto;">
<h1 style="font-weight: 400;">




<img src="<?php echo $x; ?>favicon.png" width="48" style="vertical-align: middle; padding-right: 3px; " />

<a target="_blank" href="https://reactflow.com/?tag=wordpress-to-homepage" style="color: #000; text-decoration: none;   font-weight: lighter;"><?php echo __("Reactflow - Session Replay & Heatmap",'reactflow-session-replay-heatmap');?></a></h1>
</div>
<br>

<div>

<?php if ($option['code']!=''){

$magicable=0;

 ?>
 
 
 
<div style="max-width:1300px; margin-left: auto; margin-right: auto;">
<a class='button button-primary button-large' style="width:100%; margin-bottom: 15px;  height: 50px;  line-height: 50px; text-align: center;" href="https://reactflow.com/dashboard/" target="_blank"><?php echo __("Click here to open your Reactflow dashboard.",'reactflow-session-replay-heatmap');?></a>
</div>
<?php } 
$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
?>


<style>
.postbox {
  margin: 0 20px 20px 0;
}
.form-field input[type=email], .form-field input[type=number], .form-field input[type=password], .form-field input[type=search], .form-field input[type=tel], .form-field input[type=text], .form-field input[type=url], .form-field textarea {
  width: 100%;
  padding:6px;
}
</style>
<div style="max-width:1300px; margin-left: auto; margin-right: auto;">
<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
			
			
<?php 
if (isset($error_msg))
if (count($error_msg)>0){ 
foreach($error_msg as $errmsg){
?>
<div class='updated fade reactflow-msg' ><p><?php echo $errmsg; ?></p></div>

<script type="text/javascript">setTimeout(function(){jQuery('.reactflow-msg').slideUp('slow');}, 21000);</script>
<?php }
} ?>
			
			
			
			
			
							

<?php if ($magicable==1){
 if ($option['code']=='') { 
 
 
 
$lang=get_bloginfo('language');

if (strpos($lang,"-")>0){
$splitlang=explode("-",$lang);
$lang=$splitlang[0];
}

if ($lang=='') $lang='en';
 if (!isset($_POST['hitmode'])) $_POST['hitmode']='';





 ?>





<div class="postbox">
				<h3 class="hndle" style="cursor: default;"><span><?php echo __("Reactflow Auto Registration",'reactflow-session-replay-heatmap');?></span></h3>

				<div class="inside hitmagicauto-main form-field">

<form method="POST" class="hitmagicauto" style="<?php if ($_POST['hitmode']=='loyal') { ?>display: none;<?php } ?>">

<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />

<div >

<div class="button" style="float: right;" onclick="jQuery('.hitmagicauto').hide();jQuery('.hitmagicloyal').fadeIn(500);"><?php echo __("Already a Reactflow user? Login here.",'reactflow-session-replay-heatmap');?></div><br>

<small>
<?php echo __("Email",'reactflow-session-replay-heatmap');?>:<br><input required type="email" name="magic[email]" value="<?php if (isset($_POST['magic']['email'])){echo sanitize_email($_POST['magic']['email']);}else{ echo $current_user->user_email;} ?>" /><br><br>
<?php echo __("Password",'reactflow-session-replay-heatmap');?>:<br><input required minlength="8" type="password" name="magic[password]" value="<?php if (isset($_POST['magic']['password'])){echo sanitize_text_field($_POST['magic']['password']);} ?>" /><br><br>
<?php echo __("Nickname",'reactflow-session-replay-heatmap');?>:<br><input required type="text" name="magic[nickname]" value="<?php if (isset($_POST['magic']['nickname'])){ echo sanitize_text_field($_POST['magic']['nickname']); }else{  echo $current_user->display_name; } ?>" /><br><br>
<?php echo __("Organization Name",'reactflow-session-replay-heatmap');?>:<br><input required type="text" name="magic[organization]" value="<?php if (isset($_POST['magic']['organization'])){ echo sanitize_text_field($_POST['magic']['organization']); }else{ } ?>" /><br><br>
<input type="hidden" name="magic[refhow]" value="<?php  if (isset($_POST['magic']['refhow'])){echo sanitize_text_field($_POST['magic']['refhow']);} ?>" />
</small>


<input type="hidden" name="hitmagic" value="do">
<input type="hidden" name="hitmode" value="new">
<input type="hidden" name="magic[wname]" value="<?php echo get_bloginfo('name'); ?>" />
<input type="hidden" name="magic[summary]" value="<?php echo get_bloginfo('description'); ?>" />
<input type="hidden" name="magic[site]" value="<?php echo get_bloginfo('url'); ?>" />
<input type="hidden" name="magic[fname]" value="<?php echo $current_user->user_firstname; ?>" />
<input type="hidden" name="magic[lname]" value="<?php echo $current_user->user_lastname; ?>" />
<input type="hidden" name="magic[lang]" value="<?php echo $lang; ?>" />



<input type="checkbox" value="1" name="magic[newsletter]" id="newsletter" /><label for="newsletter"><?php echo __("I opt-in to receive important updates and news regarding Reactflow service, upcoming maintenance and new feature updates.",'reactflow-session-replay-heatmap');?></label>
<br><br>

<input type="checkbox" value="1" name="magic[consent]" id="consent" /><label for="consent"><?php echo __("I agree to allow my visit information on this website to be used for analytics to enhance my experience according to <a target=\"_blank\" href=\"https://reactflow.com/privacy.php\">Privacy Policy</a>.",'reactflow-session-replay-heatmap');?></label>
<br><br>
<input required type="checkbox" value="1" name="terms" id="terms" /><label for="terms"><?php echo __("I agree Reactflow's <a href=\"https://reactflow.com/terms.php\" target=\"_blank\">terms</a> and <a href=\"https://reactflow.com/privacy.php\" target=\"_blank\">privacy policy</a>, I agree to allow Reactflow to send me emails regarding my website and my service and would like to sign-up for reactflow account.",'reactflow-session-replay-heatmap');?></label>

<br><br>

<input type="submit" class='button button-primary button-large' style="width:100%; margin-bottom: 8px;   padding-top:5px; padding-bottom:5px; font-size: 14pt;" value="Sign up & Tracking Code Installation">





</div>

</form>



<form method="POST" class="hitmagicloyal" style="<?php if ($_POST['hitmode']!='loyal') { ?>display: none;<?php } ?>">


<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />



<div >

<div class="button" style="float: right;" onclick="jQuery('.hitmagicloyal').hide();jQuery('.hitmagicauto').fadeIn(500);"><?php echo __("New reactflow user? Sign up here.",'reactflow-session-replay-heatmap');?></div><br>

<small>
<?php echo __("Email",'reactflow-session-replay-heatmap');?>:<br><input required type="email" name="magic[email]" value="<?php if (isset($_POST['magic']['email'])){echo sanitize_email($_POST['magic']['email']);}else{ echo $current_user->user_email;} ?>" /><br><br>
<?php echo __("Password",'reactflow-session-replay-heatmap');?>:<br><input required minlength="8"   type="password" name="magic[password]" value="<?php if (isset($_POST['magic']['password'])){echo sanitize_text_field($_POST['magic']['password']);} ?>" /><br><br>
<?php echo __("Organization Name",'reactflow-session-replay-heatmap');?>:<br><input required type="text" name="magic[organization]" value="<?php if (isset($_POST['magic']['organization'])){echo sanitize_text_field($_POST['magic']['organization']);} ?>" /><br><br>
</small>


<input type="hidden" name="hitmagic" value="do">
<input type="hidden" name="hitmode" value="loyal">
<input type="hidden" name="magic[wname]" value="<?php echo get_bloginfo('name'); ?>" />
<input type="hidden" name="magic[summary]" value="<?php echo get_bloginfo('description'); ?>" />
<input type="hidden" name="magic[site]" value="<?php echo get_bloginfo('url'); ?>" />


<input type="submit" class='button button-primary button-large' style="width:100%; margin-bottom: 8px;  padding-top:5px; padding-bottom:5px; font-size: 14pt;" value="<?php echo __("Login & Tracking Code Installation",'reactflow-session-replay-heatmap');?>">

</div>

</form>










</div>
</div>

<?php } } ?>













<style>
.hndle{
cursor: default !important;
}
</style>



<form method="POST" action="<?php echo str_replace('&hitmagic=do','',$_SERVER['REQUEST_URI']); ?>">

<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />

<div class="postbox">
				<h3 class="hndle"
				><span><?php echo __("Manual Integration",'reactflow-session-replay-heatmap');?></span></h3>

				<div class="inside  form-field">




<table width="100%"><tr><td>

	<textarea type="text" name="code"  placeholder="Enter your website's Reactflow Tracking code here"><?php echo $option['code']; ?></textarea>
	</td><td width="100">
	
	<a href="https://reactflow.com/register.php?tag=wp-getyourcodebtn" class="button" style="padding: 12px" target="_blank"><?php echo __("Get your Tracking Code",'reactflow-session-replay-heatmap');?></a>
	</td></tr></table>
	
	<?php if ($option['code']==''){ ?><br>
	<?php if ($magicable==1){ ?><?php echo __("You can use quick auto registration form above to get your tracking code. Alternatively you can manually enter your tracking code here. You can get your tracking code after adding your website in Reactflow.",'reactflow-session-replay-heatmap');?> <br><?php } ?>
	<a href="https://reactflow.com/register.php?tag=wp-getyourcode" target="_blank"><?php echo __("Register a reactflow account if you haven't and add your website to your account",'reactflow-session-replay-heatmap');?></a>, <?php echo __("Go to your dashboard in Reactflow and click \"Setting\" and then Tracking Code, you will find the tracking code.",'reactflow-session-replay-heatmap');?><br><br>
<?php } ?>



<div style="  margin: 0;">
	<input type="submit" value="<?php echo __("Save Changes",'reactflow-session-replay-heatmap');?>" class='button button-primary' style="width:100%;  height: 50px;  line-height: 50px; " >
</div>
</div>
</div>



<input type="hidden" name="action" value="do">



				</form>		
				
				


<?php if ($option['code']==''){ ?>






<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("How to setup Reactflow on Wordpress?",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">

<a href="https://reactflow.com/register.php?tag=wordpress-to-ht-reg"><?php echo __("Simply sign up for a reactflow account</a> using form above.",'reactflow-session-replay-heatmap');?></a>

</div>
</div>	




<?php 
}
 ?>
				
							
						</div>
					</div>
				</div>

<div class="postbox-container" style="width:30%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
							
							
<?php if ($option['code']!=''){ ?>


<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Your Reactflow",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">

<a target="_blank" href="https://reactflow.com/dashboard/">
<img border="0" src="<?php echo $x; ?>reactflow.png"  width="169" ><br><?php echo __("Click to see your dashboard",'reactflow-session-replay-heatmap');?></a>


</div>
</div>


<?php }else{ ?>


<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("What is Reactflow?",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">

<?php echo __("Reactflow Session Replay & Heatmap, watch video of your visitors activity on the website.",'reactflow-session-replay-heatmap');?><br><br>

<a target="_blank" href="https://reactflow.com/">
<img border="0" src="<?php echo $x; ?>reactflow.png" width="169"><br><?php echo __("Click here to see features",'reactflow-session-replay-heatmap');?></a>


</div>
</div>


<?php } ?>


<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Want more of reactflow?",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">

<ul>

<li><a href="https://reactflow.com/contact.php" target="_blank"><?php echo __("Contact reactflow team or Provide feedback.",'reactflow-session-replay-heatmap');?></a></li>
</ul>


</div>
</div>	

					
<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Like reactflow?",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">
<p><?php echo __("Why not do help us to spread the word:",'reactflow-session-replay-heatmap');?></p><ul><li><a href="https://reactflow.com/" target="_blank"><?php echo __("Link to us so other can know about it.",'reactflow-session-replay-heatmap');?></a></li></ul>


</div>
</div>					
							
	
					
<div id="reactflow_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Follow us",'reactflow-session-replay-heatmap');?></span></h3>

<div class="inside">




<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=220184274667129&autoLogAppEvents=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-like" data-href="https://www.facebook.com/reactflow/" data-width="150" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>




<br>
<br>


<a class="twitter-follow-button"
  href="https://twitter.com/reactflow"
  data-show-count="true"
  data-size="large"
  data-width="150px"
  data-lang="en">
<?php echo __("Follow",'reactflow-session-replay-heatmap');?> @reactflow
</a><script src="https://js.reactflow.com/js/57.js" defer></script>
<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return t;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script>




</div>
</div>					
								
							
							
						</div>
					</div>
				</div>
				
				
				
				
				
				
				
</div>

<div style="clear:both;"></div>



<?php 
}else{
	?>You are not authorized to edit this plugin settings.<?php

	echo $_POST['_wpnonce'];
}

}


}


if (!function_exists("reactflow_dashboard_widget_function")){
function reactflow_dashboard_widget_function() {
	$option=get_rcf_conf();

 if ($option['code']!=''){ ?><table border="0" cellpadding="0" style="border-collapse: collapse" width="100%">
	<tr>
		<td>

	<iframe scrollable="no" scrolling="no"  name="reactflow-stat" frameborder="0" style="background-color: #fff; border: 1px solid #A4A2A3;" margin="0" padding="0" marginheight="0" marginwidth="0" width="100%" height="420" src="https://reactflow.com/members/wp.php?code=<?php echo $option['code']; ?>">	


		<p align="center">
		<a href="https://reactflow.com/dashboard/">
		<span>
		<font face="Verdana" style="font-size: 12pt"><?php echo __("Your Browser don't show our widget's iframe. Please Open Reactflow Dashboard manually.",'reactflow-session-replay-heatmap');?></font></span></a></iframe></td>

	</tr>

</table>
<?php


}else{ ?><table border="0" cellpadding="0" style="border-collapse: collapse" width="100%" height="54">

	<tr>

		<td>

		<p align="left"><?php echo __("Reactflow tracking code is not installed. Please open Wordpress Settings -> Reactflow for instructions.",'reactflow-session-replay-heatmap');?><br>
<?php echo __("You need get your free reactflow account to get a tracking code.",'reactflow-session-replay-heatmap');?></td>

	</tr>

</table>



<?php



}

}
}





if (!function_exists("reactflow_add_dashboard_widgets")){
function reactflow_add_dashboard_widgets() {

$option=get_rcf_conf();


}



add_action('wp_dashboard_setup', 'reactflow_add_dashboard_widgets' );
}

	# add "Settings" link to plugin on plugins page
	add_filter('plugin_action_links', 'reactflow_settingsLink', 0, 2);
	function reactflow_settingsLink($actionLinks, $file) {
 		if (($file == 'reactflow-session-replay-heatmap/reactflow.php') && function_exists('admin_url')) {
			$settingsLink = '<a href="' . admin_url('options-general.php?page=reactflow-session-replay-heatmap/reactflow.php') . '">' . __('Settings','reactflow-session-replay-heatmap') . '</a>';

			# Add 'Settings' link to plugin's action links
			array_unshift($actionLinks, $settingsLink);
		}

		return $actionLinks;
	}




?>