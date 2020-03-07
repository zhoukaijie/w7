<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->func('communication');
load()->func('db');
load()->model('system');

$cloud_ready = cloud_prepare();
if (is_error($cloud_ready)) {
	message($cloud_ready['message'], url('cloud/diagnose'), 'error');
}

$dos = array('upgrade', 'get_upgrade_info', 'get_error_file_list');
$do = in_array($do, $dos) ? $do : 'upgrade';

if ($do == 'upgrade') {
	if (empty($_W['setting']['cloudip']) || $_W['setting']['cloudip']['expire'] < TIMESTAMP) {
	//	$cloudip = gethostbyname('api-upgrade.w7.cc');
	$cloudip = '127.0.0.1';
		if (empty($cloudip) || !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $cloudip)) {
			itoast('云服务域名解析失败，请查看服务器DNS设置或是在“云服务诊断”中手动设置云服务IP', url('cloud/diagnose'), 'error');
		}
		setting_save(array('ip' => $cloudip, 'expire' => TIMESTAMP + 3600), 'cloudip');
	}

	$path = IA_ROOT . '/data/patch/' . date('Ymd') . '/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($patchpath = readdir($handle))) {
				if ($patchpath != '.' && $patchpath != '..') {
					if(is_dir($path.$patchpath)){
						$patchs[] = $patchpath;
					}
				}
			}
		}
		if (!empty($patchs)) {
			sort($patchs, SORT_NUMERIC);
		}
	}
		$scrap_file = system_scrap_file();
	$have_no_permission_file = array();
	foreach ($scrap_file as $key => $file) {
		if (!file_exists(IA_ROOT . $file)) {
			continue;
		}
		$result = @unlink(IA_ROOT . $file);
		if (!$result) {
			$have_no_permission_file[] = $file;
		}
	}
	if ($have_no_permission_file) {
		itoast(implode('<br>', $have_no_permission_file) . '<br>以上废弃文件删除失败，可尝试将文件权限设置为777，再行删除！', referer(), 'error');
	}
}
if ($do == 'get_error_file_list') {
	$error_file_list = array();
	cloud_file_permission_pass($error_file_list);
	iajax(0, !empty($error_file_list) ? $error_file_list : '');
}
if ($do == 'get_upgrade_info') {
	$notice_str = '<div class="content text-left we7-margin-left"><div class="we7-margin-bottom-sm">云服务向您的服务器传输数据过程中发生错误！</div><div class=" we7-margin-bottom-sm color-gray">尝试解决以下已知问题后再试：</div>';

	if ($_W['config']['setting']['timezone'] != 'Asia/Shanghai') {
		iajax(-1, $notice_str . '<div class="color-red">请把服务器时间修改为北京时间，即修改config.php中timezone为Asia/Shanghai</div></div>');
	}
	if (empty($_W['setting']['site']) || empty($_W['setting']['site']['url'])) {
		iajax(-1, $notice_str . '<div class="color-red">站点信息不完整，请重置站点 <a href="./index.php?c=cloud&a=diagnose" class="color-default" target="_blank"> 去重置</a></div></div>');
	}
	if (parse_url($_W['siteroot'], PHP_URL_HOST) != parse_url($_W['setting']['site']['url'], PHP_URL_HOST)) {
		iajax(-1, $notice_str . '<div class="color-red">1. 请使用微擎授权域名进行更新，授权域名为：' . $_W['setting']['site']['url'] . '<br>2. 重置站点 <a href="./index.php?c=cloud&a=diagnose" class="color-default" target="_blank"> 去重置</a></div></div>');
	}
	
	$upgrade = cloud_build();
	if (is_error($upgrade)) {
		iajax(-1, $notice_str . '<div class="color-red">1.请关闭服务器的防火墙，杀毒软件，cdn，云锁，云盾，安全狗之类的软件后再重试；<br>2.下载最新的cloud.mod.php覆盖到/framework/model/cloud.mod.php <a href="//cdn.w7.cc/we7/cloud.mod.php" class="color-default" target="_blank"> 点击下载</a>；<br>3.重置站点 <a href="./index.php?c=cloud&a=diagnose" class="color-default" target="_blank"> 去重置</a></div></div>');
	}

	if (!$upgrade['upgrade']) {
		cache_delete(cache_system_key('checkupgrade'));
		cache_delete(cache_system_key('cloud_transtoken'));
		iajax(1, '检查结果: 恭喜, 你的程序已经是最新版本. ');
	}
	if (!empty($upgrade['schemas'])) {
		$upgrade['database'] = cloud_build_schemas($upgrade['schemas']);
	}
	if (!empty($upgrade['files'])) {
		foreach ($upgrade['files'] as &$file) {
			if (is_file(IA_ROOT . $file)) {
				$file = 'M ' . $file;
			} else {
				$file = 'A ' . $file;
			}
		}
		unset($value);
	}
	iajax(0, $upgrade);
}
template('cloud/upgrade');