<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);

load()->model('user');
load()->model('message');
load()->classs('oauth2/oauth2client');
load()->model('setting');

if (!empty($_W['uid']) && $_GPC['handle_type'] != 'bind') {
	itoast('请先退出再登录！');
}
if (checksubmit() || $_W['isajax']) {
	_login($_GPC['referer']);
}

$support_login_types = OAuth2Client::supportThirdLoginType();
if (in_array($_GPC['login_type'], $support_login_types)) {
	_login($_GPC['referer']);
}

$setting = $_W['setting'];
$_GPC['login_type'] = !empty($_GPC['login_type']) ? $_GPC['login_type'] : (!empty($_W['setting']['copyright']['mobile_status']) ? 'mobile': 'system');

$login_urls = user_support_urls();
template('user/login');

function _login($forward = '') {
	global $_GPC, $_W;
	if (empty($_GPC['login_type'])) {
		$_GPC['login_type'] = 'system';
	}

	if (empty($_GPC['handle_type'])) {
		$_GPC['handle_type'] = 'login';
	}

	if ($_GPC['handle_type'] == 'login') {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->login();
	} else {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->bind();
	}

	if (!empty($_W['user']) && $_GPC['handle_type'] != ''  && $_GPC['handle_type'] == 'bind') {
		if (is_error($member)) {
			itoast($member['message'], url('user/profile/bind'), '');
		} else {
			itoast('绑定成功', url('user/profile/bind'), '');
		}
	}

	if (is_error($member)) {
		itoast($member['message'], url('user/login'), '');
	}

	$record = user_single($member);
	$failed = pdo_get('users_failed_login', array('username' => trim($_GPC['username'])));
	if (!empty($record)) {
		if ($record['status'] == USER_STATUS_CHECK || $record['status'] == USER_STATUS_BAN) {
			itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决?', url('user/login'), '');
		}
		$_W['uid'] = $record['uid'];
		$_W['isfounder'] = user_is_founder($record['uid']);
		$_W['user'] = $record;

		$support_login_bind_types = Oauth2CLient::supportThirdLoginBindType();
		if (in_array($_GPC['login_type'], $support_login_bind_types) && !empty($_W['setting']['copyright']['oauth_bind']) && !$record['is_bind'] && empty($_W['isfounder']) && ($record['register_type'] == USER_REGISTER_TYPE_QQ || $record['register_type'] == USER_REGISTER_TYPE_WECHAT)) {
			message('您还没有注册账号，请前往注册', url('user/third-bind/bind_oauth', array('uid' => $record['uid'], 'openid' => $record['openid'], 'register_type' => $record['register_type'])));
			exit;
		}

		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			itoast('站点已关闭，关闭原因:'. $_W['setting']['copyright']['reason'], '', '');
		}

		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = !empty($record['hash']) ? $record['hash'] : md5($record['password'] . $record['salt']);
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		user_update($status);

		if (empty($forward)) {
			$forward = user_login_forward($_GPC['forward']);
		}
				$forward = safe_gpc_url($forward);

		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		if (!empty($failed)) {
			pdo_delete('users_failed_login', array('id' => $failed['id']));
		}

		$user_endtime = $_W['user']['endtime'];
		if (!empty($user_endtime) && !in_array($user_endtime, array(USER_ENDTIME_GROUP_EMPTY_TYPE, USER_ENDTIME_GROUP_UNLIMIT_TYPE)) && $user_endtime < TIMESTAMP) {
			$user_is_expired = true;
		}

		if ((empty($_W['isfounder']) || user_is_vice_founder()) && $user_is_expired) {
			$user_expire = setting_load('user_expire');
			$user_expire = !empty($user_expire['user_expire']) ? $user_expire['user_expire'] : array();
			$notice = !empty($user_expire['notice']) ? $user_expire['notice'] : '您的账号已到期，请前往商城购买续费';
			$redirect = !empty($user_expire['status_store_redirect']) && $user_expire['status_store_redirect'] == 1 ? url('home/welcome/ext', array('m' => 'store')) : '';
			$extend_buttons = array();
			if (!empty($user_expire['status_store_button']) && $user_expire['status_store_button'] == 1) {
				$extend_buttons['status_store_button'] = array(
					'url' => url('home/welcome/ext', array('m' => 'store')),
					'class' => 'btn btn-primary',
					'title' => '去商城续费',
				);
			}
			$extend_buttons['cancel'] = array(
				'url' => url('user/profile'),
				'class' => 'btn btn-default',
				'title' => '取消',
			);
			message($notice, $redirect, 'expired', '', $extend_buttons);
		}
		cache_build_frame_menu();
		itoast("欢迎回来，{$record['username']}", $forward, 'success');
	} else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => trim($_GPC['username']), 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		itoast('登录失败，请检查您输入的账号和密码', '', '');
	}
}
