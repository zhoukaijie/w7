<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('setting');

$dos = array('display', 'save_expire', 'change_status', 'setting');
$do = in_array($do, $dos) ? $do : 'display';

$user_expire = setting_load('user_expire');
$user_expire = !empty($user_expire['user_expire']) ? $user_expire['user_expire'] : array();

if ($do == 'display') {
	$user_expire['day'] = !empty($user_expire['day']) ? $user_expire['day'] : 1;
	$user_expire['status'] = !empty($user_expire['status']) ? $user_expire['status'] : 0;
}

if ($do == 'save_expire') {
	$type = safe_gpc_string($_GPC['type']);

	if ($type == 'day') {
		$user_expire['day'] = !empty($_GPC['day']) ? intval($_GPC['day']) : 1;
		$url = url('user/expire');
	} elseif ($type == 'notice') {
		$user_expire['notice'] = !empty($_GPC['notice']) ? safe_gpc_string($_GPC['notice']) : '';
		$url = url('user/expire/setting');
	}

	$result = setting_save($user_expire, 'user_expire');
	if (is_error($result)) {
		iajax(-1, '设置失败', $url);
	}
	iajax(0, '设置成功', $url);
}

if ($do == 'change_status') {
	$type = safe_gpc_string($_GPC['type']);

	if ($type == 'status') {
		$user_expire['status'] = empty($user_expire['status']) ? 1 :0;
		$url = url('user/expire');
	} elseif ($type == 'status_store_button') {
		$user_expire['status_store_button'] = empty($user_expire['status_store_button']) ? 1 :0;
		$url = url('user/expire/setting');
	} elseif ($type == 'status_store_redirect') {
		$user_expire['status_store_redirect'] = empty($user_expire['status_store_redirect']) ? 1 :0;
		$url = url('user/expire/setting');
	}

	$result = setting_save($user_expire, 'user_expire');
	if (is_error($result)) {
		iajax(-1, '设置失败', $url);
	}
	iajax(0, '设置成功', $url);
}

if ($do == 'setting') {
	$user_expire['notice'] = !empty($user_expire['notice']) ? $user_expire['notice'] : '您的账号已到期，请前往商城购买续费';
	$user_expire['status_store_button'] = !empty($user_expire['status_store_button']) ? $user_expire['status_store_button'] : 0;
	$user_expire['status_store_redirect'] = !empty($user_expire['status_store_redirect']) ? $user_expire['status_store_redirect'] : 0;
}

template('user/expire');