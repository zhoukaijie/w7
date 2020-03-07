<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->model('setting');

$dos = array(
	'auth',
	'build',
	'init',
	'schema',
	'download',
	'module.query',
	'module.bought',
	'module.info',
	'module.build',
	'module.setting.cloud',
	'theme.query',
	'theme.info',
	'theme.build',
	'application.build',
	'sms.send',
	'sms.info',
	'api.oauth',
);
$do = in_array($do, $dos) ? $do : '';

if($do != 'auth') {
	if(is_error(cloud_prepare())) {
		exit('cloud service is unavailable.');
	}
}

$post = file_get_contents('php://input');

if($do == 'auth') {
	$auth = @json_decode(base64_decode($post), true);
	if (empty($auth)) {
		exit('推送的站点数据有误');
	}
	setting_save($auth, 'site');
	exit('success');
}

if($do == 'build') {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/application.build', iserializer($ret));
		exit($secret);
	}
}

if($do == 'schema') {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/application.schema', iserializer($ret));
		exit($secret);
	}
}

if($do == 'download') {
	$data = base64_decode($post);
	if (base64_encode($data) !== $post) {
		$data = $post;
	}
	$ret = iunserializer($data);
	$gz = function_exists('gzcompress') && function_exists('gzuncompress');
	$file = base64_decode($ret['file']);
	if($gz) {
		$file = gzuncompress($file);
	}
	
	$_W['setting']['site']['token'] = authcode(cache_load(cache_system_key('cloud_transtoken')), 'DECODE');
	$string = (md5($file) . $ret['path'] . $_W['setting']['site']['token']);
	if(!empty($_W['setting']['site']['token']) && md5($string) === $ret['sign']) {
						if (strpos($ret['path'], '/web/') === 0 || strpos($ret['path'], '/framework/') === 0) {
			$patch_path = sprintf('%s/data/patch/upgrade/%s', IA_ROOT, date('Ymd'));
		} else {
			$patch_path = IA_ROOT;
		}
		$path = $patch_path . $ret['path'];
		load()->func('file');
		@mkdirs(dirname($path));
		file_put_contents($path, $file);
		$sign = md5(md5_file($path) . $ret['path'] . $_W['setting']['site']['token']);
		if($ret['sign'] === $sign) {
			exit('success');
		}
	}
	exit('failed');
}

if(in_array($do, array('module.query', 'module.bought', 'module.info', 'module.build', 'theme.query', 'theme.info', 'theme.build', 'application.build'))) {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/' . $do, iserializer($ret));
		exit($secret);
	}
}

if ($do == 'module.setting.cloud') {
	$data = __secure_decode($post);
	$data = iunserializer($data);
	$setting = $data['setting'];
	$uniacid = $data['acid'];
	$_W['uniacid'] = $data['acid'];
	$module = WeUtility::createModule($data['module']);
	$module->saveSettings($setting);
	cache_delete(cache_system_key('module_info', array('module_name' => $data['module'])));
	cache_delete(cache_system_key('module_setting', array('module_name' => $data['module'], 'uniacid' => $_W['uniacid'])), $setting);
	echo 'success';
	exit;
}

if ($do == 'sms.send') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
}

if ($do == 'sms.info') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
	if(!empty($dat) && is_array($dat)) {
		setting_save($dat, "sms.info");
		cache_clean();
		die('success');
	}
	die('fail');
}

if ($do == 'api.oauth') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
	if(!empty($dat) && is_array($dat)) {
		if ($dat['module'] == 'core') {
			$result = file_put_contents(IA_ROOT.'/framework/builtin/core/module.cer', $dat['access_token']);
		} else {
			$result = file_put_contents(IA_ROOT."/addons/{$dat['module']}/module.cer", $dat['access_token']);
		}
		if ($result !== false) {
			die('success');
		}
		die('获取到的访问云API的数字证书写入失败.');
	}
	die('获取云API授权失败: api oauth.');
}

function __secure_decode($post) {
	global $_W;
	$data = base64_decode($post);
	if (base64_encode($data) !== $post) {
		$data = $post;
	}
	$ret = iunserializer($data);
	$string = ($ret['data'] . $_W['setting']['site']['token']);
	if(md5($string) === $ret['sign']) {
		return $ret['data'];
	}
	return false;
}