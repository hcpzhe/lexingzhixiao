<?php
//公共函数
/**
 * 用户升级应付金额算法
 * @param  $level 当前用户级别
 * @param  $basepoints 基础金额
 */
function get_shouldpay($level, $basepoints) {
	return $basepoints * pow(2,$level);
}

/**
 * 密码加密方法
 */
function pwdHash($password, $type = 'md5') {
	return hash ( $type, C('USER_PW_PREFIX').$password );
}

/**
 * 处理要在数据库中使用的字符串
 */
function strfordb($string) {
	return str_replace( array('%','_'), array('\%','\_'), $string );
}

/**
 * 批量ID处理
 */
function idshandle($idstr) {
//	$idstr = trim(trim($idstr,','));
	if (preg_match('/^([0-9]+(\,){0,1})+[^\,]$/',$idstr)) {
		return TRUE;
	}
	return FALSE;
}

/**
 * 站点URL匹配判断
 */
function urlmatch($url) {
	if (preg_match('/^(https?:\/\/)?([\da-z\.-])+\.([a-z\.]{2,6})\/?$/',$url)) {
		return TRUE;
	}
	return FALSE;
}

/**
 * 数据库查询前,时间区间处理
 */
function timehandle($start,$end) {
	$result = FALSE;
	$start_time = strtotime($start);//开始时间
	if ($start_time > 0) $result = array('egt',$start_time);//大于等于开始时间
	
	$end_time = strtotime($end);//截止时间
	if ($end_time > 0) {
		$tmp_end_time = getdate($end_time);
		$end_time = mktime('23','59','59',$tmp_end_time['mon'],$tmp_end_time['mday'],$tmp_end_time['year']);
		//小于等于截止时间
		if (is_array($result)) $result = array($result,array('elt',$end_time));
		else $result = array('elt',$end_time);
	}
	return $result;
}

/**
 * Enter description here ...
 * @param array $list
 * @param string/array $filed
 */
function field_unique($list, $filed) {
	$arr = array();
	if (!empty($list)) {
		if (is_array($filed)) {
			foreach ($filed as $k => $v) {
				$arr[$k] = field_unique($list,$v);
			}
		}else {
			$filedarr = explode(",", $filed);
			foreach ($list as $row) {
				foreach ($filedarr as $k => $v) {
					$arr[] = $row[$v];
				}
			}
			$arr = array_unique($arr);
		}
	}
	return $arr;
}
