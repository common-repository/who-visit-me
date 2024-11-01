<?php
/*
Plugin Name: who-visit-me
Plugin URI: http://winysky.com
Description: Who - visit - me is not a statistic plugin . It shows the  friends who visit to author's blog recently, similar to the "recent visit" function in the SNS websites.This plugin will  display gravatar, visit time, blog link, the source address (IP real address ) for all friends who have ever leaved a comment when they visit next time.who-visit-me(最近来访）不是一个访问统计插件，本插件功能显示最近访问博客的朋友，类似于SNS网站“最近来访”功能。凡是曾在博客留言的朋友下次来访均可以显示头像、到访时间、博客链接、来源地址(ip真实地址-邪恶的功能）。
Version: 1.0.0
Author: winy
Author URI: http://winysky.com
*/
defined('ABSPATH') or die('This file can not be loaded directly.');
load_plugin_textdomain('who-visit-me', false, basename(dirname(__FILE__)) );
 global $wpdb, $wvm;
 $table_prefix = (isset($table_prefix)) ? $table_prefix : $wpdb->prefix;
 $wvm = $table_prefix . 'wvm';
 

/* 启用插件 */
function whovisitme_activate() {
 global $wpdb, $wvm;

// 建立数据库
 if ($wpdb->get_var("show tables like '$wvm'") != $wvm) {
  $wpdb->query("CREATE TABLE ". $wvm ." (
  id       int(8)       NOT NULL auto_increment,
  time 	   varchar(32)  NOT NULL,
  ip       varchar(32)  NOT NULL,
  region   varchar(100) NOT NULL,
  name     varchar(100) NOT NULL, 
  mail     varchar(100) NOT NULL, 
  url      varchar(100) NOT NULL, 
  UNIQUE KEY id (id)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
 }
	wp_schedule_event(time(), 'daily', 'wvm_daily_event');//添加一个计划任务用于自动清理数据库
	$qqwry = ABSPATH . "wp-content/plugins/who-visit/ip/qqwry.dat"; // 純真 IP 庫
	add_option('wvm_1', $qqwry );//纯真IP地址库地址
	add_option('wvm_2', 10 );//数据保留数量
	add_option('wvm_3', 5 );//显示数量
	add_option('wvm_4', 32 );//头像大小
	add_option('wvm_5', '' );//是否显示链接
	add_option('wvm_6', 'on' );//是否显示访客来源
	add_option('wvm_7', '' );//黑名单
}
register_activation_hook(__FILE__, 'whovisitme_activate');
add_action('wvm_daily_event', 'wvm_clean');

/* 停用插件 */
function whovisitme_deactivate() {
 global $wpdb, $wvm;

// 刪除 'wvm' table 和 'wvm_settings' option
 $wpdb->query("DROP TABLE IF EXISTS $wvm");
$options = array ('1','2','3','4','5','6','7');
foreach ( $options as $opt ){
	delete_option ( 'wvm_'.$opt, $_POST[$opt] );
		}
//清理计划任务
wp_clear_scheduled_hook('wvm_daily_event');
 
}
register_deactivation_hook(__FILE__, 'whovisitme_deactivate');

/*每天运行一次，删除多余数据节省空间*/
function wvm_clean() {
	global $wpdb, $wvm;
	$wvm_2= get_option('wvm_2');
  $wpdb->query("DELETE FROM $wvm WHERE id NOT IN ( SELECT id FROM $wvm ORDER BY id DESC LIMIT $wvm_2 )");// 删除多余记录
  $wpdb->query("OPTIMIZE TABLE $wvm"); // 刪除後進行優化
}



 /* 插件设置 */
add_action('admin_menu', 'wvm_page');
function wvm_page (){
	if ( count($_POST) > 0 && isset($_POST['wvm_settings']) ){
		$options = array ('1','2','3','4','5','6','7');
		foreach ( $options as $opt ){
			delete_option ( 'wvm_'.$opt, $_POST[$opt] );
			add_option ( 'wvm_'.$opt, $_POST[$opt] );	
		}
		if(isset($_POST[7])!=''){
			$wvm_7=$_POST[7];
			$blacklist=explode(",",$wvm_7);
			global $wpdb, $wvm;
			foreach ( $blacklist as $name ){
			$wpdb->query("DELETE FROM $wvm WHERE name = '$name'");//删掉黑名单里的记录
					}
		}
	}
	add_options_page('who-visit-me options',  __("who-visit-me","who-visit-me"), 8, basename(__FILE__), 'wvm_settings');

}

function wvm_settings() {?>

<div class="wrap">
<h2><?php _e('who-visit-me Options','who-visit-me');?></h2>
<form method="post" action="">
<?php wp_nonce_field('update-options'); ?>
	<h3><?php _e('Some configuration:','who-visit-me');?></h3>
	<fieldset>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="1"><?php _e('IP database address Options:','who-visit-me');?></label></th>
				<td>
					<input name="1" type="text" id="1" value="<?php echo get_option('wvm_1');?>" />
					<br /><em><?php _e('Customize IP database','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="2"><?php _e('Database reserve number:','who-visit-me');?></label></th>
				<td>
					<input name="2" type="text" id="2" value="<?php echo get_option('wvm_2'); ?>" /><br /> <em><?php _e('Input reserve number.Automatically remove the old data (activated once per day )','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="3"><?php _e('show visitor number:','who-visit-me');?></label></th>
				<td>
					<input name="3" type="text" id="3" value="<?php echo get_option('wvm_3'); ?>" /> 
					<br /><em><?php _e('Input display number','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="4"><?php _e('Size of gravartar img','who-visit-me');?></label></th>
				<td>
					<input name="4" type="text" id="4" value="<?php echo get_option('wvm_4'); ?>"/>
					<br /><em><?php _e('Input size number(32 for default)','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="5"><?php _e('links setting:','who-visit-me');?></label></th>
				<td>
					<input name="5" type="checkbox" id="5" <?php if (get_option('wvm_5')!='') echo 'checked="checked"'; ?>/>
					<em><?php _e('Display links?','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="6"><?php _e('Location setting :','who-visit-me');?></label></th>
				<td>
					<input name="6" type="checkbox" id="6" <?php if (get_option('wvm_6')!='') echo 'checked="checked"'; ?>/>
					<em><?php _e('Display location?','who-visit-me');?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="7"><?php _e('Blacklist :','who-visit-me');?></label></th>
				<td>
					<input name="7" type="text" id="7" value="<?php echo get_option('wvm_7'); ?>" /> 
					<br /><em><?php _e('Input the NAME which do not want to show(Like SPAMs),seperate by "," for multiple names','who-visit-me');?></em>
				</td>
			</tr>
			
		</table>
	</fieldset>

	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="保存设置" />
		<input type="hidden" name="wvm_settings" value="save" style="display:none;" />
	</p>

</form>
	<h3><?php _e('plugin reduce','who-visit-me') ?></h3>
	<div id="who-visit-me_info">
		<p><?php _e('use &lt;?php wvm_echo(); ?> to show the who-visit-me list.','who-visit-me') ?></p>
	<br>
<h3><?php _e('Go to ','who-visit-me') ?><a href="http://winysky.com/who-visit-me-recent-visitors-plug-in-released"><?php _e('My Blog Plugins page ','who-visit-me') ?></a><?php _e(' to get more info.','who-visit-me') ?>
</h3></div>
</div>
<?php
}
/*前台hook*/
add_action('wp_head', 'wvm_record');      // $arg = ''
add_action('comment_post', 'wvm_record'); // $arg = $comment_id
/* 取得来访的朋友资料 */

function wvm_record(){

	 $time = time()+(60*60*get_settings("gmt_offset")); //服务器时间不同
	 $ip  = $_SERVER["REMOTE_ADDR"];
	 $name = isset($_COOKIE['comment_author_'. COOKIEHASH]) ? $_COOKIE['comment_author_'. COOKIEHASH] : '' ;
     $mail = isset($_COOKIE['comment_author_email_'. COOKIEHASH]) ? $_COOKIE['comment_author_email_'. COOKIEHASH] : '';
     $url = isset($_COOKIE['comment_author_url_'. COOKIEHASH]) ? $_COOKIE['comment_author_url_'. COOKIEHASH] : '';

if ($arg) { // comment 才有 $arg,为新来的访客评论后立即加入
	$name = $comment->comment_author . " $arg";
	$mail = $comment->comment_email . " $arg";
	$url = $comment->comment_url . " $arg";
	
}
if($name==''|| $mail==''){return;}//没有cookie不记录
$wvm_7=get_option('wvm_7');//检查黑名单

$blacklist=explode(",",$wvm_7);
if(in_array($name, $blacklist)){
	global $wpdb, $wvm;
	$wpdb->query("DELETE FROM $wvm WHERE name = '$name'");//删掉黑名单里的记录
return;

}

	$wvm_1= get_option('wvm_1');
	if (is_file($wvm_1)) {
		include('iplocation.php');
		$iplocation = new IpLocation($wvm_1);
		$separator = $iplocation->separate(1000);
		$ip_location = $iplocation->getlocation($ip, $separator);
		if ($ip_location['area'] == '对方和您在同一内部网') $ip_location['area'] =  __(' From Local','who-visit-me');
		$region = $ip_location['country'].' '.$ip_location['area'];
		if (isset($_SERVER["HTTP_VIA"])) $region .= ' (Proxy:'.$_SERVER["HTTP_VIA"].')';
	} else {
		function wvm_whois($ip) {
			$whois = "http://www.ip138.com/ips.asp?ip=$ip";
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $whois);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
			ob_start();
			curl_exec($ch);
			curl_close($ch);
			$html = mb_convert_encoding(ob_get_contents(), 'UTF-8', 'GB2312');
			ob_end_clean();
			$tmp = @strpos($html, '<li>本站主数据：') + 22;
			return $regn = ($tmp < 23) ? '' : substr($html, $tmp, strpos($html, '</li>', $tmp) - $tmp);
		}

		if (function_exists('curl_init')) {
		$region = wvm_whois($ip);
		if (isset($_SERVER["HTTP_VIA"])) $region .= ' (Proxy:'.$_SERVER["HTTP_VIA"].')'; 
		} else $region = __('Unknow Address','who-visit-me');

}

/*记录到数据库*/

	$visit=false;//先看看有没有记录
	global $wpdb, $wvm;
	$results = $wpdb->get_results("SELECT id,name,mail FROM $wvm ORDER BY id DESC");
    foreach ($results as $result) {
		if ($result->name==$name||$result->mail==$mail) {
		$visit=true;
			break;
		}	
       }
	if($visit){$wpdb->query("DELETE FROM $wvm WHERE name = '$name'");}//这人又来了，先删除旧的信息，只保留新的


 $wpdb->query("INSERT INTO ". $wvm ." VALUES ('', '$time', '$ip', '$region', '$name', '$mail', '$url')");
 

}

/*页面输出*/
function whovisitme(){
	global $wpdb, $wvm;
	$wvm_3= get_option('wvm_3');
	$wvm_4= get_option('wvm_4');
	$wvm_5= get_option('wvm_5');
	$wvm_6= get_option('wvm_6');
	$results = $wpdb->get_results("SELECT * FROM  $wvm  ORDER BY id DESC LIMIT $wvm_3");
	$output='<ul id="wvm_item">';
	if(empty($results)){
		$output.='<li>'.__('Oooops,nobody visit here recently~','who-visit-me').'</li>';
	}else{
		foreach ($results as $result) {
			$time=$result->time;
			$region=($wvm_6!='')? $result->region:'';
			$name=$result->name;
			$mail=$result->mail;
			$url=($wvm_5!='')? $result->url:'javascript:void(0);';
			$output.= '<li><span class="wvm_avatar">'.get_avatar($mail,$wvm_4).'</span><span class="wvm_name"><a href="'.$url.'" rel="follow" title="'.$region.'">'.$name.'--'.wvm_time_since($time).'</a></span></li>';
		}
}
	$output.='</ul>';
	
return $output;
}
/*直接调用输出函数*/
function wvm_echo(){
	$output = whovisitme();
	echo $output;
	
}
/*添加一个边栏小工具*/
//widget
class wvm_widget extends WP_Widget{
	function wvm_widget(){
		$widget_des = array('classname'=>'who-visit-me','description'=>__('show recent visitor in sidebar widget.','who-visit-me'));
		$this->WP_Widget(false,__('who-visit-me', 'who-visit-me'),$widget_des);
	}
	function form($instance){
		$instance = wp_parse_args((array)$instance,array(
		'title'=>__('recent visitor', 'who-visit-me')));
		echo '<p><label for="'.$this->get_field_name('title').'">'.__('widget title: ', 'who-visit-me').'<input style="width:200px;" name="'.$this->get_field_name('title').'" type="text" value="'.htmlspecialchars($instance['title']).'" /></label></p>';
	}
	function update($new_instance,$old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		return $instance;
	}
	function widget($args,$instance){
		extract($args);
		$title = apply_filters('widget_title',empty($instance['title']) ? __('recent visitor', 'who-visit-me') : $instance['title']);
		echo "<li id='wvm_widget' class='widget widget-container'><h3 class='widget-title'>".$title."</h3>";
		 wvm_echo();
		echo "</li>";
	}
}
function wvm_widget_init(){
	register_widget('wvm_widget');
}
add_action('widgets_init','wvm_widget_init');

/*显示一个相对时间*/
function wvm_time_since($older_date){
$chunks = array(array(60 * 60 , __(' hour','who-visit-me')),array(60 , __(' minutes','who-visit-me')),);
$newer_date =time()+(60*60*get_settings("gmt_offset"));
$since = $newer_date - $older_date;
if($since<86400 and $since>60){
for ($i = 0, $j = count($chunks); $i < $j; $i++){
$seconds = $chunks[$i][0];
$name = $chunks[$i][1];
if (($count = floor($since / $seconds)) != 0){
break;}}
$output = "$count{$name}";
if ($i + 1 < $j){
$seconds2 = $chunks[$i + 1][0];
$name2 = $chunks[$i + 1][1];
if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0){
$output .= " $count2{$name2}";}}
return $output.__('before','who-visit-me');}
elseif($since<60 || $since==60){
return $since.__(' seconds before','who-visit-me');}
else{return __('1 day before','who-visit-me');}
}


?>