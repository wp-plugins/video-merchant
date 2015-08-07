<?php
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

video_merchant_db_install();

$supportedImageTypes = array('jpg', 'jpeg', 'png', 'gif');
$supportedVideoExtensions = array('mp4', 'ogg', 'ogv', 'webm');

$uploadDir = wp_upload_dir();
$uploadUrl = $uploadDir['baseurl'].'/video/'.get_current_blog_id();
$uploadDir = $uploadDir['basedir'].DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.get_current_blog_id();

$tempLinkExpirationsDays = (int)video_merchant_get_setting('temp_download_link_expiration');
$currency = video_merchant_get_setting('currency');
$downloadRequiresRegistration = (int)video_merchant_get_setting('download_user_login_required');
$purchaseRequiresRegistration = (int)video_merchant_get_setting('purchase_user_login_required');

$videoFiles = video_merchant_get_video(false);

if(!get_option('css_frontend_default'))
{
	update_option('css_frontend_default', file_get_contents(plugin_dir_path( __FILE__ ).'video-merchant-html-player-frontend.css'));
}
?>
<div class="wrap">
	<h2><?php echo __('Video Merchant', 'video-merchant'); ?></h2>
	<div id="video_merchant_main_tabs">
		<ul>
			<li><a href="#my_video_tab"><?php echo __('Video', 'video-merchant'); ?></a></li>
			<li><a href="#html_player_tab"><?php echo __('Playlists', 'video-merchant'); ?></a></li>
			<li><a href="#orders_tab"><?php echo __('Orders', 'video-merchant'); ?></a></li>
			<li><a href="#settings_tab"><?php echo __('Settings', 'video-merchant'); ?></a></li>
		</ul>
		<div id="my_video_tab">
			<button name="add_video_btn" id="add_video_btn"><?php echo __('Add Video', 'video-merchant'); ?></button>
			<br /><br />
			<table id="video_inventory_table" class="compact hover cell-border stripe" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th><?php echo __('Video ID', 'video-merchant'); ?></th>
						<th><?php echo __('Video Display Name', 'video-merchant'); ?></th>
						<th><?php echo __('Video Full Quality Download Price', 'video-merchant'); ?></th>
						<th><?php echo __('Exclusive Price', 'video-merchant'); ?></th>
						<th><?php echo __('Video Thumbnail Image', 'video-merchant'); ?></th>
						<th><?php echo __('Video File', 'video-merchant'); ?></th>
						<th><?php echo __('Video Preview File', 'video-merchant'); ?></th>
						<th><?php echo __('File', 'video-merchant'); ?></th>
						<th><?php echo __('Exclusive File', 'video-merchant'); ?></th>
						<th><?php echo __('Video Duration', 'video-merchant'); ?></th>
						<th><?php echo __('Date Created', 'video-merchant'); ?></th>
						<th><?php echo __('Last Modified', 'video-merchant'); ?></th>
						<th><?php echo __('Options', 'video-merchant'); ?></th>
					</tr>
				</thead>
			</table>
			<div id="new_video_wnd" title="<?php echo __('Add Video', 'video-merchant'); ?>">
				<form id="new_video_file_form" method="post" enctype="multipart/form-data" onsubmit="javascript: return addNewVideoFile();">
					<fieldset>
						<label for="video_display_name"><?php echo __('Video Display Name:', 'video-merchant'); ?></label>
						<input type="text" name="video_display_name" id="video_display_name" value="" maxlength="255" placeholder="<?php echo __('Example Format: My Great Video', 'video-merchant'); ?>" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the name displayed to the guest in the frontend player. You can add things like album name, BPM, etc. This field is completely flexible.', 'video-merchant'); ?>" />
						<br />
						<br />

						<label for="video_lease_price"><?php echo __('Video Full Quality Download Price:', 'video-merchant'); ?></label>
						<input type="text" name="video_lease_price" id="video_lease_price" value="" maxlength="13" placeholder="<?php echo __('Example Format: 20.00', 'video-merchant'); ?>" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The price to download the full quality video file. If price is empty or set to 0 then this file will be available for free download.', 'video-merchant'); ?>" />
						<br /><br />
						<label for="video_exclusive_price"><?php echo __('Exclusive Price:', 'video-merchant'); ?></label>
						<input type="hidden" name="video_exclusive_price" id="video_exclusive_price" value="" maxlength="13" placeholder="<?php echo __('Example Format: 200.00', 'video-merchant'); ?>" class="text ui-widget-content ui-corner-all" /> <img class="video_exclusive_price_tip" src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The price to purchase an exclusive license and download the full quality video file. When files are purchased exclusively, they are removed from the frontend player after a successfull checkout. Set to 0 to disable this option. If exclusive price AND lease price are both set to 0 then this file will be available for free download.', 'video-merchant'); ?>" />

						<br class="video_exclusive_price_tip" />
						<br class="video_exclusive_price_tip" />
						
						<div class="upload_file_wrapper hidden">
							<div id="video_upload_file_wrapper">
								<label for="video_upload_file"><?php echo __('Full Quality Video File:', 'video-merchant'); ?></label>
								<input type="file" name="video_upload_file" id="video_upload_file" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the full quality video file that is delivered to the buyer at the end of a successful purchase. This file should NOT contain any video watermarks. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div id="video_url_file_wrapper" class="hidden">
								<label for="video_url_file"><?php echo __('Full Quality Video File:', 'video-merchant'); ?></label>
								<input type="text" name="video_url_file" id="video_url_file" value="" maxlength="250" placeholder="http://externalserver.com/my_full_quality_video_file.mp4" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the full quality video file that is delivered to the buyer at the end of a successful purchase. This file should NOT contain any video watermarks. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div id="video_existing_file_wrapper" class="hidden">
								<?php echo __('Full Quality Video File:', 'video-merchant'); ?>
								<span class="existing_file_scroller">
									<?php
									$existingVideoFiles = array();

									if(file_exists($uploadDir))
									{
										if($supportedVideoExtensions)
										{
											foreach($supportedVideoExtensions as $supportedVideoExtension)
											{
												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.$supportedVideoExtension) as $file) 
												{
													$existingVideoFiles[] = $file;
												}

												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.strtoupper($supportedVideoExtension)) as $file) 
												{
													$existingVideoFiles[] = $file;
												}
											}

											$existingVideoFiles = array_unique($existingVideoFiles);
										}
									}

									if(!empty($existingVideoFiles))
									{
										foreach($existingVideoFiles as $key => $existingVideoFile)
										{
											$filename = basename($existingVideoFile);

											echo '<span><input type="radio" name="video_existing_file" id="video_existing_file_'.$key.'" value="'.$filename.'" /><label for="video_existing_file_'.$key.'"><img src="'.video_merchant_make_url_protocol_less(plugins_url('images/video_icon.png', __FILE__)).'" width="50" height="50" border="0" alt="" /><br /><a href="'.$uploadUrl.'/'.$filename.'" target="_blank" title="'.$filename.'">'.substr($filename, 0, 9).'...</a></label></span>';
										}
									}
									else 
									{
										echo '<span class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</span>';
									}
									?>
								</span> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the full quality video file that is delivered to the buyer at the end of a successful purchase. This file should NOT contain any video watermarks. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div class="small_url_container">
								<input type="hidden" class="upload_mode" name="video_mode" value="upload" />
								<a id="video_upload_link" class="hidden" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('upload', document.getElementById('video_upload_link'), 'video_upload_file_wrapper');"><?php echo __('Upload File', 'video-merchant'); ?></a> <a id="video_url_link" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('url', document.getElementById('video_url_link'), 'video_url_file_wrapper');"><?php echo __('Specify URL', 'video-merchant'); ?></a> <a id="video_existing_link" class="last_small_url" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('existing', document.getElementById('video_existing_link'), 'video_existing_file_wrapper');"><?php echo __('Select Existing File', 'video-merchant'); ?></a>
							</div>
							<div class="clear"></div>
						</div>
						<div class="upload_file_wrapper">
							<div id="preview_video_upload_file_wrapper">
								<label for="preview_video_upload_file"><?php echo __('Preview Video File:', 'video-merchant'); ?></label>
								<input type="file" name="preview_video_upload_file" id="preview_video_upload_file" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the video file that the guest listens to on the frontend player BEFORE they purchase the full quality video file. This file is used as a form of protection, and MAY contain video watermarks and/or other forms of protection. This file may even be shorter than the full quality video file, if you choose. Alternatively, you can play the full quality video file for your guests as the preview file, which we HIGHLY discourage, by re-uploading your full quality video file again in this field, however we HIGHLY recommend differentiating your preview file audo file and your full quality video file for security reasons. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div id="preview_video_url_file_wrapper" class="hidden">
								<label for="preview_video_url_file"><?php echo __('Preview Video File:', 'video-merchant'); ?></label>
								<input type="text" name="preview_video_url_file" id="preview_video_url_file" value="" maxlength="250" placeholder="http://externalserver.com/my_video_preview_file.mp4" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the video file that the guest listens to on the frontend player BEFORE they purchase the full quality video file. This file is used as a form of protection, and MAY contain video watermarks and/or other forms of protection. This file may even be shorter than the full quality video file, if you choose. Alternatively, you can play the full quality video file for your guests as the preview file, which we HIGHLY discourage, by re-uploading your full quality video file again in this field, however we HIGHLY recommend differentiating your preview file audo file and your full quality video file for security reasons. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div id="preview_video_existing_file_wrapper" class="hidden">
								<?php echo __('Preview Video File:', 'video-merchant'); ?>
								<span class="existing_file_scroller">
									<?php
									$existingVideoFiles = array();

									if(file_exists($uploadDir))
									{
										if($supportedVideoExtensions)
										{
											foreach($supportedVideoExtensions as $supportedVideoExtension)
											{
												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.$supportedVideoExtension) as $file) 
												{
													$existingVideoFiles[] = $file;
												}

												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.strtoupper($supportedVideoExtension)) as $file) 
												{
													$existingVideoFiles[] = $file;
												}
											}

											$existingVideoFiles = array_unique($existingVideoFiles);
										}
									}

									if(!empty($existingVideoFiles))
									{
										foreach($existingVideoFiles as $key => $existingVideoFile)
										{
											$filename = basename($existingVideoFile);

											echo '<span><input type="radio" name="preview_video_existing_file" id="preview_video_existing_file_'.$key.'" value="'.$filename.'" /><label for="preview_video_existing_file_'.$key.'"><img src="'.video_merchant_make_url_protocol_less(plugins_url('images/video_icon.png', __FILE__)).'" width="50" height="50" border="0" alt="" /><br /><a href="'.$uploadUrl.'/'.$filename.'" target="_blank" title="'.$filename.'">'.substr($filename, 0, 9).'...</a></label></span>';
										}
									}
									else 
									{
										echo '<span class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</span>';
									}
									?>
								</span> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This is the video file that the guest listens to on the frontend player BEFORE they purchase the full quality video file. This file is used as a form of protection, and MAY contain video watermarks and/or other forms of protection. This file may even be shorter than the full quality video file, if you choose. Alternatively, you can play the full quality video file for your guests as the preview file, which we HIGHLY discourage, by re-uploading your full quality video file again in this field, however we HIGHLY recommend differentiating your preview file audo file and your full quality video file for security reasons. Acceptable formats are: .mp4, .ogg, .ogv, .webm', 'video-merchant'); ?>" />
							</div>
							<div class="small_url_container">
								<input type="hidden" class="upload_mode" name="preview_video_mode" value="upload" />
								<a id="preview_video_upload_link" class="hidden" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('upload', document.getElementById('preview_video_upload_link'), 'preview_video_upload_file_wrapper');"><?php echo __('Upload File', 'video-merchant'); ?></a> <a id="preview_video_url_link" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('url', document.getElementById('preview_video_url_link'), 'preview_video_url_file_wrapper');"><?php echo __('Specify URL', 'video-merchant'); ?></a> <a id="preview_video_existing_link" class="last_small_url" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('existing', document.getElementById('preview_video_existing_link'), 'preview_video_existing_file_wrapper');"><?php echo __('Select Existing File', 'video-merchant'); ?></a>
							</div>
							<div class="clear"></div>
						</div>
						<div class="upload_file_wrapper">
							<div id="additional_lease_file_wrapper">
								<label for="additional_lease_file"><?php echo __('File To Provide With Purchase Or Free Download:', 'video-merchant'); ?></label>
								<input type="file" name="additional_lease_file" id="additional_lease_file" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase a video. This is a great place to provide additional files (.pdf), or any other file that may relate to your video file. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div id="additional_lease_url_file_wrapper" class="hidden">
								<label for="additional_lease_url_file"><?php echo __('File To Provide With Purchase Or Free Download:', 'video-merchant'); ?></label>
								<input type="text" name="additional_lease_url_file" id="additional_lease_url_file" value="" maxlength="250" placeholder="http://externalserver.com/my_full_quality_file.zip" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase a video. This is a great place to provide additional files (.pdf), or any other file that may relate to your video file. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div id="additional_lease_existing_file_wrapper" class="hidden">
								<?php echo __('File To Provide With Purchase Or Free Download:', 'video-merchant'); ?>
								<span class="existing_file_scroller">
									<?php
									$existingVideoFiles = array();

									if(file_exists($uploadDir))
									{
										foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*') as $file) 
										{
											$existingVideoFiles[] = $file;
										}
									}

									if(!empty($existingVideoFiles))
									{
										$supportedVideoExtensionsList = implode('|', $supportedVideoExtensions);
										$supportedImageTypesList = implode('|', $supportedImageTypes);

										foreach($existingVideoFiles as $key => $existingVideoFile)
										{
											$filename = basename($existingVideoFile);
											
											if('index.html' == $filename)
											{
												continue;
											}
											
											if(preg_match('@\.('.$supportedVideoExtensionsList.')$@i', $filename))
											{
												$iconSrc = video_merchant_make_url_protocol_less(plugins_url('images/video_icon.png', __FILE__));
											}
											elseif(preg_match('@\.('.$supportedImageTypesList.')$@i', $filename))
											{
												$iconSrc = video_merchant_make_url_protocol_less($uploadUrl.'/'.$filename);
											}
											else
											{
												$iconSrc = video_merchant_make_url_protocol_less(plugins_url('images/file_icon.png', __FILE__));
											}

											echo '<span><input type="radio" name="additional_lease_existing_file" id="additional_lease_existing_file_'.$key.'" value="'.$filename.'" /><label for="additional_lease_existing_file_'.$key.'"><img src="'.video_merchant_make_url_protocol_less($iconSrc).'" width="50" height="50" border="0" alt="" /><br /><a href="'.$uploadUrl.'/'.$filename.'" target="_blank" title="'.$filename.'">'.substr($filename, 0, 9).'...</a></label></span>';
										}
									}
									else 
									{
										echo '<span class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</span>';
									}
									?>
								</span> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase a video. This is a great place to provide additional files (.pdf), or any other file that may relate to your video file. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div class="small_url_container">
								<input type="hidden" class="upload_mode" name="addtional_file_lease_mode" value="upload" />
								<a id="additional_lease_upload_link" class="hidden" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('upload', document.getElementById('additional_lease_upload_link'), 'additional_lease_file_wrapper');"><?php echo __('Upload File', 'video-merchant'); ?></a> <a id="additional_lease_url_link" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('url', document.getElementById('additional_lease_url_link'), 'additional_lease_url_file_wrapper');"><?php echo __('Specify URL', 'video-merchant'); ?></a> <a id="additional_lease_existing_link" class="last_small_url" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('existing', document.getElementById('additional_lease_existing_link'), 'additional_lease_existing_file_wrapper');"><?php echo __('Select Existing File', 'video-merchant'); ?></a>
							</div>
							<div class="clear"></div>
						</div>

						<div class="upload_file_wrapper video_exclusive_price_tip">
							<div id="additional_exclusive_file_wrapper">
								<label for="additional_exclusive_file"><?php echo __('File To Provide With Exclusive Purchase:', 'video-merchant'); ?></label>
								<input type="file" name="additional_exclusive_file" id="additional_exclusive_file" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase an exclusive license. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div id="additional_exclusive_url_file_wrapper" class="hidden">
								<label for="additional_exclusive_url_file"><?php echo __('File To Provide With Exclusive Purchase:', 'video-merchant'); ?></label>
								<input type="text" name="additional_exclusive_url_file" id="additional_exclusive_url_file" value="" maxlength="250" placeholder="http://externalserver.com/my_additional_exclusive_file.zip" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase an exclusive license. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div id="additional_exclusive_existing_file_wrapper" class="hidden">
								<?php echo __('File To Provide With Exclusive Purchase:', 'video-merchant'); ?>
								<span class="existing_file_scroller">
									<?php
									$existingVideoFiles = array();

									if(file_exists($uploadDir))
									{
										foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*') as $file) 
										{
											$existingVideoFiles[] = $file;
										}
									}

									if(!empty($existingVideoFiles))
									{
										$supportedVideoExtensionsList = implode('|', $supportedVideoExtensions);
										$supportedImageTypesList = implode('|', $supportedImageTypes);

										foreach($existingVideoFiles as $key => $existingVideoFile)
										{
											$filename = basename($existingVideoFile);
											
											if('index.html' == $filename)
											{
												continue;
											}
											
											if(preg_match('@\.('.$supportedVideoExtensionsList.')$@i', $filename))
											{
												$iconSrc = video_merchant_make_url_protocol_less(plugins_url('images/video_icon.png', __FILE__));
											}
											elseif(preg_match('@\.('.$supportedImageTypesList.')$@i', $filename))
											{
												$iconSrc = video_merchant_make_url_protocol_less($uploadUrl.'/'.$filename);
											}
											else
											{
												$iconSrc = video_merchant_make_url_protocol_less(plugins_url('images/file_icon.png', __FILE__));
											}

											echo '<span><input type="radio" name="additional_exclusive_existing_file" id="additional_exclusive_existing_file_'.$key.'" value="'.$filename.'" /><label for="additional_exclusive_existing_file_'.$key.'"><img src="'.video_merchant_make_url_protocol_less($iconSrc).'" width="50" height="50" border="0" alt="" /><br /><a href="'.$uploadUrl.'/'.$filename.'" target="_blank" title="'.$filename.'">'.substr($filename, 0, 9).'...</a></label></span>';
										}
									}
									else 
									{
										echo '<span class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</span>';
									}
									?>
								</span> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This file is provided to buyers AFTER they purchase an exclusive license. Accepts any file format. If you wish to provide multiple files, please archive (.zip, .rar, etc.) them up into one file first, then upload that file here.', 'video-merchant'); ?>" />
							</div>
							<div class="small_url_container">
								<input type="hidden" class="upload_mode" name="addtional_file_exclusive_mode" value="upload" />
								<a id="additional_exclusive_upload_link" class="hidden" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('upload', document.getElementById('additional_exclusive_upload_link'), 'additional_exclusive_file_wrapper');"><?php echo __('Upload File', 'video-merchant'); ?></a> <a id="additional_exclusive_url_link" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('url', document.getElementById('additional_exclusive_url_link'), 'additional_exclusive_url_file_wrapper');"><?php echo __('Specify URL', 'video-merchant'); ?></a> <a id="additional_exclusive_existing_link" class="last_small_url" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('existing', document.getElementById('additional_exclusive_existing_link'), 'additional_exclusive_existing_file_wrapper');"><?php echo __('Select Existing File', 'video-merchant'); ?></a>
							</div>
							<div class="clear"></div>
						</div>
						
						<div class="upload_file_wrapper">
							<div id="cover_photo_upload_file_wrapper">
								<label for="cover_photo_upload_file"><?php echo __('Video Thumbnail Image:', 'video-merchant'); ?></label>
								<input type="file" name="cover_photo_upload_file" id="cover_photo_upload_file" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The image that represents this video file. This is the image displayed on the frontend player when this song is being played. The image width and height should be proportional. We recommend a size of 300 x 300 pixels, however this field is flexible and will accept any image dimensions and will resize them automatically. Acceptable formats are: .png, .jpg, .gif. Auto-generated from full quality video if left empty.', 'video-merchant'); ?>" />
							</div>
							<div id="cover_photo_url_file_wrapper" class="hidden">
								<label for="cover_photo_url_file"><?php echo __('Video Thumbnail Image:', 'video-merchant'); ?></label>
								<input type="text" name="cover_photo_url_file" id="cover_photo_url_file" value="" maxlength="250" placeholder="http://externalserver.com/my_video_thumbnail_image.png" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The image that represents this video file. This is the image displayed on the frontend player when this song is being played. The image width and height should be proportional. We recommend a size of 300 x 300 pixels, however this field is flexible and will accept any image dimensions and will resize them automatically. Acceptable formats are: .png, .jpg, .gif. Auto-generated from full quality video if left empty.', 'video-merchant'); ?>" />
							</div>
							<div id="cover_photo_existing_file_wrapper" class="hidden">
								<?php echo __('Video Thumbnail Image:', 'video-merchant'); ?>
								<span class="existing_file_scroller">
									<?php
									$existingFiles = array();

									if(file_exists($uploadDir))
									{
										if($supportedImageTypes)
										{
											foreach($supportedImageTypes as $supportedExtension)
											{
												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.$supportedExtension) as $file) 
												{
													$existingFiles[] = $file;
												}

												foreach(glob($uploadDir.DIRECTORY_SEPARATOR.'*.'.strtoupper($supportedExtension)) as $file) 
												{
													$existingFiles[] = $file;
												}
											}

											$existingFiles = array_unique($existingFiles);
										}
									}

									if(!empty($existingFiles))
									{
										foreach($existingFiles as $key => $existingFile)
										{
											$filename = basename($existingFile);

											echo '<span><input type="radio" name="cover_photo_existing_file" id="cover_photo_existing_file_'.$key.'" value="'.$filename.'" /><label for="cover_photo_existing_file_'.$key.'"><img src="'.video_merchant_make_url_protocol_less($uploadUrl).'/'.$filename.'" width="50" height="50" border="0" alt="" /><br /><a href="'.$uploadUrl.'/'.$filename.'" target="_blank" title="'.$filename.'">'.substr($filename, 0, 9).'...</a></label></span>';
										}
									}
									else 
									{
										echo '<span class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</span>';
									}
									?>
								</span> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The image that represents this video file. This is the image displayed on the frontend player when this song is being played. The image width and height should be proportional. We recommend a size of 300 x 300 pixels, however this field is flexible and will accept any image dimensions and will resize them automatically. Acceptable formats are: .png, .jpg, .gif. Auto-generated from full quality video if left empty.', 'video-merchant'); ?>" />
							</div>
							<div class="small_url_container">
								<input type="hidden" class="upload_mode" name="cover_photo_mode" value="upload" />
								<a id="cover_photo_upload_link" class="hidden" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('upload', document.getElementById('cover_photo_upload_link'), 'cover_photo_upload_file_wrapper');"><?php echo __('Upload File', 'video-merchant'); ?></a> <a id="cover_photo_url_link" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('url', document.getElementById('cover_photo_url_link'), 'cover_photo_url_file_wrapper');"><?php echo __('Specify URL', 'video-merchant'); ?></a> <a id="cover_photo_existing_link" class="last_small_url" href="javascript: void(0);" onclick="javascript: return toggle_upload_field('existing', document.getElementById('cover_photo_existing_link'), 'cover_photo_existing_file_wrapper');"><?php echo __('Select Existing File', 'video-merchant'); ?></a>
							</div>
							<div class="clear"></div>
						</div>
						
						<input type="hidden" name="editing_video_id" id="editing_video_id" value="" />
						<!-- Allow form submission with keyboard without duplicating the dialog button -->
						<input type="submit" tabindex="-1" class="default_submit_button" />
					</fieldset>
				</form>
			</div>
		</div>
		<div id="html_player_tab">
			<button name="create_html_player_widget_btn" id="create_html_player_widget_btn"><?php echo __('Add Playlist', 'video-merchant'); ?></button>
			<br /><br />
			<table id="html_player_table" class="compact hover cell-border stripe" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th><?php echo __('Playlist ID', 'video-merchant'); ?></th>
						<th><?php echo __('Playlist Name', 'video-merchant'); ?></th>
						<th><?php echo __('Mode', 'video-merchant'); ?></th>
						<th><?php echo __('Filter', 'video-merchant'); ?></th>
						<th><?php echo __('Order By Field', 'video-merchant'); ?></th>
						<th><?php echo __('Order By Direction', 'video-merchant'); ?></th>
						<th><?php echo __('Date Created', 'video-merchant'); ?></th>
						<th><?php echo __('Last Modified', 'video-merchant'); ?></th>
						<th><?php echo __('Options', 'video-merchant'); ?></th>
					</tr>
				</thead>
			</table>
			<div id="html_widget_dlg" title="<?php echo __('Add Playlist', 'video-merchant'); ?>">
				<form id="html_widget_form" method="post" onsubmit="javascript: return videoMerchantSaveHTMLPlayer();">
					<fieldset>
						<label for="playlist_name"><?php echo __('Playlist Name:', 'video-merchant'); ?></label>
						<input type="text" name="playlist_name" id="playlist_name" value="" maxlength="255" placeholder="<?php echo __('Example Format: My Slow Jams', 'video-merchant'); ?>" class="text ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('A descriptive name for you to remember what this playlist is about. This name is used for administrative purposes only, it is never displayed to the frontend user/listener.', 'video-merchant'); ?>" />
						<br />
						<br />
						
						<input onchange="javascript: toggleCreateHTMLPlayerMode();" type="radio" name="player_mode" id="player_mode_all" value="all" class="text2 ui-corner-all" checked="checked" /><label for="player_mode_all"><?php echo __('Display All Video', 'video-merchant'); ?></label> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This option will display all the video files available in the system for your listener.', 'video-merchant'); ?>" />
						<br /><br />
						<input onchange="javascript: toggleCreateHTMLPlayerMode();" type="radio" name="player_mode" id="player_mode_selected" value="selected" class="text2 ui-corner-all" /><label for="player_mode_selected"><?php echo __('Display Selected Video', 'video-merchant'); ?></label> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This option will display the below selected tracks in the frontend player for your listener. This list is sortable.', 'video-merchant'); ?>" />
						<span class="select_all_container"><a class="unselect_all" href="javascript: void(0);" onclick="javascript: jQuery('input[name=\'player_selected_video_ids[]\']').prop('checked', false); jQuery(this).parent().find('.select_all').css('display', 'inline-block'); jQuery(this).css('display', 'none');"><?php _e('- Unselect All -', 'video-merchant'); ?></a><a class="select_all" href="javascript: void(0);" onclick="javascript: jQuery('#player_mode_selected').prop('checked', true); toggleCreateHTMLPlayerMode(); jQuery('input[name=\'player_selected_video_ids[]\']').prop('checked', true); jQuery(this).parent().find('.unselect_all').css('display', 'inline-block'); jQuery(this).css('display', 'none');"><?php _e('- Select All -', 'video-merchant'); ?></a></span>
						<ol class="vertical_video_scroller">
							<?php
							if(!empty($videoFiles['data']))
							{
								foreach($videoFiles['data'] as $arrKey => $videoFile)
								{
									if(preg_match('@^https?://@i', $videoFile[4]))
									{
										$coverPhotoUrl = $videoFile[4];
									}
									else
									{
										$coverPhotoUrl = $uploadUrl.'/'.$videoFile[4];
									}
									
									if(preg_match('@^https?://@i', $videoFile[5]))
									{
										$fileUrl = $videoFile[5];
									}
									else
									{
										$fileUrl = $uploadUrl.'/'.$videoFile[5];
									}
									
									if(!empty($videoFile[4]))
									{
										$coverPhoto = '<img src="'.video_merchant_make_url_protocol_less($coverPhotoUrl).'" width="50" height="50" border="0" alt="" />';
									}
									else
									{
										$coverPhoto = '';
									}
									
									echo '<li><input type="checkbox" name="player_selected_video_ids[]" id="player_selected_video_ids_'.$videoFile[0].'" value="'.$videoFile[0].'" disabled="disabled" /> <label for="player_selected_video_ids_'.$videoFile[0].'">'.$coverPhoto.' <a href="'.$fileUrl.'" target="_blank">'.$videoFile[1].'</a></label></li>';
								}
							}
							else 
							{
								echo '<li class="warning_msg">'.__('There are currently no files uploaded!', 'video-merchant').'</li>';
							}
							?>
						</ol>
						<span class="small_grey"><?php _e('^ Sortable List', 'video-merchant'); ?></span>
						<br /><br />
						<input type="radio" onchange="javascript: toggleCreateHTMLPlayerMode();" name="player_mode" id="player_mode_text_match" value="text_match" class="text2 ui-corner-all" /><label for="player_mode_text_match"><?php echo __('Display Video Matching Text', 'video-merchant'); ?></label> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('This option will display all the tracks that match the text defined below.', 'video-merchant'); ?>" />
						<br />
						<blockquote>
							<label for="player_mode_text_value"><?php echo __('Match String:', 'video-merchant'); ?></label> <input type="text" name="player_mode_text_value" id="player_mode_text_value" value="" size="25" maxlength="255" placeholder="<?php echo __('Video Name', 'video-merchant'); ?>" class="text2 ui-widget-content ui-corner-all" disabled="disabled" />
						</blockquote>
						<br />
						<label for="player_display_order"><?php echo __('Display Order:', 'video-merchant'); ?></label> <select name="player_display_order" id="player_display_order" class="text2 ui-corner-all">
						<option value="1"><?php echo __('By Display Name', 'video-merchant'); ?></option>
						<option value="2"><?php echo __('By Video Full Quality Download Price', 'video-merchant'); ?></option>
						<option value="4"><?php echo __('By Duration', 'video-merchant'); ?></option>
						<option value="5"><?php echo __('By Date Created', 'video-merchant'); ?></option>
						<option value="6"><?php echo __('By Date Modified', 'video-merchant'); ?></option>
						</select><select name="player_display_order_direction" id="player_display_order_direction" class="text2 ui-corner-all">
						<option value="ASC"><?php echo __('Ascending', 'video-merchant'); ?></option>
						<option value="DESC"><?php echo __('Descending', 'video-merchant'); ?></option>
						</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The order in which the playlist tracks will display in.', 'video-merchant'); ?>" />
						
						<input type="hidden" id="player_id" name="player_id" value="" />
						<input type="submit" tabindex="-1" class="default_submit_button" />
					</fieldset>
				</form>
			</div>
		</div>
		<div id="orders_tab">
			<table id="orders_table" class="compact hover cell-border stripe" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th><?php echo __('Order ID', 'video-merchant'); ?></th>
						<th><?php echo __('User ID', 'video-merchant'); ?></th>
						<th><?php echo __('Payment Transaction ID', 'video-merchant'); ?></th>
						<th><?php echo __('Customer Name', 'video-merchant'); ?></th>
						<th><?php echo __('Customer Email', 'video-merchant'); ?></th>
						<th><?php echo __('Payment Status', 'video-merchant'); ?></th>
						<th><?php echo __('Grand Total', 'video-merchant'); ?></th>
						<th><?php echo __('Video ID', 'video-merchant'); ?></th>
						<th><?php echo __('Order Item', 'video-merchant'); ?></th>
						<th><?php echo __('License Type', 'video-merchant'); ?></th>
						<th><?php echo __('Date Created', 'video-merchant'); ?></th>
						<th><?php echo __('Last Modified', 'video-merchant'); ?></th>
						<th><?php echo __('Options', 'video-merchant'); ?></th>
					</tr>
				</thead>
			</table>
		</div>
		<div id="settings_tab">
			<p>
				<form id="settings_form" method="post" onsubmit="javascript: return videoMerchantSaveSettings();">
					<label for="paypal_email"><?php echo __('Paypal Email:', 'video-merchant'); ?></label> <?php echo __('Selling feature is only available in Pro version of this plugin. <a href="http://www.myvideomerchant.com/#download" target="_blank">Click here</a> to upgrade &gt;&gt;', 'video-merchant'); ?> <input type="hidden" name="paypal_email" id="paypal_email" value="" placeholder="you@youremail.com" size="20" class="text2 ui-widget-content ui-corner-all" /> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The PayPal email address that should receive payments. This should be the same as your primary email address in your PayPal account. If no PayPal email address is supplied, then selling functionality is disabled in the frontend HTML5 video player, and you will only be showcasing your video files at that point.', 'video-merchant'); ?>" />
					<br />
					<label for="video_merchant_currency"><?php echo __('Currency:', 'video-merchant'); ?></label> <select name="video_merchant_currency" id="video_merchant_currency" class="text2 ui-corner-all">
						<option value="AUD"<?php if('AUD' == $currency) { ?> selected="yes"<?php } ?>>Australian Dollar</option>
						<option value="BRL"<?php if('BRL' == $currency) { ?> selected="yes"<?php } ?>>Brazilian Real </option>
						<option value="CAD"<?php if('CAD' == $currency) { ?> selected="yes"<?php } ?>>Canadian Dollar</option>
						<option value="CZK"<?php if('CZK' == $currency) { ?> selected="yes"<?php } ?>>Czech Koruna</option>
						<option value="DKK"<?php if('DKK' == $currency) { ?> selected="yes"<?php } ?>>Danish Krone</option>
						<option value="EUR"<?php if('EUR' == $currency) { ?> selected="yes"<?php } ?>>Euro</option>
						<option value="HKD"<?php if('HKD' == $currency) { ?> selected="yes"<?php } ?>>Hong Kong Dollar</option>
						<option value="HUF"<?php if('HUF' == $currency) { ?> selected="yes"<?php } ?>>Hungarian Forint </option>
						<option value="ILS"<?php if('ILS' == $currency) { ?> selected="yes"<?php } ?>>Israeli New Sheqel</option>
						<option value="JPY"<?php if('JPY' == $currency) { ?> selected="yes"<?php } ?>>Japanese Yen</option>
						<option value="MYR"<?php if('MYR' == $currency) { ?> selected="yes"<?php } ?>>Malaysian Ringgit</option>
						<option value="MXN"<?php if('MXN' == $currency) { ?> selected="yes"<?php } ?>>Mexican Peso</option>
						<option value="NOK"<?php if('NOK' == $currency) { ?> selected="yes"<?php } ?>>Norwegian Krone</option>
						<option value="NZD"<?php if('NZD' == $currency) { ?> selected="yes"<?php } ?>>New Zealand Dollar</option>
						<option value="PHP"<?php if('PHP' == $currency) { ?> selected="yes"<?php } ?>>Philippine Peso</option>
						<option value="PLN"<?php if('PLN' == $currency) { ?> selected="yes"<?php } ?>>Polish Zloty</option>
						<option value="GBP"<?php if('GBP' == $currency) { ?> selected="yes"<?php } ?>>Pound Sterling</option>
						<option value="SGD"<?php if('SGD' == $currency) { ?> selected="yes"<?php } ?>>Singapore Dollar</option>
						<option value="SEK"<?php if('SEK' == $currency) { ?> selected="yes"<?php } ?>>Swedish Krona</option>
						<option value="CHF"<?php if('CHF' == $currency) { ?> selected="yes"<?php } ?>>Swiss Franc</option>
						<option value="TWD"<?php if('TWD' == $currency) { ?> selected="yes"<?php } ?>>Taiwan New Dollar</option>
						<option value="THB"<?php if('THB' == $currency) { ?> selected="yes"<?php } ?>>Thai Baht</option>
						<option value="TRY"<?php if('TRY' == $currency) { ?> selected="yes"<?php } ?>>Turkish Lira</option>
						<option value="USD"<?php if('USD' == $currency) { ?> selected="yes"<?php } ?>>U.S. Dollar</option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The currency that should be used for all sales.', 'video-merchant'); ?>" />
					
					
					<br />
					<label for="temp_download_link_expiration"><?php echo __('Download Links Expire:', 'video-merchant'); ?></label> <select name="temp_download_link_expiration" id="temp_download_link_expiration" class="text2 ui-corner-all">
						<?php for ($a = 1; $a <= 365; $a++) { ?>
						<option value="<?php echo $a; ?>"<?php if($tempLinkExpirationsDays == $a) { ?> selected="yes"<?php } ?>><?php 
						if($a <= 1)
						{
							echo $a.' '.__('Day', 'video-merchant'); 
						}
						else
						{
							echo $a.' '.__('Days', 'video-merchant');
						}
						?></option>
						<?php } ?>
						<option value="0"<?php if($tempLinkExpirationsDays == 0) { ?> selected="yes"<?php } ?>><?php echo __('Never', 'video-merchant'); ?></option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The number of days the download links for an order should remain valid until they expire and are no longer valid.', 'video-merchant'); ?>" />
					
					<br />
					<label for="email_admin_order_notices"><?php echo __('Email Admin Order Notifications:', 'video-merchant'); ?></label> <select name="email_admin_order_notices" id="email_admin_order_notices" class="text2 ui-corner-all">
						<option value="1"<?php if((int)video_merchant_get_setting('email_admin_order_notices') == 1) { ?> selected="yes"<?php } ?>><?php echo __('Yes', 'video-merchant'); ?></option>
						<option value="0"<?php if((int)video_merchant_get_setting('email_admin_order_notices') == 0) { ?> selected="yes"<?php } ?>><?php echo __('No', 'video-merchant'); ?></option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('Email the admin a copy of the receipt email sent to customer after a purchase is completed.', 'video-merchant'); ?>" />
					
					
					<br />
					<label for="purchase_user_login_required"><?php echo __('Require User Registration For Purchases:', 'video-merchant'); ?></label> <select name="purchase_user_login_required" id="purchase_user_login_required" class="text2 ui-corner-all">
						<option value="1"<?php if($purchaseRequiresRegistration == 1) { ?> selected="yes"<?php } ?>><?php echo __('Yes', 'video-merchant'); ?></option>
						<option value="0"<?php if($purchaseRequiresRegistration == 0) { ?> selected="yes"<?php } ?>><?php echo __('No', 'video-merchant'); ?></option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('Require website login or registration in order to purchase anything from the frontend player.', 'video-merchant'); ?>" />
					
					
					<br />
					<label for="download_user_login_required"><?php echo __('Require User Registration For Downloads:', 'video-merchant'); ?></label> <select name="download_user_login_required" id="download_user_login_required" class="text2 ui-corner-all">
						<option value="1"<?php if($downloadRequiresRegistration == 1) { ?> selected="yes"<?php } ?>><?php echo __('Yes', 'video-merchant'); ?></option>
						<option value="0"<?php if($downloadRequiresRegistration == 0) { ?> selected="yes"<?php } ?>><?php echo __('No', 'video-merchant'); ?></option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('Require website login or registration in order to download FREE items from the frontend player.', 'video-merchant'); ?>" />
					
					<div class="video_exclusive_price_tip">
						<br />
						<label for="exclusive_removed"><?php echo __('Exclusively Sold Items Are Removed From Player:', 'video-merchant'); ?></label> <select name="exclusive_removed" id="exclusive_removed" class="text2 ui-corner-all">
							<option value="1"<?php if((int)video_merchant_get_setting('exclusive_removed') == 1) { ?> selected="yes"<?php } ?>><?php echo __('Yes', 'video-merchant'); ?></option>
							<option value="0"<?php if((int)video_merchant_get_setting('exclusive_removed') == 0) { ?> selected="yes"<?php } ?>><?php echo __('No', 'video-merchant'); ?></option>
						</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('Remove exclusively purchased items from the frontend player or leave them and display a SOLD text next to them instead.', 'video-merchant'); ?>" />
					</div>
					<br />
					<label for="show_author_link"><?php echo __('Show author credits:', 'video-merchant'); ?></label> <select name="show_author_link" id="show_author_link" class="text2 ui-corner-all">
						<option value="1"<?php if((int)video_merchant_get_setting('show_author_link') == 1) { ?> selected="yes"<?php } ?>><?php echo __('Yes', 'video-merchant'); ?></option>
						<option value="0"<?php if((int)video_merchant_get_setting('show_author_link') == 0) { ?> selected="yes"<?php } ?>><?php echo __('No', 'video-merchant'); ?></option>
					</select> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('Give credit back to the plugin author. (Only in Lite version)', 'video-merchant'); ?>" />
					
					<br />
					<label id="css_frontend_label" for="css_frontend"><?php echo __('Frontend HTML5 Video Player CSS Styles:', 'video-merchant'); ?></label> <textarea id="css_frontend" name="css_frontend"><?php echo file_get_contents(plugin_dir_path( __FILE__ ).'video-merchant-html-player-frontend.css'); ?></textarea> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('CSS Styles for frontend HTML5 video player. Use this to customize the frontend look and feel.', 'video-merchant'); ?>" />
					<br />
					<label></label> <a class="use_default_link" href="javascript: void(0);" onclick="javascript: loadDefaultCSS();"><?php echo __('- Use Default CSS -', 'video-merchant'); ?></a>
					
					<br /><br />
					<label><?php echo __('php.ini upload_max_filesize:', 'video-merchant'); ?></label> <?php echo ini_get('upload_max_filesize'); ?> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The maximum file size of EACH FILE that can be uploaded to your webserver. This is the maximum file size PER individual file and not the total filesize of all files uploaded at any given time.', 'video-merchant'); ?>" />
					<br />
					<label><?php echo __('php.ini post_max_size:', 'video-merchant'); ?></label> <?php echo ini_get('post_max_size'); ?> <img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/question_mark.png', __FILE__)); ?>" width="15" height="15" align="absmiddle" border="0" alt="" title="<?php echo __('The maximum size of a single post which may include multiple file uploads. This is the total number of all uploads at a time. Please increase to a sufficient size that allows uploading of your files to your web server.', 'video-merchant'); ?>" />
					
					<br /><br />
					<label for="save_settings_btn">&nbsp;</label> <button type="submit" name="save_settings_btn" id="save_settings_btn"><?php echo __('Save', 'video-merchant'); ?></button>
				</form>
			</p>
		</div>
	</div>
	<br />
	<div style="text-align: center;"><?php echo __('You are currently using the free version of this plugin, selling functionality is not available in this version. Please <a href="http://www.myvideomerchant.com/#download" target="_blank">click here</a> to upgrade for a $25 one-time fee &gt;&gt;', 'video-merchant'); ?></div>
	<div id="error_msg_wrapper" title="<?php echo __('Error', 'video-merchant'); ?>"><p></p></div>
	<div id="success_msg_wrapper" title="<?php echo __('Success', 'video-merchant'); ?>"><p></p></div>
	<div id="success_msg_wrapper2" title="<?php echo __('Success', 'video-merchant'); ?>"><p></p></div>
	<div id="share_dialog" title="<?php echo __('Share', 'video-merchant'); ?>">
		<p>
			<textarea id="share_dialog_content"></textarea>
			<br />
			<a id="copy_share_code_to_clipboard" data-clipboard-text="" onclick="javascript: alert('<?php echo __('Copied to clipboard!', 'video-merchant'); ?>');" href="javascript: void(0);" class="float_right" title="<?php echo __('Copy to Clipboard', 'video-merchant'); ?>"><img src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/copy_icon.png', __FILE__)); ?>" width="20" alt="" border="0" /></a>
			<select id="share_dialog_mode" name="share_dialog_mode" onchange="javascript: updateShareDialog();">
				<option value="wp"><?php echo __('WordPress ShortCode', 'video-merchant'); ?></option>
				<option value="link" disabled="disabled"><?php echo __('Link (Only available in Pro version)', 'video-merchant'); ?></option>
				<option value="iframe" disabled="disabled"><?php echo __('IFrame (Only available in Pro version)', 'video-merchant'); ?></option>
			</select>
		</p>
	</div>
	<div class="hide2">
		<video id="dummy_video_player" width="300" muted>
			<?php echo __('Your browser does not support HTML5 video tag. Please download FireFox 3.5 or higher.', 'video-merchant'); ?>
		</video>
		<canvas id="dummy_canvas"></canvas>
	</div>
</div>
<script>
	uploadBaseUrl = '<?php echo video_merchant_make_url_protocol_less($uploadUrl); ?>';
	theFollowingErrorOccurredText = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('The following error(s) have occurred:', 'video-merchant'), ENT_QUOTES)); ?>';
	unknownErrorOccurredTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('An unknown error occurred, please try your request again.', 'video-merchant'), ENT_QUOTES)); ?>';
	urlImgBase = '<?php echo video_merchant_make_url_protocol_less(plugins_url('images/', __FILE__)); ?>';
	successWndRefreshMsg = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Success! Please click okay to refresh the window with your latest changes...', 'video-merchant'), ENT_QUOTES)); ?>';
	editItemTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Edit', 'video-merchant'), ENT_QUOTES)); ?>';
	deleteItemTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Delete', 'video-merchant'), ENT_QUOTES)); ?>';
	confirmDeleteMsg = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Are you sure you want to delete this item?', 'video-merchant'), ENT_QUOTES)); ?>';
	successDeleteMsg = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Successfully deleted item!', 'video-merchant'), ENT_QUOTES)); ?>';
	newVideoWndTitle = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Add Video', 'video-merchant'), ENT_QUOTES)); ?>';
	saveSuccessMsg = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Your latest changes were saved successfully!', 'video-merchant'), ENT_QUOTES)); ?>';
	displayAllTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Display All', 'video-merchant'), ENT_QUOTES)); ?>';
	selectedTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Selected', 'video-merchant'), ENT_QUOTES)); ?>';
	textMatchTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Text Match', 'video-merchant'), ENT_QUOTES)); ?>';
	ascendingTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Ascending', 'video-merchant'), ENT_QUOTES)); ?>';
	descendingTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Descending', 'video-merchant'), ENT_QUOTES)); ?>';
	filesSelected = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Files Selected', 'video-merchant'), ENT_QUOTES)); ?>';
	fileSelected = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('File Selected', 'video-merchant'), ENT_QUOTES)); ?>';
	createHTMLWidgetTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Add Playlist', 'video-merchant'), ENT_QUOTES)); ?>';
	viewTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('View', 'video-merchant'), ENT_QUOTES)); ?>';
	receiptUrl = '<?php echo admin_url('admin-ajax.php?action=video_merchant_checkout_complete&amp;t='); ?>';
	confirmChangeStatusTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Are you sure you want to change the status for this order?', 'video-merchant'), ENT_QUOTES)); ?>';
	shareItemtxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Share', 'video-merchant'), ENT_QUOTES)); ?>';
	loadingTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('Loading...', 'video-merchant'), ENT_QUOTES)); ?>';
	emptyVideoTxt = '<?php echo preg_replace('@[\r\n\t]+@', '', htmlentities(__('There are currently no video files to show with the specified criteria.', 'video-merchant'), ENT_QUOTES)); ?>';
	savingPleaseWaitxt = "<?php echo preg_replace('@[\r\n\t]+@', '', __('<img src=\''.video_merchant_make_url_protocol_less(plugins_url('images/ajax-loader.gif', __FILE__)).'\' style=\'vertical-align: middle;\' border=\'0\' alt=\'\' align=\'middle\' width=\'16\' height=\'16\' /> Saving, please wait...', 'video-merchant')); ?>";
</script>