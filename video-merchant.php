<?php
/**
 * Plugin Name: Video Merchant Lite
 * Plugin URI: http://www.MyVideoMerchant.com
 * Description: Plugin that allows you to sell/showcase your videos with built-in HTML5 player.
 * Version: 5.0.4
 * Author: Video Merchant
 * Author URI: http://www.MyVideoMerchant.com
 * Text Domain: video-merchant
 * License: GPL3
 */
/**
 * @package Video Merchant
 * @version 5.0.4
 * @author Video Merchant <info@MyVideoMerchant.com>
 * @copyright (C) Copyright 2015 Video Merchant, MyVideoMerchant.com. All rights reserved.
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.txt

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('ABSPATH') or die('No direct access!');

register_activation_hook(__FILE__, 'video_merchant_db_install');

function video_merchant_setup_admin()
{
	if(current_user_can('manage_options')) 
	{
		add_action('wp_ajax_video_merchant_get_video', 'video_merchant_get_video_json');
		add_action('wp_ajax_video_merchant_get_playlist', 'video_merchant_get_playlist_json');
		add_action('wp_ajax_video_merchant_add_video_file', 'video_merchant_add_video_file');
		add_action('wp_ajax_video_merchant_delete_video_item', 'video_merchant_delete_video_item');
		add_action('wp_ajax_video_merchant_save_settings', 'video_merchant_save_settings');
		add_action('wp_ajax_video_merchant_save_playlist', 'video_merchant_save_playlist');
		add_action('wp_ajax_video_merchant_delete_playlist', 'video_merchant_delete_playlist');
		add_action('wp_ajax_video_merchant_get_order', 'video_merchant_get_order');
		add_action('wp_ajax_video_merchant_change_order_status', 'video_merchant_change_order_status');
		add_action('wp_ajax_video_merchant_get_share_code', 'video_merchant_get_share_code');
		add_action('wp_ajax_video_merchant_get_default_css', 'video_merchant_get_default_css');
		add_action('wp_ajax_video_merchant_video_update_thumbnail_image', 'video_merchant_video_update_thumbnail_image');
		
		if(version_compare(PHP_VERSION, '5.3.0') < 0) 
		{
			add_action('admin_notices', 'video_merchant_php_version_notice');
		}
	}
}

if(is_admin()) 
{
	add_action('admin_menu', 'video_merchant_menu');
	add_action('admin_enqueue_scripts', 'video_merchant_head');
	add_action('admin_init', 'video_merchant_setup_admin', 1);
}

add_action('wp_ajax_video_merchant_html_player', 'video_merchant_html_player');
add_action('wp_ajax_nopriv_video_merchant_html_player', 'video_merchant_html_player');
add_action('wp_ajax_video_merchant_download_free', 'video_merchant_download_free');
add_action('wp_ajax_nopriv_video_merchant_download_free', 'video_merchant_download_free');
add_action('wp_ajax_video_merchant_download', 'video_merchant_download_free');
add_action('wp_ajax_nopriv_video_merchant_download', 'video_merchant_download_free');
add_action('wp_ajax_video_merchant_check_order_status', 'video_merchant_check_order_status');
add_action('wp_ajax_nopriv_video_merchant_check_order_status', 'video_merchant_check_order_status');

$video_merchant_db_version = '5.0.4';

function video_merchant_db_check() 
{
    global $video_merchant_db_version;
	
    if (get_option('video_merchant_db_version') != $video_merchant_db_version) 
	{
        video_merchant_db_install();
    }
}

add_action('plugins_loaded', 'video_merchant_db_check');

function video_merchant_db_install() 
{
	global $wpdb, $video_merchant_db_version;
	
	$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."video_merchant_video (
				video_id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
				video_display_name VARCHAR(255) NOT NULL,
				video_lease_price DECIMAL(10, 2) UNSIGNED NOT NULL,
				video_exclusive_price DECIMAL(10, 2) UNSIGNED NOT NULL,
				video_cover_photo VARCHAR(255) NOT NULL,
				video_file VARCHAR(255) NOT NULL,
				video_file_preview VARCHAR(255) NOT NULL,
				video_lease_additional_file VARCHAR(255) NOT NULL,
				video_exclusive_additional_file VARCHAR(255) NOT NULL,
				video_duration SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
				video_cdate INT UNSIGNED DEFAULT 0 NOT NULL, 
				video_mdate INT UNSIGNED DEFAULT 0 NOT NULL,
				UNIQUE KEY video_id (video_id) 
			) ".$wpdb->get_charset_collate().";";
	$wpdb->query($sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."video_merchant_playlist (
				player_id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
				player_name VARCHAR(255) NOT NULL, 
				player_mode VARCHAR(20) NOT NULL, 
				player_filter_value VARCHAR(255) NOT NULL, 
				player_order_field VARCHAR(50) NOT NULL, 
				player_order_direction VARCHAR(4) NOT NULL, 
				player_cdate INT UNSIGNED DEFAULT 0 NOT NULL, 
				player_mdate INT UNSIGNED DEFAULT 0 NOT NULL,
				UNIQUE KEY player_id (player_id) 
			) ".$wpdb->get_charset_collate().";";
	$wpdb->query($sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."video_merchant_order (
				order_id CHAR(32) NOT NULL, 
				user_id INT UNSIGNED NOT NULL, 
				order_transaction_id VARCHAR(25) NOT NULL, 
				order_status VARCHAR(30) NOT NULL, 
				order_customer_name VARCHAR(255) NOT NULL,
				order_customer_email VARCHAR(255) NOT NULL,
				order_grand_total DECIMAL(10, 2) UNSIGNED NOT NULL,
				video_id INT UNSIGNED NOT NULL,
				order_license_type VARCHAR(20) NOT NULL, 
				order_cdate INT UNSIGNED DEFAULT 0 NOT NULL, 
				order_mdate INT UNSIGNED DEFAULT 0 NOT NULL,
				UNIQUE KEY order_id (order_id) 
			) ".$wpdb->get_charset_collate().";";
	$wpdb->query($sql);
	
	update_option('video_merchant_db_version', $video_merchant_db_version);
}

function video_merchant_get_default_css()
{
	wp_send_json(array('result' => get_option('css_frontend_default')));
}

function video_merchant_get_share_code()
{
	wp_send_json(array('html' => video_merchant_render_player((int)@$_GET['video_id'], (int)@$_GET['playlist_id'], 400, 1, false, false)));
}

function video_merchant_change_order_status()
{
	global $wpdb;
	
	$sql = 'UPDATE '.$wpdb->prefix.'video_merchant_order SET order_status = %s, order_mdate = %d 
			WHERE order_id = %s;';
	
	wp_send_json(array('result' => $wpdb->query($wpdb->prepare($sql, $_GET['new_status'], (int)current_time('timestamp'), $_GET['t']))));
}

function video_merchant_video_update_thumbnail_image()
{
	global $wpdb;
	
	$result = false;
	
	if(isset($_POST['video_id']) && isset($_POST['new_thumb_img']))
	{
		$uploadDir = wp_upload_dir();
		$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
		$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
		
		$data = $_POST['new_thumb_img'];
		
		list($type, $data) = explode(';', $data);
		list(, $data)      = explode(',', $data);
		$data = base64_decode($data);
		
		$newFilename = substr(md5(uniqid(rand(), true)), 0, 25).'.png';
		
		if(file_put_contents($uploadDir.DIRECTORY_SEPARATOR.$newFilename, $data))
		{
			$sql = 'UPDATE '.$wpdb->prefix.'video_merchant_video SET video_cover_photo = %s 
					WHERE video_id = %d;';
			
			$result = $wpdb->query($wpdb->prepare($sql, $newFilename, (int)$_POST['video_id']));
		}
	}
	
	wp_send_json(array('result' => $result));
}

function video_merchant_get_order() 
{
	global $wpdb;
	
	$orderByColumn = 'o.order_cdate';
	$orderByDirection = 'DESC';
	
	if(isset($_GET['order']) && isset($_GET['order'][0]['column']))
	{
		switch((int)$_GET['order'][0]['column'])
		{
			case 0:
				$orderByColumn = 'o.order_id';
				break;
			
			case 1:
				$orderByColumn = 'o.user_id';
				break;
			
			case 2:
				$orderByColumn = 'o.order_transaction_id';
				break;
			
			case 3:
				$orderByColumn = 'o.order_customer_name';
				break;
			
			case 4:
				$orderByColumn = 'o.order_customer_email';
				break;
			
			case 5:
				$orderByColumn = 'o.order_status';
				break;
			
			case 6:
				$orderByColumn = 'o.order_grand_total';
				break;
			
			case 7:
				$orderByColumn = 'o.video_id';
				break;
				
			case 8:
				$orderByColumn = 'a.video_display_name';
				break;
				
			case 9:
				$orderByColumn = 'o.order_license_type';
				break;
				
			case 10:
				$orderByColumn = 'o.order_cdate';
				break;
				
			case 11:
				$orderByColumn = 'o.order_mdate';
				break;
		}
		
		if($_GET['order'][0]['dir'] == 'asc' || $_GET['order'][0]['dir'] == 'desc')
		{
			$orderByDirection = $_GET['order'][0]['dir'];
		}
	}
	
	$whereClause = '';
	
	if(isset($_GET['search']['value']) && !empty($_GET['search']['value']))
	{
		$searchTxtInt = (int)$_GET['search']['value'];
		$searchTxt = esc_sql(htmlentities($_GET['search']['value'], ENT_QUOTES));
		$whereClause = 'WHERE o.order_id LIKE \'%'.$searchTxt.'%\' OR o.order_transaction_id LIKE \'%'.$searchTxt.'%\' OR o.order_customer_name LIKE \'%'.$searchTxt.'%\' OR o.order_customer_email LIKE \'%'.$searchTxt.'%\' OR o.order_status LIKE \'%'.$searchTxt.'%\' OR o.video_id = '.(string)$searchTxtInt.' OR a.video_display_name LIKE \'%'.$searchTxt.'%\' OR o.order_license_type LIKE \'%'.$searchTxt.'%\' ';
		
		if($searchTxtInt > 0)
		{
			$whereClause .= ' OR o.user_id = '.(string)$searchTxtInt.' ';
		}
	}
	
	$limitClause = '';
	
	if(isset($_GET['length']) && (int)$_GET['length'] > 0)
	{
		$limitClause = 'LIMIT '.(int)$_GET['start'].', '.(int)$_GET['length'];
	}
	
	$currencySymbol = video_merchant_get_locale_currency_symbol(get_locale(), video_merchant_get_setting('currency'));
	
	$sql = 'SELECT SQL_CALC_FOUND_ROWS 
				o.order_id,
				o.user_id,
				o.order_transaction_id,
				o.order_customer_name,
				o.order_customer_email,
				o.order_status,
				CONCAT(\''.$currencySymbol.'\', o.order_grand_total) AS \'order_grand_total\',
				o.video_id, 
				a.video_display_name, 
				o.order_license_type,
				FROM_UNIXTIME(o.order_cdate, \'%c/%e/%y %k:%i\'),
				FROM_UNIXTIME(o.order_mdate, \'%c/%e/%y %k:%i\') 
			FROM '.$wpdb->prefix.'video_merchant_order o 
			LEFT JOIN '.$wpdb->prefix.'video_merchant_video a 
			ON a.video_id = o.video_id 
			'.$whereClause.' 
			ORDER BY '.$orderByColumn.' '.$orderByDirection.' 
			'.$limitClause.';';
	$results = $wpdb->get_results($sql, ARRAY_N);
	
	$totalRecords = $wpdb->get_var('SELECT FOUND_ROWS();');
	
	$result = array(
		'draw' => $_GET['draw'],
		'recordsTotal' => $totalRecords,
		'recordsFiltered' => $totalRecords,
		'data' => $results
	);
	
	wp_send_json($result);
}

function video_merchant_check_order_status()
{
	global $wpdb;
	
	$result = array(
		'login_redirect' => false,
		'msg' => '',
		'data' => null
	);
	
	$orderId = $_GET['t'];
	
	$sql = 'SELECT 
				o.order_id,
				o.user_id,
				o.order_transaction_id,
				o.order_status,
				o.order_customer_name,
				o.order_customer_email,
				o.order_grand_total,
				o.video_id,
				o.order_license_type,
				o.order_cdate, 
				IF(o.order_license_type = \'EXCLUSIVE\', a.video_exclusive_additional_file, a.video_lease_additional_file) AS \'additional_file\' 
			FROM '.$wpdb->prefix.'video_merchant_order o 
			LEFT JOIN '.$wpdb->prefix.'video_merchant_video a 
			ON a.video_id = o.video_id 
			WHERE o.order_id = %s 
			AND o.order_status = \'Completed\' 
			LIMIT 1;';
	
	$orderRecord = $wpdb->get_row($wpdb->prepare($sql, $orderId), ARRAY_A);
	
	if(!empty($orderRecord))
	{
		if((int)video_merchant_get_setting('purchase_user_login_required') > 0)
		{
			if($orderRecord['user_id'] > 0 && (int)$orderRecord['user_id'] != (int)get_current_user_id() && !current_user_can('manage_options'))
			{
				$result['msg'] = __('Please login to your account first.', 'video-merchant');
				$result['login_redirect'] = true;
			}
			elseif($orderRecord['user_id'] > 0 && (int)$orderRecord['user_id'] == (int)get_current_user_id())
			{
				$result['data'] = $orderRecord;
			}
			elseif($orderRecord['user_id'] < 1)
			{
				$result['data'] = $orderRecord;
			}
			else
			{
				$result['data'] = $orderRecord;
			}
		}
		else
		{
			$result['data'] = $orderRecord;
		}
		
		if(!empty($result['data']))
		{
			if(!empty($result['data']['additional_file']))
			{
				$result['data']['additional_file'] = true;
			}
			else
			{
				$result['data']['additional_file'] = false;
			}
		}
	}
	
	wp_send_json($result);
}

function video_merchant_download_free()
{
	global $wpdb;
	
	$uploadDir = wp_upload_dir();
	$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
	$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
	
	$defaultErrorMsg = __('This download is no longer available.', 'video-merchant');
	
	$sql = '';
	
	if(isset($_GET['t']))
	{
		if((int)video_merchant_get_setting('purchase_user_login_required') > 0 && !current_user_can('manage_options'))
		{
			$userFilterSql = ' AND o.user_id IN ('.(int)get_current_user_id().', 0) ';
		}
		else
		{
			$userFilterSql = '';
		}
		
		if((int)video_merchant_get_setting('temp_download_link_expiration') > 0)
		{
			$dateFilterSql = ' AND o.order_mdate >= '.((int)current_time('timestamp')-(86400*(int)video_merchant_get_setting('temp_download_link_expiration'))).' ';
		}
		else
		{
			$dateFilterSql = '';
		}
		
		$sql = 'SELECT 
					o.order_id,
					o.user_id,
					o.order_transaction_id,
					o.order_status,
					o.order_customer_name,
					o.order_customer_email,
					o.order_grand_total,
					o.video_id,
					o.order_license_type, 
					a.video_file, 
					IF(o.order_license_type = \'EXCLUSIVE\', a.video_exclusive_additional_file, a.video_lease_additional_file) AS \'additional_file\' 
				FROM '.$wpdb->prefix.'video_merchant_order o 
				INNER JOIN '.$wpdb->prefix.'video_merchant_video a 
				ON a.video_id = o.video_id 
				WHERE o.order_id = \''.esc_sql($_GET['t']).'\' 
				AND o.order_status = \'Completed\' 
				'.$userFilterSql.' 
				'.$dateFilterSql.' 
				LIMIT 1;';
	}
	elseif(isset($_GET['video_id']))
	{
		$videoId = (int)$_GET['video_id'];
		
		if((int)video_merchant_get_setting('download_user_login_required') > 0 && (int)get_current_user_id() < 1)
		{
			$videoId = 0;
		}
		
		$sql = 'SELECT 
					video_file, 
					video_lease_additional_file AS \'additional_file\' 
				FROM '.$wpdb->prefix.'video_merchant_video 
				WHERE video_id = '.$videoId.' 
				AND video_lease_price = 0.00 
				AND video_exclusive_price = 0.00 
				LIMIT 1;';
	}
	
	$videoRecord = $wpdb->get_row($sql, ARRAY_A);
	
	if(!empty($videoRecord))
	{
		$downloadFile = $videoRecord['additional_file'];
		
		if(preg_match('@^https?://@i', $downloadFile))
		{
			$displayName = basename($downloadFile);
			$fullPathVideo = $downloadFile;
		}
		else
		{
			$displayName = preg_replace('@-[^-]+?(\.[^\.]+?)$@', '$1', $downloadFile, 1);
			$fullPathVideo = $uploadDir.DIRECTORY_SEPARATOR.$downloadFile;
		}
		
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"".$displayName."\"");
		
		readfile($fullPathVideo);
	}
	else
	{
		echo '<!DOCTYPE html>
				<html><body><script>
				alert(\''.$defaultErrorMsg.'\');
				window.history.go(-1);
				</script></body></html>';
	}
	
	wp_die();
}

function video_merchant_html_player()
{
	global $wpdb;
	
	$uploadDir = wp_upload_dir();
	$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
	$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
	
	$videoRecords = array();
	
	if(isset($_GET['playlist_id']))
	{
		$_GET['player_id'] = $_GET['playlist_id'];
	}
	
	if(isset($_GET['video_ids']))
	{
		$_GET['video_id'] = $_GET['video_ids'];
	}
	
	$sql = 'SELECT DISTINCT video_id FROM '.$wpdb->prefix.'video_merchant_order WHERE order_status = \'Completed\' AND order_license_type = \'EXCLUSIVE\';';
	
	$tmpExclusiveItems = $wpdb->get_results($sql, ARRAY_A);
	
	$exclusiveItemsExclude = array(0);
	
	if(!empty($tmpExclusiveItems))
	{
		foreach($tmpExclusiveItems as $key => $row)
		{
			$exclusiveItemsExclude[] = $row['video_id'];
		}
	}
	
	if((int)video_merchant_get_setting('exclusive_removed') > 0)
	{
		$excludeClause = ' AND video_id NOT IN (SELECT DISTINCT video_id FROM '.$wpdb->prefix.'video_merchant_order WHERE order_status = \'Completed\' AND order_license_type = \'EXCLUSIVE\') ';
	}
	else
	{
		$excludeClause = '';
	}
	
	if(isset($_GET['player_id']) && !empty($_GET['player_id']) && (int)$_GET['player_id'] > 0)
	{
		$playerId = (int)$_GET['player_id'];
		
		$sql = 'SELECT 
					player_id,
					player_mode,
					player_filter_value,
					player_order_field,
					player_order_direction 
				FROM '.$wpdb->prefix.'video_merchant_playlist 
				WHERE player_id = '.$playerId.' 
				LIMIT 1;';
		
		$htmlPlayerRecord = $wpdb->get_row($sql, ARRAY_A);
		
		if(!empty($htmlPlayerRecord))
		{
			switch($htmlPlayerRecord['player_mode'])
			{
				case 'all':
					$sql = 'SELECT 
								video_id, 
								video_display_name,
								video_lease_price, 
								video_exclusive_price,
								video_cover_photo,
								video_file,
								video_file_preview, 
								video_duration 
							FROM '.$wpdb->prefix.'video_merchant_video 
							WHERE 1 = 1 
							'.$excludeClause.' 
							ORDER BY '.esc_sql($htmlPlayerRecord['player_order_field']).' '.esc_sql($htmlPlayerRecord['player_order_direction']).';';
					
					$videoRecords = $wpdb->get_results($sql, ARRAY_A);
					break;
				
				case 'selected':
					$sql = 'SELECT 
								video_id, 
								video_display_name,
								video_lease_price, 
								video_exclusive_price,
								video_cover_photo,
								video_file,
								video_file_preview, 
								video_duration 
							FROM '.$wpdb->prefix.'video_merchant_video 
							WHERE video_id IN ('.esc_sql(trim($htmlPlayerRecord['player_filter_value'], ' ,')).') 
							'.$excludeClause.' 
							ORDER BY FIELD(video_id, '.esc_sql(trim($htmlPlayerRecord['player_filter_value'], ' ,')).');';
					
					$videoRecords = $wpdb->get_results($sql, ARRAY_A);
					break;
				
				case 'text_match':
					$sql = 'SELECT 
								video_id, 
								video_display_name,
								video_lease_price, 
								video_exclusive_price,
								video_cover_photo,
								video_file,
								video_file_preview, 
								video_duration 
							FROM '.$wpdb->prefix.'video_merchant_video 
							WHERE video_display_name LIKE \'%'.esc_sql(trim($htmlPlayerRecord['player_filter_value'])).'%\' 
							'.$excludeClause.' 
							ORDER BY '.esc_sql($htmlPlayerRecord['player_order_field']).' '.esc_sql($htmlPlayerRecord['player_order_direction']).';';
					
					$videoRecords = $wpdb->get_results($sql, ARRAY_A);
					break;
			}
		}
	}
	elseif(isset($_GET['video_id']) && !empty($_GET['video_id']) && preg_match('@^[0-9,\s]+$@', $_GET['video_id']))
	{
		$videoIds = trim($_GET['video_id'], ' ,');
		
		$sql = 'SELECT 
				video_id, 
				video_display_name,
				video_lease_price, 
				video_exclusive_price,
				video_cover_photo,
				video_file,
				video_file_preview, 
				video_duration 
			FROM '.$wpdb->prefix.'video_merchant_video 
			WHERE video_id IN ('.$videoIds.') 
			'.$excludeClause.' 
			ORDER BY FIELD(video_id, '.$videoIds.');';
		
		$videoRecords = $wpdb->get_results($sql, ARRAY_A);
	}
	else
	{
		$sql = 'SELECT 
				video_id, 
				video_display_name,
				video_lease_price, 
				video_exclusive_price,
				video_cover_photo,
				video_file,
				video_file_preview, 
				video_duration 
			FROM '.$wpdb->prefix.'video_merchant_video 
			WHERE video_id > 0 
			'.$excludeClause.';';
		
		$videoRecords = $wpdb->get_results($sql, ARRAY_A);
	}
	
	$height = (int)@$_GET['height'];
	$currencySymbol = video_merchant_get_locale_currency_symbol(get_locale(), video_merchant_get_setting('currency'));
	$payPalEmail = video_merchant_get_setting('paypal_email');
	
	if(isset($_GET['current_url']) && !empty($_GET['current_url']))
	{
		$currentUrl = $_GET['current_url'];
	}
	else
	{
		$currentUrl = null;
	}
	
	$buyToken = strtoupper(md5(uniqid(rand(), true)));
	
	if(!empty($videoRecords))
	{
		foreach($videoRecords as $key => $row)
		{
			if(in_array($row['video_id'], $exclusiveItemsExclude))
			{
				$videoRecords[$key]['is_sold_exclusive'] = true;
			}
			else
			{
				$videoRecords[$key]['is_sold_exclusive'] = false;
			}
		}
	}
	
	require_once __DIR__.DIRECTORY_SEPARATOR.'video-merchant-player.php';
	
	wp_die();
}

function video_merchant_delete_playlist() 
{
	global $wpdb;
	
	$result = array(
		'success' => $wpdb->delete($wpdb->prefix.'video_merchant_playlist', array('player_id' => (int)$_GET['player_id']), array('%d')),
		'errors' => array()
	);
	
	wp_send_json($result);
}

function video_merchant_get_video_json()
{
	return video_merchant_get_video();
}

function video_merchant_get_playlist_json()
{
	return video_merchant_get_playlist();
}

function video_merchant_get_playlist($sendJSON=true) 
{
	global $wpdb;
	
	if($sendJSON)
	{
		$orderByColumn = 'player_cdate';
		$orderByDirection = 'DESC';
	}
	else
	{
		$orderByColumn = 'player_id';
		$orderByDirection = 'ASC';
	}
	
	if(isset($_GET['order']) && isset($_GET['order'][0]['column']))
	{
		switch((int)$_GET['order'][0]['column'])
		{
			case 0:
				$orderByColumn = 'player_id';
				break;
			
			case 1:
				$orderByColumn = 'player_name';
				break;
			
			case 2:
				$orderByColumn = 'player_mode';
				break;
			
			case 3:
				$orderByColumn = 'player_filter_value';
				break;
			
			case 4:
				$orderByColumn = 'player_order_field';
				break;
			
			case 5:
				$orderByColumn = 'player_order_direction';
				break;
			
			case 6:
				$orderByColumn = 'player_cdate';
				break;
			
			case 7:
				$orderByColumn = 'player_mdate';
				break;
		}
		
		if($_GET['order'][0]['dir'] == 'asc' || $_GET['order'][0]['dir'] == 'desc')
		{
			$orderByDirection = $_GET['order'][0]['dir'];
		}
	}
	
	$whereClause = '';
	
	if(isset($_GET['search']['value']) && !empty($_GET['search']['value']))
	{
		$searchTxtInt = (int)$_GET['search']['value'];
		$searchTxt = esc_sql(htmlentities($_GET['search']['value'], ENT_QUOTES));
		$whereClause = 'WHERE player_id = '.(string)$searchTxtInt.' OR player_name LIKE \'%'.$searchTxt.'%\' OR player_filter_value LIKE \'%'.$searchTxt.'%\' ';
	}
	
	$limitClause = '';
	
	if(isset($_GET['length']) && (int)$_GET['length'] > 0)
	{
		$limitClause = 'LIMIT '.(int)$_GET['start'].', '.(int)$_GET['length'];
	}
	
	$sql = 'SELECT SQL_CALC_FOUND_ROWS 
				player_id, 
				player_name,
				player_mode,
				player_filter_value,
				player_order_field,
				player_order_direction, 
				FROM_UNIXTIME(player_cdate, \'%c/%e/%y %k:%i\'),
				FROM_UNIXTIME(player_mdate, \'%c/%e/%y %k:%i\') 
			FROM '.$wpdb->prefix.'video_merchant_playlist 
			'.$whereClause.' 
			ORDER BY '.$orderByColumn.' '.$orderByDirection.' 
			'.$limitClause.';';

	$results = $wpdb->get_results($sql, ARRAY_N);
	
	$totalRecords = $wpdb->get_var('SELECT FOUND_ROWS();');
	
	$result = array(
		'draw' => (int)@$_GET['draw'],
		'recordsTotal' => $totalRecords,
		'recordsFiltered' => $totalRecords,
		'data' => $results
	);
	
	if($sendJSON)
	{
		wp_send_json($result);
	}
	else
	{
		return $result;
	}
}

function video_merchant_save_playlist()
{
	global $wpdb;
	
	$_POST = stripslashes_deep($_POST);
	
	$result = array(
		'errors' => array()
	);
	
	$filterValue = '';
	
	switch($_POST['player_mode'])
	{
		case 'all':
			break;
		
		case 'selected':
			if(isset($_POST['player_selected_video_ids']) && !empty($_POST['player_selected_video_ids']))
			{
				$filterValue = implode(',', $_POST['player_selected_video_ids']);
				
				if(!preg_match('@^[0-9,]+$@', $filterValue))
				{
					$result['errors'][] = __('Please select at least one video file', 'video-merchant');
				}
			}
			else
			{
				$result['errors'][] = __('Please select at least one video file', 'video-merchant');
			}
			
			break;
		
		case 'text_match':
			if(!empty($_POST['player_mode_text_value']))
			{
				$filterValue = $_POST['player_mode_text_value'];
			}
			else
			{
				$result['errors'][] = __('Please specify a match text', 'video-merchant');
			}
			
			break;
		
		default:
			$result['errors'][] = __('Unknown error', 'video-merchant');
			break;
	}
	
	$orderByField = 'video_display_name';
	
	if(isset($_POST['player_display_order']))
	{
		switch((int)$_POST['player_display_order'])
		{
			case 1:
				$orderByField = 'video_display_name';
				break;

			case 2:
				$orderByField = 'video_lease_price';
				break;

			case 3:
				$orderByField = 'video_exclusive_price';
				break;

			case 4:
				$orderByField = 'video_duration';
				break;

			case 5:
				$orderByField = 'video_cdate';
				break;

			case 6:
				$orderByField = 'video_mdate';
				break;
		}
	}
	
	$orderByDirection = 'ASC';
	
	if(isset($_POST['player_display_order_direction']) && ($_POST['player_display_order_direction'] == 'ASC' || $_POST['player_display_order_direction'] == 'DESC'))
	{
		$orderByDirection = $_POST['player_display_order_direction'];
	}
	
	if(empty($result['errors']))
	{
		$playerId = (int)$_POST['player_id'];
		
		$playListName = trim($_POST['playlist_name']);
		
		if(empty($playListName))
		{
			$playListName = __('Playlist Created On ', 'video-merchant').date('m/d/Y');
		}
		
		if($playerId > 0)
		{
			if(!$wpdb->update( 
				$wpdb->prefix.'video_merchant_playlist', 
				array(
					'player_name' => htmlentities($playListName, ENT_QUOTES), 
					'player_mode' => $_POST['player_mode'], 
					'player_filter_value' => htmlentities($filterValue, ENT_QUOTES), 
					'player_order_field' => $orderByField, 
					'player_order_direction' => $orderByDirection, 
					'player_mdate' => (int)current_time('timestamp')
				), 
				array('player_id' => $playerId), 
				array(
					'%s', 
					'%s', 
					'%s', 
					'%s', 
					'%s', 
					'%d' 
				), 
				array('%d') 
			))
			{
				$result['errors'][] = __('There was an issue saving to the database.', 'video-merchant');
			}
		}
		else
		{
			if(!$wpdb->insert( 
				$wpdb->prefix.'video_merchant_playlist', 
				array( 
					'player_name' => htmlentities($playListName, ENT_QUOTES), 
					'player_mode' => $_POST['player_mode'], 
					'player_filter_value' => htmlentities($filterValue, ENT_QUOTES), 
					'player_order_field' => $orderByField, 
					'player_order_direction' => $orderByDirection, 
					'player_cdate' => (int)current_time('timestamp'), 
					'player_mdate' => (int)current_time('timestamp')
				), 
				array( 
					'%s', 
					'%s', 
					'%s', 
					'%s', 
					'%s', 
					'%d', 
					'%d' 
				) 
			))
			{
				$result['errors'][] = __('There was an issue saving to the database.', 'video-merchant');
			}
		}
	}
	
	wp_send_json($result);
}

function video_merchant_save_settings()
{
	$_POST = stripslashes_deep($_POST);
	
	$result = array(
		'errors' => array()
	);
	
	if(!empty($_POST['paypal_email']) && !is_email($_POST['paypal_email']))
	{
		$result['errors'][] = __('Invalid Paypal Email', 'video-merchant');
	}
	
	if(!isset($_POST['video_merchant_currency']) || empty($_POST['video_merchant_currency']))
	{
		$result['errors'][] = __('Invalid Currency', 'video-merchant');
	}
	
	if(!is_numeric($_POST['temp_download_link_expiration']))
	{
		$result['errors'][] = __('Invalid Link Expiration', 'video-merchant');
	}
	
	if(empty($_POST['css_frontend']))
	{
		$result['errors'][] = __('CSS Styles cannot be empty!', 'video-merchant');
	}
	
	if(!is_writable(plugin_dir_path( __FILE__ ).'video-merchant-html-player-frontend.css'))
	{
		$result['errors'][] = plugin_dir_path( __FILE__ ).'video-merchant-html-player-frontend.css'.__(' is not writable!', 'video-merchant');
	}
	
	if(empty($result['errors']))
	{
		update_option('paypal_email', $_POST['paypal_email']);
		update_option('currency', $_POST['video_merchant_currency']);
		update_option('temp_download_link_expiration', $_POST['temp_download_link_expiration']);
		update_option('download_user_login_required', (int)$_POST['download_user_login_required']);
		update_option('purchase_user_login_required', (int)$_POST['purchase_user_login_required']);
		update_option('email_admin_order_notices', (int)$_POST['email_admin_order_notices']);
		update_option('exclusive_removed', (int)$_POST['exclusive_removed']);
		update_option('show_author_link', (int)$_POST['show_author_link']);
		
		file_put_contents(plugin_dir_path( __FILE__ ).'video-merchant-html-player-frontend.css', $_POST['css_frontend']);
	}
	
	wp_send_json($result);
}

function video_merchant_menu()
{
	add_options_page('Video Merchant', 'Video Merchant', 'manage_options', 'video-merchant', 'video_merchant_options');
}

function video_merchant_options()
{
	if(!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.', 'video-merchant'));
	}

	include dirname(__FILE__).DIRECTORY_SEPARATOR.'video-merchant-control-panel.php';
}

function video_merchant_head($hook)
{
	if(isset($_GET['page']) && 'video-merchant' == $_GET['page'])
	{
		if ( 'classic' == get_user_option( 'admin_color' ) ) {
			$uiCSS = 'jquery-ui-classic.css';
		} else {
			$uiCSS = 'jquery-ui-fresh.css';
		}

		wp_enqueue_style('jquery-datatable-css', video_merchant_make_url_protocol_less(plugins_url('assets/jquery.dataTables.min.css', __FILE__)));
		wp_enqueue_style('jquery-ui-css', video_merchant_make_url_protocol_less(plugins_url($uiCSS, __FILE__)));
		wp_enqueue_style('jquery-ui-datatables-integration-css', video_merchant_make_url_protocol_less(plugins_url('assets/dataTables.jqueryui.css', __FILE__)), array('jquery-ui-css'));
		wp_enqueue_style('video-merchant-css', video_merchant_make_url_protocol_less(plugins_url('video-merchant.css', __FILE__)), array('jquery-ui-datatables-integration-css', 'jquery-datatable-css', 'jquery-ui-css'));

		wp_enqueue_script('jquery-datatable-js', video_merchant_make_url_protocol_less(plugins_url('assets/jquery.dataTables.min.js', __FILE__)), array('jquery', 'jquery-ui-button', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'jquery-ui-tabs'), null, true);
		wp_enqueue_script('jquery-datatable-ui-integration-js', video_merchant_make_url_protocol_less(plugins_url('assets/dataTables.jqueryui.js', __FILE__)), array('jquery-datatable-js'));
		wp_enqueue_script('zero-clipboard-js', video_merchant_make_url_protocol_less(plugins_url('assets/zeroclipboard/ZeroClipboard.min.js', __FILE__)), array(), null, false);
		wp_enqueue_script('video-merchant-js', video_merchant_make_url_protocol_less(plugins_url('video-merchant.js', __FILE__)), array('jquery-ui-sortable', 'jquery-datatable-js', 'jquery-datatable-ui-integration-js'), null, false);
	}
}

function video_merchant_delete_video_item()
{
	global $wpdb;
	
	$uploadDir = wp_upload_dir();
	$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
	$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
	
	$videoId = (int)$_GET['video_id'];
	
	$sql = 'SELECT 
				video_cover_photo,
				video_file,
				video_file_preview,
				video_lease_additional_file,
				video_exclusive_additional_file 
			FROM '.$wpdb->prefix.'video_merchant_video 
			WHERE video_id = '.$videoId.' 
			LIMIT 1;';
	
	$files = $wpdb->get_results($sql, ARRAY_A);
	
	if(!empty($files) && isset($files[0]))
	{
		if(!empty($files[0]['video_file']) && !preg_match('@^https?://@i', $files[0]['video_file']))
		{
			$sql = "SELECT count(*) AS 'thecount' FROM ".$wpdb->prefix."video_merchant_video 
					WHERE video_id <> ".$videoId." AND video_file = %s;";
			
			$fileBeingUsed = (int)$wpdb->get_var($wpdb->prepare($sql, $files[0]['video_file']));
			
			if($fileBeingUsed < 1 && file_exists($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_file']))
			{
				unlink($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_file']);
			}
		}
		
		if(!empty($files[0]['video_file_preview']) && !preg_match('@^https?://@i', $files[0]['video_file_preview']))
		{
			$sql = "SELECT count(*) AS 'thecount' FROM ".$wpdb->prefix."video_merchant_video 
					WHERE video_id <> ".$videoId." AND video_file_preview = %s;";
			
			$fileBeingUsed = (int)$wpdb->get_var($wpdb->prepare($sql, $files[0]['video_file_preview']));
			
			if($fileBeingUsed < 1 && file_exists($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_file_preview']))
			{
				unlink($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_file_preview']);
			}
		}
		
		if(!empty($files[0]['video_cover_photo']) && !preg_match('@^https?://@i', $files[0]['video_cover_photo']))
		{
			$sql = "SELECT count(*) AS 'thecount' FROM ".$wpdb->prefix."video_merchant_video 
					WHERE video_id <> ".$videoId." AND video_cover_photo = %s;";
			
			$fileBeingUsed = (int)$wpdb->get_var($wpdb->prepare($sql, $files[0]['video_cover_photo']));
			
			if($fileBeingUsed < 1 && file_exists($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_cover_photo']))
			{
				unlink($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_cover_photo']);
			}
		}
		
		if(!empty($files[0]['video_lease_additional_file']) && !preg_match('@^https?://@i', $files[0]['video_lease_additional_file']))
		{
			$sql = "SELECT count(*) AS 'thecount' FROM ".$wpdb->prefix."video_merchant_video 
					WHERE video_id <> ".$videoId." AND video_lease_additional_file = %s;";
			
			$fileBeingUsed = (int)$wpdb->get_var($wpdb->prepare($sql, $files[0]['video_lease_additional_file']));
			
			if($fileBeingUsed < 1 && file_exists($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_lease_additional_file']))
			{
				unlink($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_lease_additional_file']);
			}
		}
		
		if(!empty($files[0]['video_exclusive_additional_file']) && !preg_match('@^https?://@i', $files[0]['video_exclusive_additional_file']))
		{
			$sql = "SELECT count(*) AS 'thecount' FROM ".$wpdb->prefix."video_merchant_video 
					WHERE video_id <> ".$videoId." AND video_exclusive_additional_file = %s;";
			
			$fileBeingUsed = (int)$wpdb->get_var($wpdb->prepare($sql, $files[0]['video_exclusive_additional_file']));
			
			if($fileBeingUsed < 1 && file_exists($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_exclusive_additional_file']))
			{
				unlink($uploadDir.DIRECTORY_SEPARATOR.$files[0]['video_exclusive_additional_file']);
			}
		}
	}
	
	$result = array(
		'success' => $wpdb->delete($wpdb->prefix.'video_merchant_video', array('video_id' => $videoId), array('%d'))
	);
	
	wp_send_json($result);
}

function video_merchant_get_video($sendJSON=true, $orderByIds=array()) 
{
	global $wpdb;
	
	$orderByColumn = 'video_display_name';
	$orderByDirection = 'ASC';
	
	if(isset($_GET['order']) && isset($_GET['order'][0]['column']))
	{
		switch((int)$_GET['order'][0]['column'])
		{
			case 0:
				$orderByColumn = 'video_id';
				break;
			
			case 1:
				$orderByColumn = 'video_display_name';
				break;
			
			case 2:
				$orderByColumn = 'video_lease_price';
				break;
			
			case 3:
				$orderByColumn = 'video_exclusive_price';
				break;
			
			case 4:
				$orderByColumn = 'video_cover_photo';
				break;
			
			case 5:
				$orderByColumn = 'video_file';
				break;
			
			case 6:
				$orderByColumn = 'video_file_preview';
				break;
			
			case 7:
				$orderByColumn = 'video_lease_additional_file';
				break;
			
			case 8:
				$orderByColumn = 'video_exclusive_additional_file';
				break;
			
			case 9:
				$orderByColumn = 'video_duration';
				break;
			
			case 10:
				$orderByColumn = 'video_cdate';
				break;
			
			case 11:
				$orderByColumn = 'video_mdate';
				break;
		}
		
		if($_GET['order'][0]['dir'] == 'asc' || $_GET['order'][0]['dir'] == 'desc')
		{
			$orderByDirection = $_GET['order'][0]['dir'];
		}
	}
	
	if(!empty($orderByIds))
	{
		if(is_array($orderByIds))
		{
			$orderByIds = implode(',', $orderByIds);
		}
		
		$orderByColumn = 'FIELD(video_id, '.$orderByIds.')';
		$orderByDirection = 'ASC';
	}
	
	$whereClause = '';
	
	if(isset($_GET['search']['value']) && !empty($_GET['search']['value']))
	{
		$searchTxtInt = (int)$_GET['search']['value'];
		$searchTxt = esc_sql(htmlentities($_GET['search']['value'], ENT_QUOTES));
		$whereClause = 'WHERE video_id = '.(string)$searchTxtInt.' OR video_display_name LIKE \'%'.$searchTxt.'%\' ';
	}
	
	$limitClause = '';
	
	if(isset($_GET['length']) && (int)$_GET['length'] > 0)
	{
		$limitClause = 'LIMIT '.(int)$_GET['start'].', '.(int)$_GET['length'];
	}
	
	$currencySymbol = video_merchant_get_locale_currency_symbol(get_locale(), video_merchant_get_setting('currency'));
	
	$sql = 'SELECT SQL_CALC_FOUND_ROWS 
				video_id, 
				video_display_name,
				CONCAT(\''.$currencySymbol.'\', video_lease_price),
				CONCAT(\''.$currencySymbol.'\', video_exclusive_price),
				video_cover_photo,
				video_file,
				video_file_preview,
				video_lease_additional_file,
				video_exclusive_additional_file,
				video_duration,
				FROM_UNIXTIME(video_cdate, \'%c/%e/%y %k:%i\'),
				FROM_UNIXTIME(video_mdate, \'%c/%e/%y %k:%i\') 
			FROM '.$wpdb->prefix.'video_merchant_video 
			'.$whereClause.' 
			ORDER BY '.$orderByColumn.' '.$orderByDirection.' 
			'.$limitClause.';';
	
	$results = $wpdb->get_results($sql, ARRAY_N);
	
	$totalRecords = $wpdb->get_var('SELECT FOUND_ROWS();');
	
	$result = array(
		'draw' => (int)@$_GET['draw'],
		'recordsTotal' => $totalRecords,
		'recordsFiltered' => $totalRecords,
		'data' => $results
	);
	
	if($sendJSON)
	{
		wp_send_json($result);
	}
	else
	{
		return $result;
	}
}

function video_merchant_make_url_protocol_less($url)
{
	return preg_replace('@^https?:@i', '', $url, 1);
}

function video_merchant_move_uploaded_file_to_inventory($uploadedFile)
{
	$uploadDir = wp_upload_dir();
	$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
	$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
	
	if(!file_exists($uploadDir))
	{
		if(wp_mkdir_p($uploadDir))
		{
			file_put_contents($uploadDir.DIRECTORY_SEPARATOR.'index.html', ' ');
		}
	}
	
	$newFileName = str_replace('\'', '', preg_replace('@(\.[^\.]+?)$@', '-'.substr(uniqid(rand(), true), rand(0, 3), 7).'$1', $uploadedFile['name']));
	
	if(!move_uploaded_file($uploadedFile['tmp_name'], $uploadDir.DIRECTORY_SEPARATOR.$newFileName))
	{
		$newFileName = '';
	}
	
	return $newFileName;
}

function video_merchant_add_video_file()
{
	$_POST = stripslashes_deep($_POST);
	
	global $wpdb;
	
	$uploadDir = wp_upload_dir();
	$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
	$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();
	
	$supportedImageTypes = array('jpg', 'jpeg', 'png', 'gif');
	$supportedVideoExtensions = array('mp4', 'ogg', 'ogv', 'webm');
	
	$result = array(
		'errors' => array()
	);
	
	$displayName = $_POST['video_display_name'];
	$leasePrice = 0;
	$exclusivePrice = 0;
	
	if(!empty($_POST['video_lease_price']) && !is_numeric($_POST['video_lease_price']))
	{
		$result['errors'][] = __('Invalid Video Full Quality Download Price', 'video-merchant');
	}
	elseif(!empty($_POST['video_lease_price']) && is_numeric($_POST['video_lease_price']))
	{
		$leasePrice = $_POST['video_lease_price'];
	}
	
	if(!empty($_POST['video_exclusive_price']) && !is_numeric($_POST['video_exclusive_price']))
	{
		$result['errors'][] = __('Invalid Exclusive Price', 'video-merchant');
	}
	elseif(!empty($_POST['video_exclusive_price']) && is_numeric($_POST['video_exclusive_price']))
	{
		$exclusivePrice = $_POST['video_exclusive_price'];
	}
	
	$coverPhoto = '';
	
	switch($_POST['cover_photo_mode'])
	{
		case 'upload':
			if(isset($_FILES['cover_photo_upload_file']['name']) && !empty($_FILES['cover_photo_upload_file']['name']))
			{
				$fileType = strtolower(end((explode('.', $_FILES['cover_photo_upload_file']['name']))));
				
				if(!in_array($fileType, $supportedImageTypes) || $_FILES['cover_photo_upload_file']['error'] <> 0)
				{
					$result['errors'][] = __('Invalid Video Thumbnail Image', 'video-merchant');
				}
				else
				{
					$coverPhoto = video_merchant_move_uploaded_file_to_inventory($_FILES['cover_photo_upload_file']);
					
					if(empty($coverPhoto))
					{
						$result['errors'][] = __('Invalid Upload Directory Permissions', 'video-merchant');
					}
				}
			}
			
			break;
		
		case 'url':
			if(isset($_POST['cover_photo_url_file']) && !empty($_POST['cover_photo_url_file']))
			{
				if(!preg_match('@^https?://@i', $_POST['cover_photo_url_file']))
				{
					$result['errors'][] = __('Invalid Video Thumbnail Image', 'video-merchant');
				}
				else
				{
					$coverPhoto = $_POST['cover_photo_url_file'];
				}
			}
			
			break;
		
		case 'existing':
			if(isset($_POST['cover_photo_existing_file']) && !empty($_POST['cover_photo_existing_file']))
			{
				$coverPhoto = $_POST['cover_photo_existing_file'];
			}
			
			break;
	}
	
	$previewVideoFile = '';
	
	switch($_POST['preview_video_mode'])
	{
		case 'upload':
			if(isset($_FILES['preview_video_upload_file']['name']) && !empty($_FILES['preview_video_upload_file']['name']))
			{
				$fileType = strtolower(end((explode('.', $_FILES['preview_video_upload_file']['name']))));
				
				if(!in_array($fileType, $supportedVideoExtensions) || $_FILES['preview_video_upload_file']['error'] <> 0)
				{
					$result['errors'][] = __('Invalid Preview Video File', 'video-merchant');
				}
				else
				{
					$previewVideoFile = video_merchant_move_uploaded_file_to_inventory($_FILES['preview_video_upload_file']);
					
					if(empty($previewVideoFile))
					{
						$result['errors'][] = __('Invalid Upload Directory Permissions', 'video-merchant');
					}
				}
			}
			else
			{
				$result['errors'][] = __('Invalid Preview Video File', 'video-merchant');
			}
			
			break;
		
		case 'url':
			if(isset($_POST['preview_video_url_file']) && !empty($_POST['preview_video_url_file']))
			{
				if(!preg_match('@^https?://@i', $_POST['preview_video_url_file']))
				{
					$result['errors'][] = __('Invalid Preview Video File', 'video-merchant');
				}
				else
				{
					$previewVideoFile = $_POST['preview_video_url_file'];
				}
			}
			else
			{
				$result['errors'][] = __('Invalid Preview Video File', 'video-merchant');
			}
			
			break;
		
		case 'existing':
			if(isset($_POST['preview_video_existing_file']) && !empty($_POST['preview_video_existing_file']))
			{
				$previewVideoFile = $_POST['preview_video_existing_file'];
			}
			else
			{
				$result['errors'][] = __('Invalid Preview Video File', 'video-merchant');
			}
			
			break;
			
		default:
			$result['errors'][] = __('Invalid Preview Video File OR you have exceeded your webserver\'s php.ini post_max_size setting which is currently set to '.ini_get('post_max_size').' and/or your upload_max_filesize setting which is currently set to '.ini_get('upload_max_filesize').'. Please check all of the above and try your request again.', 'video-merchant');
			
			break;
	}
	
	$additionalFileLease = '';
	
	switch($_POST['addtional_file_lease_mode'])
	{
		case 'upload':
			if(isset($_FILES['additional_lease_file']['name']) && !empty($_FILES['additional_lease_file']['name']))
			{
				if($_FILES['additional_lease_file']['error'] <> 0)
				{
					$result['errors'][] = __('Invalid Purchase Additional File', 'video-merchant');
				}
				else
				{
					$additionalFileLease = video_merchant_move_uploaded_file_to_inventory($_FILES['additional_lease_file']);
					
					if(empty($additionalFileLease))
					{
						$result['errors'][] = __('Invalid Upload Directory Permissions', 'video-merchant');
					}
				}
			}
			
			break;
		
		case 'url':
			if(isset($_POST['additional_lease_url_file']) && !empty($_POST['additional_lease_url_file']))
			{
				if(!preg_match('@^https?://@i', $_POST['additional_lease_url_file']))
				{
					$result['errors'][] = __('Invalid Purchase Additional File', 'video-merchant');
				}
				else
				{
					$additionalFileLease = $_POST['additional_lease_url_file'];
				}
			}
			
			break;
		
		case 'existing':
			if(isset($_POST['additional_lease_existing_file']) && !empty($_POST['additional_lease_existing_file']))
			{
				$additionalFileLease = $_POST['additional_lease_existing_file'];
			}
			
			break;
	}
	
	$additionalFileExclusive = '';
	
	switch($_POST['addtional_file_exclusive_mode'])
	{
		case 'upload':
			if(isset($_FILES['additional_exclusive_file']['name']) && !empty($_FILES['additional_exclusive_file']['name']))
			{
				if($_FILES['additional_exclusive_file']['error'] <> 0)
				{
					$result['errors'][] = __('Invalid Exclusive Additional File', 'video-merchant');
				}
				else
				{
					$additionalFileExclusive = video_merchant_move_uploaded_file_to_inventory($_FILES['additional_exclusive_file']);
					
					if(empty($additionalFileExclusive))
					{
						$result['errors'][] = __('Invalid Upload Directory Permissions', 'video-merchant');
					}
				}
			}
			
			break;
		
		case 'url':
			if(isset($_POST['additional_exclusive_url_file']) && !empty($_POST['additional_exclusive_url_file']))
			{
				if(!preg_match('@^https?://@i', $_POST['additional_exclusive_url_file']))
				{
					$result['errors'][] = __('Invalid Exclusive Additional File', 'video-merchant');
				}
				else
				{
					$additionalFileExclusive = $_POST['additional_exclusive_url_file'];
				}
			}
			
			break;
		
		case 'existing':
			if(isset($_POST['additional_exclusive_existing_file']) && !empty($_POST['additional_exclusive_existing_file']))
			{
				$additionalFileExclusive = $_POST['additional_exclusive_existing_file'];
			}
			
			break;
	}
	
	if(empty($additionalFileLease))
	{
		$result['errors'][] = __('File To Provide Required', 'video-merchant');
	}
	
	if(empty($result['errors']))
	{
		if(empty($displayName))
		{
			if(preg_match('@^https?://@i', $previewVideoFile))
			{
				$displayName = trim(preg_replace('@\.[^\.]+?$@i', '', urldecode(basename($previewVideoFile)), 1));
			}
			else
			{
				$displayName = trim(preg_replace('@-[^-]+?$@i', '', urldecode(basename($previewVideoFile)), 1));
			}
			
			if(empty($displayName))
			{
				$displayName = (int)current_time('timestamp');
			}
		}
		
		if(empty($result['errors']))
		{
			if(empty($coverPhoto))
			{
				if(preg_match('@^https?://@i', $previewVideoFile))
				{
					$result['full_quality_video_url'] = video_merchant_make_url_protocol_less($previewVideoFile);
				}
				else
				{
					$result['full_quality_video_url'] = video_merchant_make_url_protocol_less($uploadUrl.'/'.$previewVideoFile);
				}
			}
			
			if((int)$_POST['editing_video_id'] < 1)
			{
				$saveToDBSuccess = $wpdb->insert( 
					$wpdb->prefix.'video_merchant_video', 
					array( 
						'video_display_name' => htmlentities($displayName, ENT_QUOTES),
						'video_lease_price' => $leasePrice,
						'video_exclusive_price' => $exclusivePrice,
						'video_cover_photo' => $coverPhoto,
						'video_file' => $previewVideoFile,
						'video_file_preview' => $previewVideoFile,
						'video_lease_additional_file' => $additionalFileLease,
						'video_exclusive_additional_file' => $additionalFileExclusive,
						'video_duration' => 0,
						'video_cdate' => (int)current_time('timestamp'),
						'video_mdate' => (int)current_time('timestamp')
					), 
					array( 
						'%s', 
						'%f', 
						'%f', 
						'%s', 
						'%s', 
						'%s', 
						'%s', 
						'%s', 
						'%d', 
						'%d', 
						'%d'
					)
				);
				
				$result['video_id'] = (int)$wpdb->insert_id;
			}
			else
			{
				$result['video_id'] = (int)$_POST['editing_video_id'];
						
				$saveToDBSuccess = $wpdb->update( 
					$wpdb->prefix.'video_merchant_video', 
					array( 
						'video_display_name' => htmlentities($displayName, ENT_QUOTES),
						'video_lease_price' => $leasePrice,
						'video_exclusive_price' => $exclusivePrice,
						'video_cover_photo' => $coverPhoto,
						'video_file' => $previewVideoFile,
						'video_file_preview' => $previewVideoFile,
						'video_lease_additional_file' => $additionalFileLease,
						'video_exclusive_additional_file' => $additionalFileExclusive,
						'video_duration' => 0,
						'video_mdate' => (int)current_time('timestamp')
					), 
					array('video_id' => (int)$_POST['editing_video_id']), 
					array( 
						'%s', 
						'%f', 
						'%f', 
						'%s', 
						'%s', 
						'%s', 
						'%s', 
						'%s', 
						'%d', 
						'%d'
					),
					array('%d') 
				);
			}
			
			if((int)$saveToDBSuccess < 1)
			{
				$result['errors'][] = __('There was an issue saving this video file to the database. Please check the data you entered and/or your database server and try your request again.', 'video-merchant');
			}
		}
	}
	
	wp_send_json($result);
}

function video_merchant_get_video_file_duration($videoFilePath, $inSeconds=true) 
{
	$duration = 0;
	
	$metadata = wp_read_video_metadata($videoFilePath);
	
	if(isset($metadata['length']))
	{
		$duration = $metadata['length'];
		
		if(!$inSeconds)
		{
			$duration = ltrim((string)gmdate('i:s', $duration), '0');
		}
	}
	
	return $duration;
}

function video_merchant_get_locale_currency_symbol($locale, $currency)
{
	if(class_exists('NumberFormatter'))
	{
		// Create a NumberFormatter
		$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

		// Figure out what 0.00 looks like with the currency symbol
		$withCurrency = $formatter->formatCurrency(0, $currency);

		// Figure out what 0.00 looks like without the currency symbol
		$formatter->setPattern(str_replace('¤', '', $formatter->getPattern()));
		$withoutCurrency = $formatter->formatCurrency(0, $currency);

		// Extract just the currency symbol from the first string
		return str_replace($withoutCurrency, '', $withCurrency);
	}
    else 
	{
		return '';
	}
}

function video_merchant_get_setting($settingName)
{
	$defaultSettings = array(
		'paypal_email' => '',
		'currency' => 'USD',
		'temp_download_link_expiration' => 2,
		'download_user_login_required' => 0,
		'purchase_user_login_required' => 0, 
		'email_admin_order_notices' => 0,
		'exclusive_removed' => 0,
		'show_author_link' => 0
	);
	
	return get_option($settingName, $defaultSettings[$settingName]);
}

function video_merchant_render_player($videoIds=array(), $playerId=0, $height=400, $autoPlay=0, $includeCurrentUrl=true, $useXHTML=true)
{
	$playerId = (int)$playerId;
	$height = (int)$height;
	$autoPlay = (int)$autoPlay;
	
	$currentUrl = '';
	
	if($includeCurrentUrl)
	{
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') 
		{ 
			$currentUrl .= 'https:';
		} 
		else 
		{ 
			$currentUrl .= 'http:';
		}

		$currentUrl .= '//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	else
	{
		$currentUrl = get_site_url();
	}
	
	if($useXHTML)
	{
		$urlDivider = '&amp;';
	}
	else
	{
		$urlDivider = '&';
	}
	
	$html = '';
	
	if($playerId > 0)
	{
		$html = '<iframe width="100%" height="'.(string)$height.'" scrolling="no" frameborder="no" src="'.video_merchant_make_url_protocol_less(admin_url('admin-ajax.php?action=video_merchant_html_player'.$urlDivider.'playlist_id='.(string)$playerId.$urlDivider.'height='.(string)$height.$urlDivider.'autoplay='.(string)$autoPlay.$urlDivider.'current_url='.urlencode($currentUrl))).'"></iframe>';
	}
	elseif(!empty($videoIds))
	{
		if(is_array($videoIds))
		{
			$videoIds = implode(',', $videoIds);
		}
		
		$html = '<iframe width="100%" height="'.(string)$height.'" scrolling="no" frameborder="no" src="'.video_merchant_make_url_protocol_less(admin_url('admin-ajax.php?action=video_merchant_html_player'.$urlDivider.'video_id='.(string)$videoIds.$urlDivider.'height='.(string)$height.$urlDivider.'autoplay='.(string)$autoPlay.$urlDivider.'current_url='.urlencode($currentUrl))).'"></iframe>';
	}
	else
	{
		$html = '<iframe width="100%" height="'.(string)$height.'" scrolling="no" frameborder="no" src="'.video_merchant_make_url_protocol_less(admin_url('admin-ajax.php?action=video_merchant_html_player'.$urlDivider.'video_id='.$urlDivider.'height='.(string)$height.$urlDivider.'autoplay='.(string)$autoPlay.$urlDivider.'current_url='.urlencode($currentUrl))).'"></iframe>';
	}
	
	if((int)video_merchant_get_setting('show_author_link') > 0)
	{
		$authorLink = get_option('vm_author_link');
		
		if(empty($authorLink))
		{
			$sponsoredLinks = array('<a href="http://www.myvideomerchant.com/">Video Player</a>', '<a href="http://www.myvideomerchant.com/">HTML5 Video Player</a>', '<a href="http://www.myvideomerchant.com/">Sell Videos</a>', '<a href="http://www.myaudiomerchant.com/">Audio Player</a>', '<a href="http://www.myaudiomerchant.com/">HTML5 Audio Player</a>', '<a href="http://www.myaudiomerchant.com/">Sell Audio</a>', '<a href="http://www.persianmobproductions.com/">Beats</a>', '<a href="http://www.persianmobproductions.com/">Rap Instrumentals</a>', '<a href="http://www.persianmobproductions.com/">Rap Beats</a>', '<a href="http://www.persianmobproductions.com/">Hip Hop Instrumentals</a>', '<a href="http://www.persianmobproductions.com/">Hip Hop Beats</a>', '<a href="http://www.persianmobproductions.com/">Buy Rap Beats</a>', '<a href="http://www.persianmobproductions.com/">Industry-Ready Rap Beats</a>');
			
			$authorLink = $sponsoredLinks[array_rand($sponsoredLinks, 1)];
			
			update_option('vm_author_link', $authorLink);
		}
		
		$html .= '<div style="width: 200px; float: right; display: block; clear: both; margin: 0; padding: 0 30px 0 0; text-align: right; font-size: 11px;">'.$authorLink.'</div>';
	}
	
	return $html;
}

function video_merchant_php_version_notice() 
{
	$class = 'error';
	$message = sprintf(__('You are currently using PHP version %s. This plugin requires PHP version 5.3.0 or greator. Please contact your system admin to update.', 'video-merchant'), PHP_VERSION);
	
	echo "<div class=\"$class\"><p>$message</p></div>"; 
}

class Video_Merchant_Widget extends WP_Widget
{
	function __construct()
	{
		parent::__construct(
			'video_merchant_widget',
			__('Video Merchant', 'video-merchant'),
			array('description' => __('Video Merchant Widget.', 'video-merchant'))
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance)
	{
		echo $args['before_widget'];
		echo $args['before_title'].$args['after_title'];
		
		if(((isset($instance['video_ids']) && !empty($instance['video_ids'])) || (isset($instance['player_id']) && !empty($instance['player_id']))) && isset($instance['height']) && isset($instance['auto_play']))
		{
			echo video_merchant_render_player($instance['video_ids'], $instance['player_id'], $instance['height'], $instance['auto_play']);
		}
		
		echo $args['after_widget'];
	}
	
	public function form($instance)
	{
		$videoIds = (isset($instance['video_ids']) && !empty($instance['video_ids'])) ? explode(',', $instance['video_ids']) : array();
		$playerId = (isset($instance['player_id']) && !empty($instance['player_id'])) ? (int)$instance['player_id'] : 0;
		$height = (isset($instance['height']) && !empty($instance['height'])) ? (int)$instance['height'] : 400;
		$autoPlay = (isset($instance['auto_play']) && !empty($instance['auto_play'])) ? (int)$instance['auto_play'] : 0;
		
		$htmlPlayers = video_merchant_get_playlist(false);
		$video = video_merchant_get_video(false, $videoIds);
		
		wp_enqueue_script('jquery-ui-sortable');
?>
		<p>
			<label for="<?php echo $this->get_field_id('player_id'); ?>"><?php _e('Playlist:', 'video-merchant'); ?></label> 
			<br />
			<select onchange="javascript: jQuery('input[name=\'<?php echo $this->get_field_name('video_ids'); ?>[]\']').prop('checked', false);" class="widefat" id="<?php echo $this->get_field_id('player_id'); ?>" name="<?php echo $this->get_field_name('player_id'); ?>">
				<option value=""><?php _e('- Select Playlist -', 'video-merchant'); ?></option>
			<?php 
			if(!empty($htmlPlayers['data']))
			{
				foreach($htmlPlayers['data'] as $htmlPlayer)
				{
			?>
				<option value="<?php echo $htmlPlayer[0]; ?>"<?php if((int)$playerId == (int)$htmlPlayer[0]) { ?> selected="selected"<?php } ?>><?php echo $htmlPlayer[1]; ?> (ID: <?php echo $htmlPlayer[0]; ?>)</option>
			<?php
				}
			}
			?>
			</select>
			<br />
			<div class="big_or2"><?php _e('OR', 'video-merchant'); ?></div>
			<style>
				.big_or2 {
					text-align: center; 
					font-weight: bold; 
					font-size: 1.5em;
				}
				
				.video_list {
					width: auto;
					height: 100px;
					overflow-x: hidden;
					overflow-y: scroll;
					border: 2px solid grey;
					padding: 5px;
					margin: 2px 0;
				}
				
				.video_list li {
					padding: 1px 0;
					margin: 0 0 5px 0;
				}
				
				.video_list li:hover {
					cursor: pointer;
				}
				
				.video_list li, .video_list li * {
					vertical-align: middle;
				}
				
				.unselect_all {
					display: none;
				}
				
				.left_label {
					display: inline-block; 
					float: left;
				}
				
				.select_all_container2 {
					display: inline-block; 
					float: right; 
					font-size: 0.9em;
				}
				
				.small_grey2 {
					color: grey;
					font-size: 0.9em;
				}
				
				.widget_left_label {
					display: inline-block; 
					width: 6em; 
					text-align: right;
				}
			</style>
			<label class="left_label"><?php _e('Video:', 'video-merchant'); ?></label>
			<span class="select_all_container2"><a class="unselect_all" href="javascript: void(0);" onclick="javascript: jQuery('input[name=\'<?php echo $this->get_field_name('video_ids'); ?>[]\']').prop('checked', false); jQuery(this).parent().find('.select_all').css('display', 'inline-block'); jQuery(this).css('display', 'none');"><?php _e('- Unselect All -', 'video-merchant'); ?></a><a class="select_all" href="javascript: void(0);" onclick="javascript: jQuery('#<?php echo $this->get_field_id('player_id'); ?>').val(''); jQuery('input[name=\'<?php echo $this->get_field_name('video_ids'); ?>[]\']').prop('checked', true); jQuery(this).parent().find('.unselect_all').css('display', 'inline-block'); jQuery(this).css('display', 'none');"><?php _e('- Select All -', 'video-merchant'); ?></a></span>
			<div class="clear"></div>
			<ul class="video_list">
				<?php 
				if(!empty($video['data']))
				{
					foreach($video['data'] as $videoRecord)
					{
				?>
				<li>
					<input onclick="javascript: jQuery('#<?php echo $this->get_field_id('player_id'); ?>').val('');" onchange="javascript: jQuery('#<?php echo $this->get_field_id('player_id'); ?>').val('');" type="checkbox" id="video_ids_<?php echo $videoRecord[0]; ?>" name="<?php echo $this->get_field_name('video_ids'); ?>[]" value="<?php echo $videoRecord[0]; ?>"<?php if(in_array($videoRecord[0], $videoIds)) { ?> checked="checked"<?php } ?> /> <?php echo $videoRecord[1]; ?>
				</li>
				<?php
					}
				}
				?>
			</ul>
			<span class="small_grey2"><?php _e('^ Sortable List', 'video-merchant'); ?></span>
			<?php 
			if(!empty($video['data']))
			{
			?>
			<script>		
				jQuery(document).ready(function() {
					jQuery(document).on('widget-updated', function(e, widget) {
						location.reload();
					});
					
					jQuery('ul.video_list li input[type="checkbox"]:checked').each(function (a, b) {
						jQuery(b).parent().parent().prepend(jQuery(b).parent());
					});
					
					jQuery('ul.video_list').sortable();
					jQuery('ul.video_list').disableSelection();
				});
			</script>
			<?php
			}
			?>
			<br /><br />
			<label class="widget_left_label" for="<?php echo $this->get_field_id('auto_play'); ?>"><?php _e('Auto Play:', 'video-merchant'); ?></label> 
			<select id="<?php echo $this->get_field_id('auto_play'); ?>" name="<?php echo $this->get_field_name('auto_play'); ?>">
				<option value="1"<?php if((int)$autoPlay == 1) { ?> selected="selected"<?php } ?>><?php _e('Yes', 'video-merchant'); ?></option>
				<option value="0"<?php if((int)$autoPlay == 0) { ?> selected="selected"<?php } ?>><?php _e('No', 'video-merchant'); ?></option>
			</select>
			<br />
			<label class="widget_left_label" for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:', 'video-merchant'); ?></label> 
			<input size="2" type="text" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $height; ?>" />px
		</p>
<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['player_id'] = (isset($new_instance['player_id']) && !empty($new_instance['player_id']) ) ? (int)$new_instance['player_id'] : 0;
		$instance['video_ids'] = (isset($new_instance['video_ids']) && !empty($new_instance['video_ids'])) ? implode(',', $new_instance['video_ids']) : '';
		$instance['height'] = (!empty($new_instance['height']) ) ? (int)$new_instance['height'] : 400;
		$instance['auto_play'] = (!empty($new_instance['auto_play']) ) ? (int)$new_instance['auto_play'] : 0;
		
		if(!empty($instance['video_ids']))
		{
			if(!preg_match('@^[0-9,]+$@', $instance['video_ids']))
			{
				$instance['video_ids'] = '';
			}
		}
		
		return $instance;
	}
}

function video_merchant_register_widget()
{
	register_widget('Video_Merchant_Widget');
}

add_action('widgets_init', 'video_merchant_register_widget');

function video_merchant_shortcode_func($atts) 
{
    $a = shortcode_atts(array(
		'video_id' => '',
		'video_ids' => '',
        'playlist_id' => 0,
		'height' => 400,
		'auto_play' => 0
    ), $atts);
	
    return video_merchant_render_player(!empty($a['video_ids']) ? $a['video_ids'] : $a['video_id'], $a['playlist_id'], $a['height'], $a['auto_play']);
}

add_shortcode('video_merchant', 'video_merchant_shortcode_func');