/**
 * RSsubmit v1.3
 * Dependencies jQuery
 * Copyright RockSnap
 */
(function ($) {

	var RSsubmitSet = {
		success_alert : false, //成功后是否 alert data.info
		error_alert : true, //失败后alert
		reload : false, //如果没有返回 data.url 是否刷新当前页面
		type : '',
		url : '',
		data : ''
		};
	
	$.fn.RSsubmit = function(options) {
		var myset = {};
		$.extend( myset, RSsubmitSet, options );
		
		if (myset.url == '') {
			myset.url = $(this).attr('action');
			if (myset.url == '' || myset.url == undefined) {
				myset.url = $(this).attr('href');
			}
		}
		if (myset.url == '' || myset.url == undefined) return false;
		
		if (myset.type == '') myset.type = $(this).attr('method');
		if (myset.type != 'post') myset.type = 'get';
		
		if (myset.data == '') {
			myset.data = $(this).serializeArray();
		}
		
		$.ajax({
			cache: false,
			type:  myset.type,
			url: myset.url,
			data: myset.data,
			async: false, //同步请求, 其它操作必须等待请求完成才可以执行
//			error: function(request) {
//				alert("RSsubmit error");
//			},
			success: function(dd) {
				if (dd.status == 1) {
					//成功
					if (myset.success_alert) alert(dd.info);
				}else {
					if (myset.error_alert) alert(dd.info);
				}
				
				if (dd.url) window.location.href = dd.url;
				else if (myset.reload && dd.status == 1) window.location.reload();
			}
		});
	};
})(jQuery);