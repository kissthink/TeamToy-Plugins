<?php
/*** 
TeamToy extenstion info block  
##name CSS自定义工具 
##folder_name css_modifier
##author Easy
##email Easychen@qq.com
##reversion 1
##desp 允许用户追加自定义的CSS。 
##update_url http://tt2net.sinaapp.com/?c=plugin&a=update_package&name=css_modifier 
##reverison_url http://tt2net.sinaapp.com/?c=plugin&a=latest_reversion&name=css_modifier 
***/
if( !defined('IN') ) die('bad request');

// 检查并创建数据库
if( !mysql_query("SHOW COLUMNS FROM `css`",db()) )
{
	// table not exists
	// create it
	run_sql("CREATE TABLE IF NOT EXISTS `css` (
  `uid` int(11) NOT NULL,
  `css` text NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ");

}

$plugin_lang = array();

$plugin_lang['zh_cn'] = array
(

	'PL_CSS_MODIFIER_MENU_TITLE' => '自定义CSS',
	'PL_CSS_MODIFIER_DATE_UPDATE_ERROR' => '数据保存失败，请稍后重试。<a href="%s">点击返回</a>',
	'PL_CSS_MODIFIER_TEST' => ''
);

$plugin_lang['zh_tw'] = array
(

	'PL_CSS_MODIFIER_MENU_TITLE' => '客制化CSS',
	'PL_CSS_MODIFIER_DATE_UPDATE_ERROR' => '數據保存失敗，請稍後重試。 <a href="%s">點擊返回</a>',
	'PL_CSS_MODIFIER_TEST' => ''
);

$plugin_lang['us_en'] = array
(

	'PL_CSS_MODIFIER_MENU_TITLE' => 'Custom CSS',
	'PL_CSS_MODIFIER_DATE_UPDATE_ERROR' => 'Date update error, <a href="%s">go back</a>',
	'PL_CSS_MODIFIER_TEST' => ''
);

plugin_append_lang( $plugin_lang );

// 添加顶部导航按钮
add_action( 'UI_USERMENU_BOTTOM' , 'mycss_menu_list');
function mycss_menu_list()
{
	?><li><a href="?c=plugin&a=mycss"><?=__('PL_CSS_MODIFIER_MENU_TITLE')?></a></li>
	<?php 	 	
} 


// 添加显示页面的逻辑
add_action( 'PLUGIN_MYCSS' , 'plugin_mycss');
function  plugin_mycss()
{

	$data['top'] = $data['top_title'] = __('PL_CSS_MODIFIER_MENU_TITLE');
	$data['css'] = get_var( "SELECT `css` FROM `css` WHERE `uid` = '" . intval(uid()) . "' LIMIT 1" );
	render( $data , 'web' , 'plugin' , 'css_modifier' ); 
}

// 添加保存css的函数
add_action( 'PLUGIN_MYCSS_SAVE' , 'plugin_mycss_save');
function plugin_mycss_save()
{
	$css = z(t(v('css')));
	$sql = "REPLACE INTO `css` ( `uid` , `css` ) VALUES ( '" . intval(uid()) . "' , '" . s( $css ) . "' )";
	run_sql( $sql );
	$location = '?c=plugin&a=mycss';
	if( db_errno() != 0 ) return info_page( __( 'PL_CSS_MODIFIER_DATE_UPDATE_ERROR' , $location  ));
	else header("Location:" . $location);
}

// 在head标签中输出自定义的CSS
add_action( 'UI_HEAD' ,'mycss_ui_head');
function mycss_ui_head()
{
	echo '<script type="text/javascript" src="plugin/css_modifier/tabindent.js"></script>'."\r\n";
	if( $css = get_var( "SELECT `css` FROM `css` WHERE `uid` = '" . intval(uid()) . "' LIMIT 1" ) )
	{
		?>
		<style type="text/css">
		<?=$css?>
		</style>
		<?php
	}
}

// 创建自定义CSS的OpenAPI
add_action( 'API_MYCSS' , 'api_mycss');
function api_mycss()
{
	$sql = "SELECT `css` FROM `css` WHERE `uid` = '" . intval(uid()) . "' LIMIT 1";
	$data = get_var( $sql ) ;
	if( db_errno() != 0  ) apiController::send_error( LR_API_DB_ERROR , 'DATABASE ERROR ' . db_error() );
	return apiController::send_result( $data );
	
}

// 为user profile 接口追加css数据
add_filter( 'API_USER_PROFILE_OUTPUT_FILTER' , 'user_profile_css' );
function user_profile_css( $data )
{
	$uid = intval($data['id']);
	$data['def_css'] = get_var("SELECT `css` FROM `css` WHERE `uid` = '" . intval($uid) . "' LIMIT 1");
	return $data;
}

