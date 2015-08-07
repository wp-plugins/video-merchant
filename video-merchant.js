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

var newVideoWnd = null;
var htmlWidgetDlg = null;
var errorMessageWnd = null;
var successMessageWnd = null;
var successMessageWnd2 = null;
var uploadBaseUrl = null;
var theFollowingErrorOccurredText = 'The following error(s) have occurred:';
var unknownErrorOccurredTxt = 'An unknown error occurred, please try your request again.';
var urlImgBase = '';
var videoInventoryTable = null;
var htmlPlayerTable = null;
var successWndRefreshMsg = 'Success! Please click okay to refresh the window with your latest changes...';
var editItemTxt = 'Edit';
var deleteItemTxt = 'Delete';
var confirmDeleteMsg = 'Are you sure you want to delete this item?';
var successDeleteMsg = 'Successfully deleted item!';
var newVideoWndTitle = 'Add Video';
var saveSuccessMsg = 'Your latest changes were saved successfully!';
var displayAllTxt = 'Display All';
var selectedTxt = 'Selected';
var textMatchTxt = 'Text Match';
var ascendingTxt = 'Ascending';
var descendingTxt = 'Descending';
var filesSelected = 'Files Selected';
var fileSelected = 'File Selected';
var createHTMLWidgetTxt = 'Add Playlist';
var amTooltips = null;
var ordersTables = null;
var viewTxt = 'View';
var receiptUrl = '';
var confirmChangeStatusTxt = 'Are you sure you want to change the status for this order?';
var shareItemtxt = 'Share';
var loadingTxt = 'Loading...';
var shareDialog = null;
var lastId = 0;
var lastShareMode = 'video_id';
var emptyVideoTxt = 'There are currently no video files to show with the specified criteria.';
var savingPleaseWaitxt = 'Saving, please wait...';

function toggle_upload_field(mode, toggleLinkClicked, showWrapper) {
	var toggleLinkParent = jQuery(toggleLinkClicked).parent();
	
	toggleLinkParent.find('a').removeClass('hidden').removeClass('last_small_url');
	jQuery(toggleLinkClicked).addClass('hidden');
	toggleLinkParent.find('a:visible:last').addClass('last_small_url');
	
	var mainParent = toggleLinkParent.parent();
	
	mainParent.find('div').removeClass('hidden').addClass('hidden');
	jQuery('#'+showWrapper).removeClass('hidden');
	jQuery(toggleLinkParent).removeClass('hidden');
	
	mainParent.find('.upload_mode').val(mode);
}

function addNewVideoFile()
{
	var dialogSaveBtn = jQuery('.new_video_wnd .ui-button-text:contains(Save)');
	
	dialogSaveBtn.html(savingPleaseWaitxt);
	dialogSaveBtn.parent().button("disable");
	
	var actionUrl = ajaxurl.split('?')[0]+'?action=video_merchant_add_video_file';
	
	jQuery.ajax({
		url: actionUrl,
		type: 'POST',
		data: new FormData(jQuery('#new_video_file_form')[0]),
		processData: false,
		contentType: false
    }).done(function (response) {
		var isError = false;
		var errorMsg = '<span class="error_msg">'+theFollowingErrorOccurredText+'<br /><blockquote>';
		
		if(response) {
			if(response.errors.length > 0) {
				isError = true;
				response.errors.forEach(function (theErrorMsg) {
					errorMsg += theErrorMsg+'<br />';
				});
			}
		} else {
			isError = true;
			errorMsg += unknownErrorOccurredTxt;
		}
		
		errorMsg += '</blockquote></span>';
		
		if(isError) {
			jQuery("#error_msg_wrapper p").html(errorMsg);
			
			errorMessageWnd.dialog("open");
			
			dialogSaveBtn.text('Save');
			dialogSaveBtn.parent().button("enable");
		} else {
			if (response.full_quality_video_url && response.video_id) {
				jQuery('#dummy_video_player').attr('crossOrigin', 'anonymous');
				
				jQuery('#dummy_video_player').on('error', function (e) {
					jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);
					
					successMessageWnd.dialog("open");
				});
				
				jQuery('#dummy_video_player').on('loadedmetadata', function () {
					jQuery('#dummy_canvas').width(jQuery('#dummy_video_player').innerWidth());
					jQuery('#dummy_canvas').height(jQuery('#dummy_video_player').innerHeight());
					
					jQuery('#dummy_video_player')[0].currentTime = Math.round(jQuery('#dummy_video_player')[0].duration-(jQuery('#dummy_video_player')[0].duration/4));
				});
				
				var playThruFunc = function () {
					jQuery('#dummy_video_player').off('canplaythrough', playThruFunc);
					
					jQuery('#dummy_video_player')[0].play();
					
					setTimeout(function () {
						var takeShotFunc = function () {
							jQuery('#dummy_video_player').off("suspend pause", takeShotFunc);

							var ctx = jQuery('#dummy_canvas')[0].getContext('2d');

							ctx.drawImage(jQuery('#dummy_video_player')[0], 0, 0, jQuery('#dummy_video_player').innerWidth(), jQuery('#dummy_video_player').innerHeight());

							try {
								jQuery.post(ajaxurl.split('?')[0]+'?action=video_merchant_video_update_thumbnail_image', {'video_id': response.video_id, 'new_thumb_img': jQuery('#dummy_canvas')[0].toDataURL()}).always(function (jqXHR, textStatus, errorThrown) {
									jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);

									successMessageWnd.dialog("open");
								});
							} catch(err) {
								jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);

								successMessageWnd.dialog("open");
							}
						};
						
						jQuery('#dummy_video_player').on("suspend pause", takeShotFunc);
						
						jQuery('#dummy_video_player')[0].pause();
					}, 4000);
				};
				
				jQuery('#dummy_video_player').on('canplaythrough', playThruFunc);
				
				jQuery('#dummy_video_player').attr('src', response.full_quality_video_url);
				jQuery('#dummy_video_player')[0].load();
			} else {
				jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);
				
				successMessageWnd.dialog("open");
			}
		}
	}).fail(function(jqXHR, textStatus, errorThrown) {
		jQuery("#error_msg_wrapper p").html(errorThrown);
			
		errorMessageWnd.dialog("open");
		
		dialogSaveBtn.text('Save');
		dialogSaveBtn.parent().button("enable");
	});
	
	return false;
}

function videoMerchantSaveSettings()
{
	jQuery.ajax({
		url: ajaxurl.split('?')[0]+'?action=video_merchant_save_settings',
		type: 'POST',
		data: new FormData(jQuery('#settings_form')[0]),
		processData: false,
		contentType: false
    }).done(function (response) {
		var isError = false;
		var errorMsg = '<span class="error_msg">'+theFollowingErrorOccurredText+'<br /><blockquote>';
		
		if(response) {
			if(response.errors.length > 0) {
				isError = true;
				response.errors.forEach(function (theErrorMsg) {
					errorMsg += theErrorMsg+'<br />';
				});
			}
		} else {
			isError = true;
			errorMsg += unknownErrorOccurredTxt;
		}
		
		errorMsg += '</blockquote></span>';
		
		if(isError) {
			jQuery("#error_msg_wrapper p").html(errorMsg);
			
			errorMessageWnd.dialog("open");
		} else {
			jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);
			
			successMessageWnd.dialog("open");
		}
	}).fail(function(jqXHR, textStatus, errorThrown) {
		jQuery("#error_msg_wrapper p").html(errorThrown);
		
		errorMessageWnd.dialog("open");
	});
	
	return false;
}

function convertSecondsToMinutes(seconds) {
	seconds = Number(seconds).toFixed();
		
	var hours   = Math.floor(seconds / 3600);
	var minutes = Math.floor((seconds - (hours * 3600)) / 60);
	var seconds = seconds - (hours * 3600) - (minutes * 60);
	var time = "";

	if (hours != 0) {
	  time = String(hours)+":";
	}
	
	if (minutes != 0 || time !== "") {
	  minutes = (minutes < 10 && time !== "") ? "0"+String(minutes) : String(minutes);
	  time += String(minutes)+":";
	}
	
	if (time === "") {
	  time = '0:';

	  if (String(seconds).length < 2) {
		  time += '0';
	  }

	  time += String(seconds);
	} else {
	  time += (seconds < 10) ? "0"+String(seconds) : String(seconds);
	}

	return time;
}

jQuery(document).ready(function ($) {
	jQuery("#video_merchant_main_tabs").tabs();
	
	jQuery("#add_video_btn").button({
      icons: {
        primary: "ui-icon-circle-plus"
      }
    }).click(function (event) {
		event.preventDefault();
		
		openAddNewVideoFileWnd();
	});
	
	jQuery("#create_html_player_widget_btn").button({
      icons: {
        primary: "ui-icon-circle-plus"
      }
    }).click(function (event) {
		event.preventDefault();
		
		createHTMLWidget();
	});
	
	jQuery("#save_settings_btn").button({
      icons: {
        primary: "ui-icon-disk"
      }
    });
	
	newVideoWnd = jQuery("#new_video_wnd").dialog({
		autoOpen: false,
		width: 550,
		modal: false,
		dialogClass: 'new_video_wnd',
		buttons: {
			Save: function () {
				addNewVideoFile();
			}
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	htmlWidgetDlg = jQuery("#html_widget_dlg").dialog({
		autoOpen: false,
		width: 550,
		modal: false,
		dialogClass: 'html_widget_dlg',
		buttons: {
			Save: function () {
				videoMerchantSaveHTMLPlayer();
			}
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	shareDialog = jQuery("#share_dialog").dialog({
		modal: false,
		autoOpen: false,
		width: 'auto',
		buttons: {
		  Close: function() {
			jQuery(this).dialog("close");
		  }
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	errorMessageWnd = jQuery("#error_msg_wrapper").dialog({
		modal: false,
		autoOpen: false,
		buttons: {
		  Ok: function() {
			jQuery(this).dialog("close");
		  }
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	successMessageWnd = jQuery("#success_msg_wrapper").dialog({
		modal: false,
		autoOpen: false,
		buttons: {
		  Ok: function() {
			jQuery(this).dialog("close");
			jQuery(".ui-dialog-content").dialog("close");
			
			location.reload(true);
		  }
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	successMessageWnd2 = jQuery("#success_msg_wrapper2").dialog({
		modal: false,
		autoOpen: false,
		buttons: {
		  Ok: function() {
			jQuery(this).dialog("close");
		  }
		},
		close: function (event, ui) {
			jQuery('.ui-tooltip').hide();
		}
	});
	
	videoInventoryTable = jQuery('#video_inventory_table').DataTable({
		'lengthMenu': [[10, 20, 30, 40, 50, -1], [10, 20, 30, 40, 50, "All"]],
		'processing': true,
        'serverSide': true,
		'stateSave': true,
		"order": [[ 1, "ASC" ]],
        'ajax': ajaxurl.split('?')[0]+'?action=video_merchant_get_video',
		"language": {
			"emptyTable": emptyVideoTxt
		},
		
		"columnDefs": [
            {
                "render": function (data, type, row) {
					var result = '';
					
					if (/^https?:/i.test(data)) {
						result += '<a href="'+data+'" target="_blank">';
						
						if (String(data).length > 30) {
							result += String(data).substring(0, 30)+'...';
						} else {
							result += data;
						}
						
						result += '</a>';
					} else if(!data) {
						result += '';
					} else {
						result += '<a href="'+uploadBaseUrl+'/'+data+'" target="_blank">';
						
						if (String(data).length > 30) {
							result += String(data).substring(0, 30)+'...';
						} else {
							result += data;
						}
						
						result += '</a>';
					}
					
					return result;
                }, "targets": [5,7]
            },{
                "render": function (data, type, row) {
					var output = '';
					
					if (row[4]) {
						if (/^https?:/i.test(row[4])) {
							output += '<a href="'+row[4]+'" target="_blank"><img src="'+row[4].replace(/^https?:/i, '')+'" width="50" border="0" alt="" align="middle" /></a> ';
						} else {
							output += '<a href="'+uploadBaseUrl+'/'+row[4]+'" target="_blank"><img src="'+uploadBaseUrl+'/'+row[4]+'" width="50" border="0" alt="" align="middle" /></a> ';
						}
					}
					
					if (/^https?:/i.test(row[5])) {
						output += '<a href="'+row[5]+'" target="_blank">'+data+'</a>';
					} else {
						output += '<a href="'+uploadBaseUrl+'/'+row[5]+'" target="_blank">'+data+'</a>';
					}
					
					if (row[6]) {
						if (/^https?:/i.test(row[6])) {
							output += ' - <a href="'+row[6]+'" target="_blank" class="preview_link">Preview File</a>';
						} else {
							output += ' - <a href="'+uploadBaseUrl+'/'+row[6]+'" target="_blank" class="preview_link">Preview File</a>';
						}
					}
					
					return output;
                }, "targets": [1]
            },{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '';
					
					output += '<a href="javascript: void(0);" onclick="javascript: editVideo('+String(row[0])+');" title="'+editItemTxt+'"><img src="'+urlImgBase+'edit_icon.png" width="20" height="20" alt="" border="0" /></a>&nbsp;&nbsp;';
					output += '<a href="javascript: void(0);" onclick="javascript: shareVideoItem('+String(row[0])+');" title="'+shareItemtxt+'"><img src="'+urlImgBase+'share_icon.png" width="20" height="20" alt="" border="0" /></a>&nbsp;&nbsp;';
					output += '<a href="javascript: void(0);" onclick="javascript: deleteVideo('+String(row[0])+');" title="'+deleteItemTxt+'"><img src="'+urlImgBase+'trash_icon.png" width="20" height="20" alt="" border="0" /></a>';
					
					return output;
                }, "targets": [12], sortable: false
            },{
				"visible": false,  "targets": [3,4,5,6,8]
			},{
				className: 'dt-body-center', "targets": [0,2,10,11]
			},{
				className: 'dt-body-center', "visible": false, "targets": [9],
				"render": function (data, type, row) {
					return convertSecondsToMinutes(data);
                }
			}
        ]
	});
	
	htmlPlayerTable = jQuery('#html_player_table').DataTable({
		'lengthMenu': [[10, 20, 30, 40, 50, -1], [10, 20, 30, 40, 50, "All"]],
		'processing': true,
        'serverSide': true,
		'stateSave': true,
		"order": [[ 6, "DESC" ]],
        'ajax': ajaxurl.split('?')[0]+'?action=video_merchant_get_playlist',
		
		"columnDefs": [
            {
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '';
					
					output += '<a href="javascript: void(0);" onclick="javascript: editHTMLPlayer('+String(row[0])+');" title="'+editItemTxt+'"><img src="'+urlImgBase+'edit_icon.png" width="20" height="20" alt="" border="0" /></a>&nbsp;&nbsp;';
					output += '<a href="javascript: void(0);" onclick="javascript: sharePlaylistItem('+String(row[0])+');" title="'+shareItemtxt+'"><img src="'+urlImgBase+'share_icon.png" width="20" height="20" alt="" border="0" /></a>&nbsp;&nbsp;';
					output += '<a href="javascript: void(0);" onclick="javascript: deleteHTMLPlayer('+String(row[0])+');" title="'+deleteItemTxt+'"><img src="'+urlImgBase+'trash_icon.png" width="20" height="20" alt="" border="0" /></a>';
					
					return output;
                }, "targets": [8], sortable: false
            },{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					if (data == 'all') {
						return displayAllTxt;
					} else if(data == 'selected') {
						return selectedTxt;
					} else if(data == 'text_match') {
						return textMatchTxt;
					}
                }, "targets": [2]
            },{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					data = data.replace("video_", '');
					data = data.replace("_", ' ');
					
					return amUCWords(data);
                }, "targets": [4]
            },{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					if (data == 'ASC') {
						return ascendingTxt;
					} else if (data == 'DESC') {
						return descendingTxt;
					}
                }, "targets": [5]
            },{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					if (row[2] == 'selected') {
						var numSongsSelected = (data.match(/,/g) || []).length+1;
						
						if (numSongsSelected > 1) {
							return String(numSongsSelected)+' '+filesSelected;
						} else {
							return String(numSongsSelected)+' '+fileSelected;
						}
					} else {
						return data;
					}
                }, "targets": [3]
            },{
				className: 'dt-body-center', "targets": [0,6,7]
			}
        ]
	});
	
	ordersTables = jQuery('#orders_table').DataTable({
		'lengthMenu': [[10, 20, 30, 40, 50, -1], [10, 20, 30, 40, 50, "All"]],
		'processing': true,
        'serverSide': true,
		'stateSave': true,
		"order": [[ 10, "DESC" ]],
        'ajax': ajaxurl.split('?')[0]+'?action=video_merchant_get_order',
		
		"columnDefs": [
            {
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '';
					
					output += '<a href="'+receiptUrl+row[0]+'" target="_blank" title="'+viewTxt+'"><img src="'+urlImgBase+'details_icon.png" width="20" alt="" border="0" /></a>';
					
					return output;
                }, "targets": [12], sortable: false
            },
			{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '';
					
					if (row[1] > 0) {
						output += '(ID: '+row[1]+') ';
					}
					
					output += data;
					
					return output;
                }, "targets": [3]
            },
			{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '';
					
					if (row[7] > 0) {
						output += '(ID: '+row[7]+') ';
					}
					
					output += data;
					
					return output;
                }, "targets": [8]
            },
			{
				className: 'dt-body-center',
                "render": function (data, type, row) {
					var output = '<select onchange="javascript: changeOrderStatus(\''+row[0]+'\', this, \''+data+'\', this.value);">';
					
					if (data == 'Pending') {
						output += '<option value="Pending" selected="selected">Pending</option>';
					} else {
						output += '<option value="Pending">Pending</option>';
					}
					
					
					if (data == 'Refunded') {
						output += '<option value="Refunded" selected="selected">Refunded</option>';
					} else {
						output += '<option value="Refunded">Refunded</option>';
					}
					
					if (data == 'Reversed') {
						output += '<option value="Reversed" selected="selected">Reversed</option>';
					} else {
						output += '<option value="Reversed">Reversed</option>';
					}
					
					if (data == 'Completed') {
						output += '<option value="Completed" selected="selected">Completed</option>';
					} else {
						output += '<option value="Completed">Completed</option>';
					}
					
					output += '</select>';
					
					return output;
                }, "targets": [5]
            },
			{
				className: 'dt-body-center',
                "targets": [0,1,2,4,6,7,10,11]
            },
			{
				"visible": false,  "targets": [1,9,7]
			}
        ]
	});
	
	amTooltips = jQuery(document).tooltip({
		hide: {effect: 'fade', duration: 1}
	});
	
	jQuery('.vertical_video_scroller').sortable();
	jQuery('.vertical_video_scroller').disableSelection();
	
	var client = new ZeroClipboard(document.getElementById("copy_share_code_to_clipboard"));
	
	jQuery('#settings_form input[type="text"], #settings_form select, #settings_form textarea').on('change keyup paste', function() {
		updateAuthor();
	});
});

function sharePlaylistItem(playListId)
{
	lastId = playListId;
	lastShareMode = 'playlist_id';
	
	updateShareDialog();
	
	shareDialog.dialog("open");
}

function shareVideoItem(videoId)
{
	lastId = videoId;
	lastShareMode = 'video_id';
	
	updateShareDialog();
	
	shareDialog.dialog("open");
}

function updateShareDialog()
{
	jQuery('#share_dialog_content').val(loadingTxt);
	jQuery('#copy_share_code_to_clipboard').attr('data-clipboard-text', '');
	
	switch(jQuery('#share_dialog_mode').val())
	{
		case 'wp':
			var code = '';
			
			code = '[video_merchant '+lastShareMode+'="'+lastId+'" height="400" auto_play="1"]';
			
			jQuery('#share_dialog_content').val(code);
			jQuery('#copy_share_code_to_clipboard').attr('data-clipboard-text', code);
			
			break;
	}
}

function changeOrderStatus(orderId, selectObj, oldValue, newValue) {
	var answer = confirm(confirmChangeStatusTxt);
	
	if (answer) {
		jQuery.get(ajaxurl.split('?')[0]+'?action=video_merchant_change_order_status&t='+orderId+'&new_status='+newValue, function (data) {
			ordersTables.ajax.reload(null, false);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			jQuery("#error_msg_wrapper p").html(errorThrown);
			
			errorMessageWnd.dialog("open");
		});
	} else {
		jQuery(selectObj).val(oldValue);
	}
}

function amUCWords(str) {
	return (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
		return $1.toUpperCase();
	});
}

function openAddNewVideoFileWnd() {
	jQuery('#new_video_file_form input[type="radio"]').prop('checked', false);
	jQuery('#new_video_file_form input[type="text"]').val('');
	jQuery('#editing_video_id').val('');
	
	newVideoWnd.dialog('option', 'title', newVideoWndTitle);
	newVideoWnd.dialog("open");
	
	toggle_upload_field('upload', document.getElementById('cover_photo_upload_link'), 'cover_photo_upload_file_wrapper');
	toggle_upload_field('upload', document.getElementById('video_upload_link'), 'video_upload_file_wrapper');
	toggle_upload_field('upload', document.getElementById('preview_video_upload_link'), 'preview_video_upload_file_wrapper');
	toggle_upload_field('upload', document.getElementById('additional_lease_upload_link'), 'additional_lease_file_wrapper');
	toggle_upload_field('upload', document.getElementById('additional_exclusive_upload_link'), 'additional_exclusive_file_wrapper');
}

function editHTMLPlayer(playerId) {
	jQuery('#html_widget_form')[0].reset();
			
	toggleCreateHTMLPlayerMode();
	
	var record = htmlPlayerTable.rows(function (idx, data, node) {
		return parseInt(data[0]) == parseInt(playerId) ? true : false;
    }).data();
	
	htmlWidgetDlg.dialog('option', 'title', editItemTxt);
	htmlWidgetDlg.dialog("open");
	
	jQuery('#player_id').val(record[0][0]);
	
	jQuery('#playlist_name').val(jQuery('<textarea/>').html(record[0][1]).text());
	
	if (record[0][2] == 'all') {
		jQuery('#player_mode_all').prop('checked', true);
		
		toggleCreateHTMLPlayerMode();
	} else if (record[0][2] == 'selected') {
		jQuery('#player_mode_selected').prop('checked', true);
		
		toggleCreateHTMLPlayerMode();
		
		var checkedVideoIds = record[0][3].split(',').reverse();
		
		checkedVideoIds.forEach(function (entry) {
			jQuery('#player_selected_video_ids_'+String(entry)).prop('checked', true);
			
			jQuery('#player_selected_video_ids_'+String(entry)).parent().parent().prepend(jQuery('#player_selected_video_ids_'+String(entry)).parent());
		});
	} else if (record[0][2] == 'text_match') {
		jQuery('#player_mode_text_match').prop('checked', true);
		
		toggleCreateHTMLPlayerMode();
		
		jQuery('#player_mode_text_value').val(jQuery('<textarea/>').html(record[0][3]).text());
	}
	
	switch(record[0][4])
	{
		case 'video_display_name' :
			jQuery('#player_display_order').val('1');
			break;
		
		case 'video_lease_price':
			jQuery('#player_display_order').val('2');
			
			break;
		
		case 'video_exclusive_price':
			jQuery('#player_display_order').val('3');
			
			break;
		
		case 'video_duration':
			jQuery('#player_display_order').val('4');
			
			break;
		
		case 'video_cdate':
			jQuery('#player_display_order').val('5');
			
			break;
		
		case 'video_mdate':
			jQuery('#player_display_order').val('6');
			
			break;
	}
	
	jQuery('#player_display_order_direction').val(record[0][5]);
}

function editVideo(videoId) {
	jQuery('.existing_file_scroller').scrollLeft(0);
	
	jQuery('#new_video_file_form input[type="radio"]').prop('checked', false);
	jQuery('#new_video_file_form input[type="text"]').val('');
	jQuery('#editing_video_id').val(videoId);
	
	var videoRecord = videoInventoryTable.rows(function (idx, data, node) {
		return parseInt(data[0]) == parseInt(videoId) ? true : false;
    }).data();
	
	newVideoWnd.dialog('option', 'title', editItemTxt);
	newVideoWnd.dialog("open");
	
	if (videoRecord[0][1]) {
		jQuery('#video_display_name').val(jQuery('<textarea/>').html(videoRecord[0][1]).text());
	}
	
	if (videoRecord[0][2]) {
		jQuery('#video_lease_price').val(String(videoRecord[0][2]).replace(/[^0-9.]+/, ''));
	}
	
	if (videoRecord[0][3]) {
		jQuery('#video_exclusive_price').val(String(videoRecord[0][3]).replace(/[^0-9.]+/, ''));
	}
	
	if (videoRecord[0][4]) {
		if (/^https?:/i.test(videoRecord[0][4])) {
			toggle_upload_field('url', document.getElementById('cover_photo_url_link'), 'cover_photo_url_file_wrapper');
			
			jQuery('#cover_photo_url_file').val(videoRecord[0][4]);
		} else {
			toggle_upload_field('existing', document.getElementById('cover_photo_existing_link'), 'cover_photo_existing_file_wrapper');
			
			var existingFileadio = jQuery('#cover_photo_existing_file_wrapper input[type="radio"][value="'+videoRecord[0][4]+'"]');
			existingFileadio.prop('checked', true);
			existingFileadio.parent().insertBefore(jQuery('#cover_photo_existing_file_wrapper .existing_file_scroller span:first'));
			
			jQuery('#cover_photo_existing_file_wrapper .existing_file_scroller span:first')[0].scrollIntoView();
		}
	} else {
		toggle_upload_field('upload', document.getElementById('cover_photo_upload_link'), 'cover_photo_upload_file_wrapper');
	}
	
	if (videoRecord[0][5]) {
		if (/^https?:/i.test(videoRecord[0][5])) {
			toggle_upload_field('url', document.getElementById('video_url_link'), 'video_url_file_wrapper');
			
			jQuery('#video_url_file').val(videoRecord[0][5]);
		} else {
			toggle_upload_field('existing', document.getElementById('video_existing_link'), 'video_existing_file_wrapper');
			
			var existingFileadio = jQuery('#video_existing_file_wrapper input[type="radio"][value="'+videoRecord[0][5]+'"]');
			existingFileadio.prop('checked', true);
			existingFileadio.parent().insertBefore(jQuery('#video_existing_file_wrapper .existing_file_scroller span:first'));
			
			jQuery('#video_existing_file_wrapper .existing_file_scroller span:first')[0].scrollIntoView();
		}
	} else {
		toggle_upload_field('upload', document.getElementById('video_upload_link'), 'video_upload_file_wrapper');
	}
	
	if (videoRecord[0][6]) {
		if (/^https?:/i.test(videoRecord[0][6])) {
			toggle_upload_field('url', document.getElementById('preview_video_url_link'), 'preview_video_url_file_wrapper');
			
			jQuery('#preview_video_url_file').val(videoRecord[0][6]);
		} else {
			toggle_upload_field('existing', document.getElementById('preview_video_existing_link'), 'preview_video_existing_file_wrapper');
			
			var existingFileadio = jQuery('#preview_video_existing_file_wrapper input[type="radio"][value="'+videoRecord[0][6]+'"]');
			existingFileadio.prop('checked', true);
			existingFileadio.parent().insertBefore(jQuery('#preview_video_existing_file_wrapper .existing_file_scroller span:first'));
			
			jQuery('#preview_video_existing_file_wrapper .existing_file_scroller span:first')[0].scrollIntoView();
		}
	} else {
		toggle_upload_field('upload', document.getElementById('preview_video_upload_link'), 'preview_video_upload_file_wrapper');
	}
	
	if (videoRecord[0][7]) {
		if (/^https?:/i.test(videoRecord[0][7])) {
			toggle_upload_field('url', document.getElementById('additional_lease_url_link'), 'additional_lease_url_file_wrapper');
			
			jQuery('#additional_lease_url_file').val(videoRecord[0][7]);
		} else {
			toggle_upload_field('existing', document.getElementById('additional_lease_existing_link'), 'additional_lease_existing_file_wrapper');
			
			var existingFileadio = jQuery('#additional_lease_existing_file_wrapper input[type="radio"][value="'+videoRecord[0][7]+'"]');
			existingFileadio.prop('checked', true);
			existingFileadio.parent().insertBefore(jQuery('#additional_lease_existing_file_wrapper .existing_file_scroller span:first'));
			
			jQuery('#additional_lease_existing_file_wrapper .existing_file_scroller span:first')[0].scrollIntoView();
		}
	} else {
		toggle_upload_field('upload', document.getElementById('additional_lease_upload_link'), 'additional_lease_file_wrapper');
	}
	
	if (videoRecord[0][8]) {
		if (/^https?:/i.test(videoRecord[0][8])) {
			toggle_upload_field('url', document.getElementById('additional_exclusive_url_link'), 'additional_exclusive_url_file_wrapper');
			
			jQuery('#additional_exclusive_url_file').val(videoRecord[0][8]);
		} else {
			toggle_upload_field('existing', document.getElementById('additional_exclusive_existing_link'), 'additional_exclusive_existing_file_wrapper');
			
			var existingFileadio = jQuery('#additional_exclusive_existing_file_wrapper input[type="radio"][value="'+videoRecord[0][8]+'"]');
			existingFileadio.prop('checked', true);
			existingFileadio.parent().insertBefore(jQuery('#additional_exclusive_existing_file_wrapper .existing_file_scroller span:first'));
			
			jQuery('#additional_exclusive_existing_file_wrapper .existing_file_scroller span:first')[0].scrollIntoView();
		}
	} else {
		toggle_upload_field('upload', document.getElementById('additional_exclusive_upload_link'), 'additional_exclusive_file_wrapper');
	}
}

function deleteVideo(videoId)
{
	var answer = confirm(confirmDeleteMsg);
	
	if (answer) {
		jQuery.get(ajaxurl.split('?')[0]+'?action=video_merchant_delete_video_item&video_id='+String(videoId), function(data) {
			jQuery("#success_msg_wrapper p").html(successWndRefreshMsg);
			
			successMessageWnd.dialog("open");
		}).fail(function(jqXHR, textStatus, errorThrown) {
			jQuery("#error_msg_wrapper p").html(errorThrown);

			errorMessageWnd.dialog("open");
		});
	}
}

function deleteHTMLPlayer(playerId)
{
	var answer = confirm(confirmDeleteMsg);
	
	if (answer) {
		jQuery.get(ajaxurl.split('?')[0]+'?action=video_merchant_delete_playlist&player_id='+String(playerId), function (data) {
			htmlPlayerTable.ajax.reload(null, false);
			
			jQuery("#success_msg_wrapper2 p").html(successDeleteMsg);
			
			successMessageWnd2.dialog("open");
		}).fail(function(jqXHR, textStatus, errorThrown) {
			jQuery("#error_msg_wrapper p").html(errorThrown);

			errorMessageWnd.dialog("open");
		});
	}
}

function createHTMLWidget()
{
	jQuery('#html_widget_form')[0].reset();
			
	toggleCreateHTMLPlayerMode();
	
	jQuery('#player_id').val('');
	
	htmlWidgetDlg.dialog('option', 'title', createHTMLWidgetTxt);
	htmlWidgetDlg.dialog("open");
}

function toggleCreateHTMLPlayerMode()
{
	if (jQuery('#player_mode_all').is(':checked')) {
		jQuery('input[type="checkbox"][name="player_selected_video_ids[]"]').prop("disabled", true);
		jQuery('#player_mode_text_value').prop("disabled", true);
		jQuery('#player_display_order').prop("disabled", false);
		jQuery('#player_display_order_direction').prop("disabled", false);
	} else if (jQuery('#player_mode_selected').is(':checked')) {
		jQuery('input[type="checkbox"][name="player_selected_video_ids[]"]').prop("disabled", false);
		jQuery('#player_mode_text_value').prop("disabled", true);
		jQuery('#player_display_order').prop("disabled", true);
		jQuery('#player_display_order_direction').prop("disabled", true);
	} else if (jQuery('#player_mode_text_match').is(':checked')) {
		jQuery('input[type="checkbox"][name="player_selected_video_ids[]"]').prop("disabled", true);
		jQuery('#player_mode_text_value').prop("disabled", false);
		jQuery('#player_display_order').prop("disabled", false);
		jQuery('#player_display_order_direction').prop("disabled", false);
	}
}

function videoMerchantSaveHTMLPlayer()
{
	var dialogSaveBtn = jQuery('.html_widget_dlg .ui-button-text:contains(Save)');
	
	dialogSaveBtn.html(savingPleaseWaitxt);
	dialogSaveBtn.parent().button("disable");
	
	var actionUrl = ajaxurl.split('?')[0]+'?action=video_merchant_save_playlist';
	
	jQuery.ajax({
		url: actionUrl,
		type: 'POST',
		data: new FormData(jQuery('#html_widget_form')[0]),
		processData: false,
		contentType: false
    }).done(function (response) {
		var isError = false;
		var errorMsg = '<span class="error_msg">'+theFollowingErrorOccurredText+'<br /><blockquote>';
		
		if(response) {
			if(response.errors.length > 0) {
				isError = true;
				response.errors.forEach(function (theErrorMsg) {
					errorMsg += theErrorMsg+'<br />';
				});
			}
		} else {
			isError = true;
			errorMsg += unknownErrorOccurredTxt;
		}
		
		errorMsg += '</blockquote></span>';
		
		if(isError) {
			jQuery("#error_msg_wrapper p").html(errorMsg);
			
			errorMessageWnd.dialog("open");
			
			dialogSaveBtn.text('Save');
			dialogSaveBtn.parent().button("enable");
		} else {
			htmlPlayerTable.ajax.reload(null, false);
			
			jQuery("#success_msg_wrapper2 p").html(saveSuccessMsg);
			
			successMessageWnd2.dialog("open");
			
			htmlWidgetDlg.dialog("close");
			
			dialogSaveBtn.text('Save');
			dialogSaveBtn.parent().button("enable");
			
			jQuery('#html_widget_form')[0].reset();
			
			toggleCreateHTMLPlayerMode();
		}
	}).fail(function(jqXHR, textStatus, errorThrown) {
		jQuery("#error_msg_wrapper p").html(errorThrown);
			
		errorMessageWnd.dialog("open");
		
		dialogSaveBtn.text('Save');
		dialogSaveBtn.parent().button("enable");
	});
	
	return false;
}

function loadDefaultCSS()
{
	jQuery.get(ajaxurl.split('?')[0]+'?action=video_merchant_get_default_css', function (data) {
		jQuery('#css_frontend').val(data.result);
	}).fail(function(jqXHR, textStatus, errorThrown) {
		jQuery("#error_msg_wrapper p").html(errorThrown);
		
		errorMessageWnd.dialog("open");
	});
}

function updateAuthor()
{
	if(parseInt(jQuery('#show_author_link').val()) < 1) {
		if(confirm('Some settings can not be changed when "Show Author Credits" is disabled.\n\nWould you like to enable "Show Author Credits" now? If not, the default settings will be restored.')) {
			jQuery('#show_author_link').val('1');
		} else {
			jQuery('#video_merchant_currency').val('USD');
			jQuery('#temp_download_link_expiration').val('2');
			jQuery('#email_admin_order_notices').val('0');
			jQuery('#purchase_user_login_required').val('0');
			jQuery('#download_user_login_required').val('0');
			jQuery('#exclusive_removed').val('0');
		}
	}
}