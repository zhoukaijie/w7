<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
define('CLOUD_GATEWAY_URL', $_URLTYPE.$_SERVER['HTTP_HOST']);
define('CLOUD_GATEWAY_URL_NORMAL', $_URLTYPE.$_SERVER['HTTP_HOST']);
function cloud_client_define() {
	return array(
		'/framework/function/communication.func.php',
		'/framework/model/cloud.mod.php',
		'/web/source/cloud/upgrade.ctrl.php',
		'/web/source/cloud/process.ctrl.php',
		'/web/source/cloud/dock.ctrl.php',
		'/web/themes/default/cloud/upgrade.html',
		'/web/themes/default/cloud/process.html'
	);
}


function _cloud_build_params($must_authorization_host = true) {
	global $_W;
	$pars = array();
	$pars['host'] = strexists($_SERVER['HTTP_HOST'], ':') ? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) : $_SERVER['HTTP_HOST'];
	if (is_array($_W['setting']['site']) && !empty($_W['setting']['site']['url']) && !$must_authorization_host) {
		$pars['host'] = parse_url($_W['setting']['site']['url'], PHP_URL_HOST);
	}
	$pars['https'] = $_W['ishttps'] ? true : false;
	$pars['family'] = IMS_FAMILY;
	$pars['version'] = IMS_VERSION;
	$pars['php_version'] = PHP_VERSION;
	$pars['current_host'] = $_SERVER['HTTP_HOST'];
	$pars['release'] = IMS_RELEASE_DATE;
	if (!empty($_W['setting']['site'])) {
		$pars['key'] = $_W['setting']['site']['key'];
		$pars['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
	}
	$clients = cloud_client_define();
	$string = '';
	foreach($clients as $cli) {
		$string .= md5_file(IA_ROOT . $cli);
	}
	$pars['client'] = md5($string);
	return $pars;
}

function _cloud_shipping_parse($dat, $file) {
	if (is_error($dat)) {
		return error(-1, '网络传输错误, 请检查您的cURL是否可用, 或者服务器网络是否正常. ' . $dat['message']);
	}
	$tmp = iunserializer($dat['content']);
	if (is_array($tmp) && is_error($tmp)) {
		if ($tmp['errno'] == '-2') {
			$data = file_get_contents(IA_ROOT . '/framework/version.inc.php');
			file_put_contents(IA_ROOT . '/framework/version.inc.php', str_replace("'x'", "'v'", $data));
		}
		return $tmp;
	}
	if ($dat['content'] == 'patching') {
		return error(-1, '补丁程序正在更新中，请稍后再试！');
	}
	if ($dat['content'] == 'frequent') {
		return error(-1, '更新操作太频繁，请稍后再试！');
	}
	if ($dat['content'] == 'blacklist') {
		return error(-1, '抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
	}
	/*
	$content = json_decode($dat['content'], true);
	if (!empty($content['error'])) {
		return error(-1, $content['error']);
	}
	if (!empty($content) && is_array($content)) {
		return $content;
	}

	if (strlen($dat['content']) != 32) {
		$dat['content'] = iunserializer($dat['content']);
		if (is_array($dat['content']) && isset($dat['content']['files'])) {
			if (!empty($dat['content']['manifest'])) {
				$dat['content']['manifest'] = base64_decode($dat['content']['manifest']);
			}
			if (!empty($dat['content']['scripts'])) {
				$dat['content']['scripts'] = base64_decode($dat['content']['scripts']);
			}
			return $dat['content'];
		}
		return error(-1, '云服务平台向您的服务器传输数据过程中出现错误, 这个错误可能是由于您的通信密钥和云服务不一致, 请尝试诊断云服务参数(重置站点ID和通信密钥). 传输原始数据:' . $dat['meta']);
	}
	$data = @file_get_contents($file);
	if (empty($data)) {
		return error(-1, '没有接收到服务器的传输的数据.');
	}
	@unlink($file);
	$ret = @iunserializer($data);
	if (empty($data) || empty($ret) || $dat['content'] != $ret['secret']) {
		return error(-1, '云服务平台向您的服务器传输的数据校验失败, 可能是因为您的网络不稳定, 或网络不安全, 请稍后重试.');
	}
	$ret = iunserializer($ret['data']);
	if (is_array($ret) && is_error($ret)) {
		if ($ret['errno'] == '-2') {
			$data = file_get_contents(IA_ROOT . '/framework/version.inc.php');
			file_put_contents(IA_ROOT . '/framework/version.inc.php', str_replace("'x'", "'v'", $data));
		}
		if ($ret['errno'] == '-3') { 			return array(
				'errno' => $ret['errno'],
				'message' => $ret['message'],
				'cloud_id' => $ret['data'],
			);
		}
	}
	if (!is_error($ret) && is_array($ret) && !empty($ret)) {
		if ($ret['state'] == 'fatal') {
			return error($ret['errorno'], '发生错误: ' . $ret['message']);
		}
		return $ret;
	} else {
		return error($ret['errno'], "发生错误: {$ret['message']}");
	}*/
	return array();
}

function cloud_request($url, $post = '', $extra = array(), $timeout = 60) {
	global $_W;
	load()->func('communication');
	if (!empty($_W['setting']['cloudip']['ip']) && empty($extra['ip'])) {
//	$extra['ip'] = $_W['setting']['cloudip']['ip'];
		$extra['ip'] = $_SERVER['HTTP_HOST'];
	}
/*	if (strexists($url, 's.we7.cc')) {
		$extra = array();
	}
	$response = ihttp_request($url, $post, $extra, $timeout);
	if (is_error($response)) {
		setting_save(array(), 'cloudip');
	}
	return $response;
*/
	return array();
}
function cloud_api($method, $data = array(), $extra = array(), $timeout = 60) {
/*	$cache_key = cache_system_key($method);
	$cache = cache_load($cache_key);
		if (!empty($cache) && $cache['expire'] > time() && $method != 'module/query') {
		return $cache;
	}
	$api_url = 'http://api.w7.cc/%s';
	$pars = _cloud_build_params();
	$pars['token'] = cloud_build_transtoken();
	$data = array_merge($pars, $data);
	if ($GLOBALS['_W']['config']['setting']['development'] == 3) {
		$extra['CURLOPT_USERAGENT'] = 'development';
	}
	$response = ihttp_request(sprintf($api_url, $method), $data, $extra, $timeout);
	$file = IA_ROOT . '/data/' . (!empty($data['method']) ? $data['method'] : $method);
	$ret = _cloud_shipping_parse($response, $file);
	cache_write($cache_key, $ret, 300);
	return $ret;
*/
	return null;
}
function cloud_api_request($method, $data = array(), $extra = array(), $timeout = 60) {
	load()->func('communication');
//	$api_url = 'http://api.w7.cc/%s';	
        $api_url = CLOUD_GATEWAY_URL;
	$pars = _cloud_build_params();
	$data = array_merge($pars, $data);
	$response = ihttp_request(sprintf($api_url, $method), $data, $extra, $timeout);
	$result = _cloud_response_parse($response);
	return $result;
}

function cloud_prepare() {
	global $_W;
	setting_load();
/*	if(empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
		return error('-1', '站点注册信息丢失, 请通过"重置站点ID和通信密钥"重新获取 !');
	}
	*/
	return true;
}

function cloud_build(){
/*
	$pars = _cloud_build_params();
	$pars['method'] = 'application.build4';
	$dat = cloud_request('http://api-upgrade.w7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/application.build';
	$ret = _cloud_shipping_parse($dat, $file);
	if (is_error($ret)) {
		return $ret;
	}

	if($ret['state'] == 'warning') {
		$ret['files'] = cloud_client_define();
		unset($ret['schemas']);
		unset($ret['scripts']);
	} else {
		$patch_path = sprintf('%s/data/patch/upgrade/%s', IA_ROOT, date('Ymd'));
		if (!is_dir($patch_path)) {
			mkdirs($patch_path);
		}

		$files = $files_allowed = array();
		if(!empty($ret['files'])) {
			foreach($ret['files'] as $file) {
				$entry = IA_ROOT . $file['path'];
				if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files_allowed[] = $file['path'];
				}

				$entry = $patch_path . $file['path'];
				if (!is_file($entry)) {
					$entry = IA_ROOT . $file['path'];
				}
				if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files[] = $file['path'];
				}
			}
		}
		$ret['files'] = $files;
		if (!empty($ret['files'])) {
			cloud_bakup_files($ret['files']);
		} else {
			if (!empty($files_allowed)) {
				foreach ($files_allowed as $file) {
					$dir = pathinfo(IA_ROOT . $file, PATHINFO_DIRNAME);
					if (!is_dir($dir)) {
						mkdirs($dir);
					}
					file_put_contents(IA_ROOT . $file, file_get_contents($patch_path . $file));
				}
				rmdirs($patch_path);
			}
		}
		$schemas = array();
			if(!empty($ret['schemas'])) {
				load()->func('db');
				foreach($ret['schemas'] as $remote) {
					$name = substr($remote['tablename'], 4);
					$local = db_table_schema(pdo(), $name);
					unset($remote['increment']);
					unset($local['increment']);
					if(empty($local)) {
						$schemas[] = $remote;
					} else {
						$sqls = db_table_fix_sql($local, $remote);
						if(!empty($sqls)) {
							$schemas[] = $remote;
						}
					}
				}
			}
			$ret['schemas'] = $schemas;
		}

		if (IMS_FAMILY != $ret['family']) {
			$update_version_success = setting_upgrade_version($ret['family'], IMS_VERSION, IMS_RELEASE_DATE);
			if (empty($update_version_success)) {
				message('切换版本失败，请修改 /framework/version.inc.php 文件权限为 User 可写或是 777', 'refresh', 'error');
			} else {
				message('更新系统正在为您自动切换版本', 'refresh');
			}
		}
		$ret['upgrade'] = false;
		if(!empty($ret['files']) || !empty($ret['schemas']) || !empty($ret['scripts'])) {
			$ret['upgrade'] = true;
		}
		$upgrade = array();
		$upgrade['upgrade'] = $ret['upgrade'];
		$upgrade['data'] = $ret;
		$upgrade['lastupdate'] = TIMESTAMP;
		cache_write(cache_system_key('upgrade'), $upgrade);
		cache_write(cache_system_key('cloud_transtoken'), authcode($ret['token'], 'ENCODE'));

	return $ret;
*/

    return null;
}

function cloud_schema() {
	$pars = _cloud_build_params();
	$pars['method'] = 'application.schema';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/application.schema';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		$schemas = array();
		if(!empty($ret['schemas'])) {
			load()->func('db');
			foreach($ret['schemas'] as $remote) {
				$name = substr($remote['tablename'], 4);
				$local = db_table_schema(pdo(), $name);
				unset($remote['increment']);
				unset($local['increment']);
				if(empty($local)) {
					$schemas[] = $remote;
				} else {
					$diffs = db_schema_compare($local, $remote);
					if(!empty($diffs)) {
						$schemas[] = $remote;
					}
				}
			}
		}
		$ret['schemas'] = $schemas;
	}
	return $ret;
}

function cloud_download($path, $type = '') {
	global $_W;
	$pars = _cloud_build_params();
	$pars['method'] = 'application.shipping';
	$pars['path'] = $path;
	$pars['type'] = $type;
	$pars['gz'] = function_exists('gzcompress') && function_exists('gzuncompress') ? 'true' : 'false';
	$pars['download'] = 'true';
	$headers = array('content-type' => 'application/x-www-form-urlencoded');
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, $headers, 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	if($dat['content'] == 'success') {
		return true;
	}
	$ret = @json_decode($dat['content'], true);
	if (isset($ret['error'])) {
		return error(1, $ret['error']);
	}
	if(is_error($ret)) {
		return $ret;
	} else {
		$post = $dat['content'];
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
			$error_file_list = array();
			if (!cloud_file_permission_pass($error_file_list)) {
				return error(-1, '请修复下列文件读写权限 : ' . implode('; ', $error_file_list));
			}
						if ($type == 'module' || $type == 'theme') {
				$patch_path = IA_ROOT;
			} else {
				$patch_path = sprintf('%s/data/patch/upgrade/%s', IA_ROOT, date('Ymd'));
			}
			$path = $patch_path . $ret['path'];
			load()->func('file');
			@mkdirs(dirname($path));
			if (file_put_contents($path, $file)) {
				return true;
			} else {
				return error(-1, '写入失败');
			}
		}
		return error(-1, '写入失败');
	}
}

function cloud_m_prepare($name) {
	$pars['method'] = 'module.check';
	$pars['module'] = $name;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	if (is_error($dat)) {
		return $dat;
	}
	if ($dat['content'] == 'install-module-protect') {
	//	return error('-1', '此模块已设置版权保护，您只能通过云平台来安装。');
	}
	return true;
}


function cloud_m_build($modulename, $type = ''){
/*	$type = in_array($type, array('uninstall')) ? $type : '';
	if (empty($modulename)) {
		return array();
	}
	$module = table('modules')->getByName($modulename);
    $pars = _cloud_build_params();
    $pars['method'] = 'module.build';
    $pars['module'] = $modulename;
    $pars['type'] = $type;
	$pars['token'] = cloud_build_transtoken();
    if (!empty($module)) {
        $pars['module_version'] = $module['version'];
    }
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
    $file = IA_ROOT . '/data/module.build';
    $ret = _cloud_shipping_parse($dat, $file);
    if (!is_error($ret)) {
        $dir = IA_ROOT . '/addons/' . $modulename;
        $files = array();
        if (!empty($ret['files'])) {
            foreach ($ret['files'] as $file) {
                $entry = $dir . $file['path'];
                if (!is_file($entry) || md5_file($entry) != $file['checksum']) {
                    $files[] = '/' . $modulename . $file['path'];
                }
            }
        }
        $ret['files'] = $files;
        $schemas = array();
        if (!empty($ret['schemas'])) {
            load()->func('db');
            foreach ($ret['schemas'] as $remote) {
                $name = substr($remote['tablename'], 4);
                $local = db_table_schema(pdo() , $name);
                unset($remote['increment']);
                unset($local['increment']);
                if (empty($local)) {
                    $schemas[] = $remote;
                } else {
                    $diffs = db_table_fix_sql($local, $remote);
                    if (!empty($diffs)) {
                        $schemas[] = $remote;
                    }
                }
            }
        }
        $ret['upgrade'] = true;
        $ret['type'] = 'module';
        $ret['schemas'] = $schemas;
        if (empty($module)) {
            $ret['install'] = 1;
        }
		cache_write(cache_system_key('cloud_transtoken'), authcode($ret['token'], 'ENCODE'));
    }
    return $ret;
    */
    return null;
}


function cloud_m_query($module = array(), $page = 0) {
	$pars['method'] = 'module.query';
	if (empty($module)) {
		$module = cloud_extra_module();
	}
	if (!is_array($module)) {
		$module = array($module);
	}
	if (intval($page) > 0) {
		$pars['page'] = $page;
	}
	$pars['module'] = base64_encode(iserializer($module));
	$ret = cloud_api('module/query/index', $pars);
	if (isset($ret['error'])) {
		return error(1, $ret['error']);
	}
/*	if (!is_error($ret)) {
		$pirate_apps = $ret['pirate_apps'];
		unset($ret['pirate_apps']);
		$support_names = array('app', 'wxapp', 'webapp', 'system_welcome', 'android', 'ios', 'xzapp', 'aliapp', 'baiduapp', 'toutiaoapp');

		foreach ($ret['data'] as $modulename => &$info) {
			foreach ($support_names as $support) {
				if (in_array($support, $info['site_branch']['bought']) && !empty($info['site_branch']["{$support}_support"]) && $info['site_branch']["{$support}_support"] == 2) {
					$info['site_branch']["{$support}_support"] = 2;
				} else {
					$info['site_branch']["{$support}_support"] = 1;
				}
			}
		}
		$ret['pirate_apps'] = $pirate_apps;
	}
	*/
	return $ret;
}

function cloud_m_bought() {
	$pars = _cloud_build_params();
	$pars['method'] = 'module.bought';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/module.bought';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_m_info($name) {
	$pars['method'] = 'module.info';
	$pars['module'] = $name;
	$ret = cloud_api('module/info/index', $pars);
	return $ret;
}


function cloud_m_upgradeinfo($modulename) {
	load()->model('module');
	
	$module = module_fetch($modulename);
	
	$pars = _cloud_build_params();
	$pars['method'] = 'module.info';
	$pars['module'] = $modulename;
	$pars['curversion'] = $module['version'];
	$pars['isupgrade'] = 1;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/module.info';
	$ret = _cloud_shipping_parse($dat, $file);

	if (empty($ret)) {
		return array();
	}
	if (is_error($ret)) {
		return $ret;
	}
	if (version_compare($ret['version']['version'], $module['version'], '>')) {
		$ret['upgrade'] = true;
	}
		$ret['site_branch'] = $ret['branches'][$ret['version']['branch_id']];
		$ret['from'] = 'cloud';
		foreach ($ret['branches'] as &$branch) {
		if ($branch['displayorder'] > $ret['site_branch']['displayorder'] || ($branch['displayorder'] == $ret['site_branch']['displayorder'] && $ret['site_branch']['id'] < intval($branch['id']))) {
			$ret['new_branch'] = true;
			}
		$branch['id'] = intval($branch['id']);
		$branch['version']['description'] = preg_replace('/\n/', '<br/>', $branch['version']['description']);
		$branch['displayorder'] = intval($branch['displayorder']);
		$branch['day'] = intval(date('d', $branch['version']['createtime']));
		$branch['month'] = date('Y.m', $branch['version']['createtime']);
		$branch['hour'] = date('H:i', $branch['version']['createtime']);
	}
	unset($branch);
	return $ret;
}

function cloud_t_prepare($name){
/*	$pars['method'] = 'theme.check';
	$pars['theme'] = $name;
	$dat = cloud_request('http://api-upgrade.w7.cc/gateway.php', $pars);
	if (is_error($dat)) {
		return $dat;
	}
	if ($dat['content'] == 'install-theme-protect') {
		return error('-1', '此模板已设置版权保护，您只能通过云平台来安装。');
	}

*/
    return true;
}


function cloud_t_query(){
/*	$pars = _cloud_build_params();
	$pars['method'] = 'theme.query';
	$pars['theme'] = cloud_extra_theme();
	$dat = cloud_request('http://api-upgrade.w7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/theme.query';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
	*/
    return null;
}

function cloud_t_info($name){
/*	$pars = _cloud_build_params();
	$pars['method'] = 'theme.info';
	$pars['theme'] = $name;
	$dat = cloud_request('http://api-upgrade.w7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/theme.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;

*/
    return null;
}

function cloud_t_build($name) {
	if (empty($name)) {
		return array();
	}
	$theme = table('site_templates')->getByName(trim($name));
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.build';
	$pars['theme'] = $name;
	if(!empty($theme)) {
		$pars['themeversion'] = $theme['version'];
	}
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/theme.build';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		$dir = IA_ROOT . '/app/themes/' . $name;
		$files = array();
		if(!empty($ret['files'])) {
			foreach($ret['files'] as $file) {
				$entry = $dir . $file['path'];
				if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files[] = '/'. $name . $file['path'];
				}
			}
		}
		$ret['files'] = $files;
		$ret['upgrade'] = true;
		$ret['type'] = 'theme';
				if(empty($theme)) {
			$ret['install'] = 1;
		}
		cache_write(cache_system_key('cloud_transtoken'), authcode($ret['token'], 'ENCODE'));
	}
	return $ret;
}


function cloud_t_upgradeinfo($name) {
	if (empty($name)) {
		return array();
	}
	$theme = table('site_templates')->getByName(trim($name));
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.upgrade';
	$pars['theme'] = $theme['name'];
	$pars['version'] = $theme['version'];
	$pars['isupgrade'] = 1;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/module.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_w_prepare($name) {
	$pars['method'] = 'webtheme.check';
	$pars['webtheme'] = $name;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	if (is_error($dat)) {
		return $dat;
	}
	if ($dat['content'] == 'install-webtheme-protect') {
		return error('-1', '此后台皮肤已设置版权保护，您只能通过云平台来安装。');
	}
	return true;
}


function cloud_w_query() {
	$pars = _cloud_build_params();
	$pars['method'] = 'webtheme.query';
	$pars['webtheme'] = cloud_extra_webtheme();
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/webtheme.query';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_w_info($name) {
	$pars = _cloud_build_params();
	$pars['method'] = 'webtheme.info';
	$pars['webtheme'] = $name;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/webtheme.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_w_build($name) {
	$sql = 'SELECT * FROM ' . tablename('webtheme_homepages') . ' WHERE `name`=:name';
	$webtheme = pdo_fetch($sql, array(':name' => $name));

	$pars = _cloud_build_params();
	$pars['method'] = 'webtheme.build';
	$pars['webtheme'] = $name;
	if(!empty($webtheme)) {
		$pars['webtheme_version'] = $webtheme['version'];
	}
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/webtheme.build';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		$dir = IA_ROOT . '/web/themes/' . $name;
		$files = array();
		if(!empty($ret['files'])) {
			foreach($ret['files'] as $file) {
				$entry = $dir . $file['path'];
				if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files[] = '/'. $name . $file['path'];						
						}
			}
		}
		$ret['files'] = $files;
		$ret['upgrade'] = true;
		$ret['type'] = 'webtheme';
				if(empty($webtheme)) {
			$ret['install'] = 1;
		}
		cache_write(cache_system_key('cloud_transtoken'), authcode($ret['token'], 'ENCODE'));
	}
	return $ret;
}


function cloud_w_upgradeinfo($name) {
	$sql = 'SELECT `name`, `version` FROM ' . tablename('webtheme_homepages') . ' WHERE `name` = :name';
	$webtheme = pdo_fetch($sql, array(':name' => $name));
	$pars = _cloud_build_params();
	$pars['method'] = 'webtheme.upgrade';
	$pars['webtheme'] = $webtheme['name'];
	$pars['version'] = $webtheme['version'];
	$pars['isupgrade'] = 1;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars);
	$file = IA_ROOT . '/data/webtheme.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_sms_send($mobile, $content, $postdata = array(), $custom_sign = '', $use_system_balance = false) {
	global $_W;
	if(!preg_match('/^1\d{10}$/', $mobile) || empty($content)) {
		return error(1, '发送短信失败, 原因: 手机号错误或内容为空.');
	}
	$uniacid = empty($use_system_balance) ? $_W['uniacid'] : 0;

	if (empty($uniacid)) {
		$sms_info = cloud_sms_info();
		$balance = empty($sms_info['sms_count']) ? 0 : $sms_info['sms_count'];
		if (!empty($custom_sign)) {
			$sign = $custom_sign;
		}
	} else {
		$row = pdo_get('uni_settings' , array('uniacid' => $uniacid), array('notify'));
		$row['notify'] = @iunserializer($row['notify']);

		$config = $row['notify']['sms'];
		$balance = intval($config['balance']);
        
		$sign = empty($custom_sign) ? $config['signature'] : $custom_sign;
		$account_name = empty($_W['account']['type_name']) ? '' : $_W['account']['type_name'];
		$account_name .= empty($_W['account']['name']) ? '' : " [{$_W['account']['name']}] ";
	}
	if(empty($sign) || $sign == 'null') {
                $sign = '短信宝';
        }
        //判断剩余条数
      if($balance<1){
                return error(-1, '短信发送失败, 原因:余额不足');
        } 
        //短信宝账号
       // $smsbao_info=pdo_get('uni_settings' , array('uniacid' => $_W['uniacid']), array('copyright'));
		//$smsbao_info=setting_load($key = 'copyright');
        $sms_param['u']=$_W['setting']['copyright']['sms_name'];
        $sms_param['p']=md5($_W['setting']['copyright']['sms_password']);
        $sms_param['m']=$mobile;
        $sms_param['c']='【'.$sign.'】'.$content;
        $response = file_get_contents("http://api.smsbao.com/sms?".http_build_query($sms_param));
        if (trim($response)!='0') {
		return error($response['errno'], '短信发送失败, 原因:'.$response['message']);
        }
 
        if (trim($response)=='0') {
		if (!empty($uniacid)) {
			$row['notify']['sms']['balance'] = $row['notify']['sms']['balance'] - 1;
			if ($row['notify']['sms']['balance'] < 0) {
				$row['notify']['sms']['balance'] = 0;
			}
			pdo_update('uni_settings', array('notify' => iserializer($row['notify'])), array('uniacid' => $uniacid));
			uni_setting_save('notify', $row['notify']);
		} else {
			$sms_info['sms_count'] = $sms_info['sms_count'] - 1;
			if ($sms_info['sms_count'] < 0) {
				$sms_info['sms_count'] = 0;
			}
			setting_save($sms_info, 'sms.info');
		}
	}
	return true;
}


function cloud_sms_info() {
        global $_W;
 
        $data=array();
        //返回短信的剩余条数以及签名
        $sms_name=$_W['setting']['copyright']['sms_name'];
        $sms_password=md5($_W['setting']['copyright']['sms_password']);
        $res=file_get_contents("http://api.smsbao.com/query?u={$sms_name}&p={$sms_password}");
        $retArr = explode("\n", $res);
        $balanceArr = explode(",", $retArr[1]);
	$data['sms_count']=$retArr[0] == 0 ? $balanceArr[1]:0;
        return $data;
}

function cloud_extra_account() {
	$data = array();
	$data['accounts'] = pdo_fetchall("SELECT name, account, original FROM ".tablename('account_wechats') . " GROUP BY account");
	return serialize($data);
}


function cloud_extra_module() {
	return table('modules')->getInstalled();
}


function cloud_extra_theme() {
	$sql = 'SELECT `name` FROM ' . tablename('site_templates') . ' WHERE `name` <> :name';
	$themes = pdo_fetchall($sql, array(':name' => 'default'), 'name');
	if (!empty($themes)) {
		return base64_encode(iserializer(array_keys($themes)));
	} else {
		return '';
	}
}


function cloud_extra_webtheme() {
	$sql = 'SELECT `name` FROM ' . tablename('webtheme_templates') . ' WHERE `name` <> :name';
	$themes = pdo_fetchall($sql, array(':name' => 'default'), 'name');
	if (!empty($themes)) {
		return base64_encode(iserializer(array_keys($themes)));
	} else {
		return '';
	}
}

function cloud_module_setting($acid, $module) {
	$pars = array(
		'acid' => $acid,
		'module_name' => $module['name'],
		'module_version' => $module['version'],
	);
	return cloud_api('module/setting/index', $pars);
}

function cloud_module_setting_save($acid, $module_name, $setting) {
	$pars = array(
		'acid' => $acid,
		'module_name' => $module_name,
		'setting' => $setting,
	);
	return cloud_api('module/setting/save', $pars);
}

function cloud_api_redirect($method, $params = array()) {
	$pars = array(
		'method' => $method,
		'params' => $params,
	);
	return cloud_api('util/api/index', $pars);
}


function cloud_cron_create($cron) {
	$pars = array(
		'cron' => base64_encode(iserializer($cron)),
	);
	return cloud_api('site/cron/save', $pars);
}


function cloud_cron_update($cron) {
	return cloud_cron_create($cron); }


function cloud_cron_get($cron_id) {
	$pars = array(
		'cron_id' => $cron_id,
	);
	return cloud_api('site/cron/get', $pars);
}


function cloud_cron_change_status($cron_id, $status) {
	$pars = array(
		'cron_id' => $cron_id,
		'status' => $status,
	);
	return cloud_api('site/cron/status', $pars);
}


function cloud_cron_remove($cron_id) {
	$pars = array(
		'cron_id' => $cron_id,
	);
	return cloud_api('site/cron/remove', $pars);
}

function _cloud_response_parse($result) {
	if (empty($result)) {
		return error(-1, '没有接收到服务器的传输的数据');
	}
	if ($result['content'] == 'blacklist') {
		return error(-1, '抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
	}
	$result = json_decode($result['content'], true);
	if (null === $result) {
		return error(-1, '云服务通讯发生错误，请稍后重新尝试！');
	}
	if (!empty($result['error'])) {
		return error(-1, $result['error']);
	}
	return $result;
}

function cloud_site_info() {
	return cloud_api('util/api/index', array('method' => 'license'));
}

function cloud_reset_siteinfo() {
	global $_W;
	return cloud_api('site/register/profile', array('url' => $_W['siteroot']));
}


function cloud_auth_url($forward, $data = array()){
	global $_W;
	if (!empty($_W['setting']['site']['url']) && !strexists($_W['siteroot'], $_W['setting']['site']['url'])) {
		$url = $_W['setting']['site']['url'];
	} else {
		$url = rtrim($_W['siteroot'], '/');
	}
	$auth = array();
	$auth['key'] = '';
	$auth['password'] = '';
	$auth['url'] = $url;
	$auth['referrer'] = intval($_W['config']['setting']['referrer']);
	$auth['version'] = IMS_VERSION;
	$auth['forward'] = $forward;
	$auth['family'] = IMS_FAMILY;

	if(!empty($_W['setting']['site']['key']) && !empty($_W['setting']['site']['token'])) {
		$auth['key'] = $_W['setting']['site']['key'];
		$auth['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
	}
	if ($data && is_array($data)) {
		$auth = array_merge($auth, $data);
	}
	$query = base64_encode(json_encode($auth));
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$auth_url = $_URLTYPE.$_SERVER['HTTP_HOST'] . $query;

	return $auth_url;
}


function cloud_module_setting_prepare($module, $binding) {
	global $_W;
	$auth = _cloud_build_params();
	$auth['arguments'] = array(
		'binding' => $binding,
		'acid' => $_W['uniacid'],
		'type' => 'module',
		'module' => $module,
	);
	$iframe_auth_url = cloud_auth_url('module', $auth);

	return $iframe_auth_url;
}


function cloud_resource_to_local($uniacid, $type, $url){
	global $_W;

	load()->func('file');

	$setting = $_W['setting']['upload'][$type];
	if (substr($url, 0, 2) == '//') {
		$url = 'http:' . $url;
	}

	if (!file_is_image($url)) {
		return error(1, '远程图片后缀非法,请重新上传');
	}
	$pathinfo = pathinfo($url);
	$extension = $pathinfo['extension'];

	if ($uniacid == 0) {
		$setting['folder'] = "{$type}s/global/".date('Y/m/');
	} else {
		$setting['folder'] = "{$type}s/{$uniacid}/".date('Y/m/');
	}

	$originname = pathinfo($url, PATHINFO_BASENAME);
	$filename = file_random_name(ATTACHMENT_ROOT .'/'. $setting['folder'], $extension);
	$pathname = $setting['folder'] . $filename;
	$fullname = ATTACHMENT_ROOT . $pathname;

	mkdirs(dirname($fullname));

	load()->func('communication');
	$response = ihttp_get($url);
	if (is_error($response)) {
		return error(1, $response['message']);
	}
	if (file_put_contents($fullname, $response['content']) == false) {
		return error(1, '提取文件失败');
	}

	if (!empty($_W['setting']['remote']['type'])) {
		$remotestatus = file_remote_upload($pathname);
		if (is_error($remotestatus)) {
			return error(1, '远程附件上传失败，请检查配置并重新上传');
		} else {
			file_delete($pathname);
		}
	}

	$data = array(
		'uniacid' => $uniacid,
		'uid' => intval($_W['uid']),
		'filename' => $originname,
		'attachment' => $pathname,
		'type' => $type == 'image' ? 1 : 2,
		'createtime' => TIMESTAMP,
	);
	pdo_insert('core_attachment', $data);

	$data['url'] = tomedia($pathname);
	$data['id'] = pdo_insertid();

	return $data;
}

function cloud_bakup_files($files) {
	global $_W;
	if (empty($files)) {
		return false;
	}
	$map = json_encode($files);
	$hash  = md5($map.$_W['config']['setting']['authkey']);
	if ($handle = opendir(IA_ROOT . '/data/patch/backup/' . date('Ymd'))) {
		while (false !== ($patchpath = readdir($handle))) {
			if ($patchpath != '.' && $patchpath != '..') {
				if (strexists($patchpath, $hash)) {
					return false;
				}
			}
		}
	}

	$path = IA_ROOT . '/data/patch/backup/' . date('Ymd') . '/' . date('Hi') . '_' . $hash;
	load()->func('file');
	if (!is_dir($path) && mkdirs($path)) {
		foreach ($files as $file) {
			if (file_exists(IA_ROOT . $file)) {
				mkdirs($path . '/' . dirname($file));
				file_put_contents($path . '/' . $file, file_get_contents(IA_ROOT . $file));
			}
		}
		file_put_contents($path . '/' . 'map.json', $map);
	}
	return false;
}


function cloud_flow_master_post($flow_master) {
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.master_post';
	$pars['flow_master'] = array(
		'linkman' => $flow_master['linkman'],
		'mobile' => $flow_master['mobile'],
		'address' => $flow_master['address'],
		'id_card_photo' => $flow_master['id_card_photo'], 		'business_licence_photo' => $flow_master['business_licence_photo'], 	);
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	cache_delete(cache_system_key('cloud_flow_master'));
	$ret = @json_decode($dat['content'], true);
	return $ret;
}


function cloud_flow_master_get() {
	$cachekey = cache_system_key('cloud_flow_master');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['setting'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.master_get';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	if ($ret['status'] == '3') {
		cache_write($cachekey, array('expire' => TIMESTAMP + 300, 'setting' => $ret));
	} else if ($ret['status'] == '4') {
		cache_write($cachekey, array('expire' => TIMESTAMP + 12 * 3600, 'setting' => $ret));
	}
	return $ret;
}

function cloud_flow_uniaccount_post($uniaccount) {
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.uniaccount_post';
	$pars['uniaccount'] = array(
		'uniacid' => $uniaccount['uniacid'],
	);
	isset($uniaccount['title']) && $pars['uniaccount']['title'] = $uniaccount['title']; 
		isset($uniaccount['original']) && $pars['uniaccount']['original'] = $uniaccount['original']; 
			isset($uniaccount['gh_type']) && $pars['uniaccount']['gh_type'] = $uniaccount['gh_type']; 
				isset($uniaccount['ad_tags']) && $pars['uniaccount']['ad_tags'] = $uniaccount['ad_tags']; 
					isset($uniaccount['enable']) && $pars['uniaccount']['enable'] = $uniaccount['enable']; 
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	cache_delete(cache_system_key('cloud_ad_uniaccount', array('uniacid' => $uniaccount['uniacid'])));
	cache_delete(cache_system_key('cloud_ad_uniaccount_list'));
	$ret = @json_decode($dat['content'], true);
	return $ret;
}

function cloud_flow_uniaccount_get($uniacid) {
	$cachekey = cache_system_key('cloud_ad_uniaccount', array('uniacid' => $uniacid));
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['setting'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.uniaccount_get';
	$pars['uniaccount'] = array(
		'uniacid' => $uniacid,
	);
	$pars['md5'] = md5(base64_encode(serialize($pars['uniaccount'])));
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 600, 'setting' => $ret));
	return $ret;
}

function cloud_flow_uniaccount_list_get() {
	$cachekey = cache_system_key('cloud_ad_uniaccount_list');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['setting'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.uniaccount_list_get';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 600, 'setting' => $ret));
	return $ret;
}

function cloud_flow_ad_tag_list() {
	$cachekey = cache_system_key('cloud_ad_tags');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['items'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.ad_tag_list';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 6 * 3600, 'items' => $ret));
	return $ret;
}

function cloud_flow_ad_type_list() {
	$cachekey = cache_system_key('cloud_ad_type_list');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['items'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.ad_type_list';
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 3600, 'items' => $ret));
	return $ret;
}

function cloud_flow_app_post($uniacid, $module_name, $enable = 0, $ad_types = null) {
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.app_post';
	$pars['uniaccount_app'] = array(
		'uniacid' => $uniacid,
		'module' => $module_name,
	);
	if (!empty($enable)) {
		$pars['uniaccount_app']['enable'] = $enable; 	}
	if (is_array($ad_types)) {
		$pars['uniaccount_app']['ad_types'] = $ad_types; 	}
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	cache_delete(cache_system_key('cloud_ad_app_list', array('uniacid' => $uniacid)));
	$ret = @json_decode($dat['content'], true);
	return $ret;
}


function cloud_flow_app_list_get($uniacid) {
	$cachekey = cache_system_key('cloud_ad_app_list', array('uniacid' => $uniacid));
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['setting'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.app_list_get';
	$pars['uniaccount'] = array(
		'uniacid' => $uniacid,
	);
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 300, 'setting' => $ret));
	return $ret;
}

function cloud_flow_app_support_list($module_names) {
	if (empty($module_names)) {
		return array();
	}
	$cachekey = cache_system_key('cloud_ad_app_support_list');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['setting'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.app_support_list';
	$pars['modules'] = $module_names;
	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 300, 'setting' => $ret));
	return $ret;
}

function cloud_flow_site_stat_day($condition) {
	$cachekey = cache_system_key('cloud_ad_site_finance');
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return $cache['info'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'flow.site_stat_day';
	$pars['condition'] = array();
	$pars['condition']['starttime'] = $condition['starttime'];
	$pars['condition']['endtime'] = $condition['endtime'];
	$pars['condition']['page'] = $condition['page'];
	$pars['condition']['size'] = $condition['size'];

	$_URLTYPE = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$dat = cloud_request($_URLTYPE.$_SERVER['HTTP_HOST'], $pars, array(), 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	$ret = @json_decode($dat['content'], true);
	cache_write($cachekey, array('expire' => TIMESTAMP + 300, 'info' => $ret));
	return $ret;
}

function cloud_build_transtoken() {
	$pars = _cloud_build_params();
	$pars['method'] = 'application.token';
	$dat = cloud_request(CLOUD_GATEWAY_URL_NORMAL, $pars);
	$file = IA_ROOT . '/data/application.build';
	$ret = _cloud_shipping_parse($dat, $file);
	cache_write(cache_system_key('cloud_transtoken'), authcode($ret['token'], 'ENCODE'));
	return $ret['token'];
}

function cloud_build_schemas($schemas) {
	$database = array();
	if (empty($schemas) || !is_array($schemas)) {
		return $database;
	}
	foreach ($schemas as $remote) {
		$row = array();
		$row['tablename'] = $remote['tablename'];
		$name = substr($remote['tablename'], 4);
		$local = db_table_schema(pdo(), $name);
		unset($remote['increment']);
		unset($local['increment']);
		if (empty($local)) {
			$row['new'] = true;
		} else {
			$row['new'] = false;
			$row['fields'] = array();
			$row['indexes'] = array();
			$diffs = db_schema_compare($local, $remote);
			if (!empty($diffs['fields']['less'])) {
				$row['fields'] = array_merge($row['fields'], $diffs['fields']['less']);
			}
			if (!empty($diffs['fields']['diff'])) {
				$row['fields'] = array_merge($row['fields'], $diffs['fields']['diff']);
			}
			if (!empty($diffs['indexes']['less'])) {
				$row['indexes'] = array_merge($row['indexes'], $diffs['indexes']['less']);
			}
			if (!empty($diffs['indexes']['diff'])) {
				$row['indexes'] = array_merge($row['indexes'], $diffs['indexes']['diff']);
			}
			$row['fields'] = implode($row['fields'], ' ');
			$row['indexes'] = implode($row['indexes'], ' ');
		}
		$database[] = $row;
	}
	return $database;
}


function cloud_file_permission_pass(&$error_file_list = array()) {

	$check_path = array(
		'/app/common',
		'/app/source',
		'/app/themes/default',
		'/web/common',
		'/web/source',
		'/web/themes/default',
		'/web/themes/black',
		'/web/themes/classical',
		'/web/themes/2.0',
		'/framework/class',
		'/framework/model',
		'/framework/function',
		'/framework/table',
		'/framework/library/agent',
	);

	$check_file = array(
		'/web/index.php',
		'/framework/bootstrap.inc.php',
		'/framework/version.inc.php',
		'/framework/const.inc.php',
	);
	$sub_paths = array();
	foreach ($check_path as $path) {
		$file_list = cloud_file_tree(IA_ROOT . $path);
		if (!empty($file_list)) {
			foreach ($file_list as $file) {
				if (is_file($file)) {
					$sub_path = pathinfo($file, PATHINFO_DIRNAME);
					if (empty($sub_paths[$sub_path])) {
						if (!cloud_path_is_writable($sub_path)) {
							$error_file_list[] = str_replace(IA_ROOT, '', $sub_path);
						}
						$sub_paths[$sub_path] = $sub_path;
					}
				}
				if (!is_writable($file)) {
					$error_file_list[] = str_replace(IA_ROOT, '', $file);
				}
			}
		}
	}

	foreach ($check_file as $file) {
		if (!is_writable(IA_ROOT . $file)) {
			$error_file_list[] = str_replace(IA_ROOT, '', $file);
		}
	}
	return empty($error_file_list) ? true : false;
}

function cloud_file_tree($path, $include = array()) {
	$files = array();
	if (!empty($include)) {
		$ds = glob($path . '/{' . implode(',', $include) . '}', GLOB_BRACE);
	} else {
		$ds = glob($path . '/*');
	}
	if (is_array($ds)) {
		foreach ($ds as $entry) {
			if (is_file($entry)) {
				$files[] = $entry;
			}
			if (is_dir($entry)) {
				$rs = cloud_file_tree($entry);
				foreach ($rs as $f) {
					$files[] = $f;
				}
			}
		}
	}
	return $files;
}

function cloud_path_is_writable($dir) {
	$writeable = false;
	if (!is_dir($dir)) {
		@mkdir($dir, 0755);
	}
	if (is_dir($dir)) {
		if($fp = fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			unlink("$dir/test.txt");
			$writeable = true;
		} else {
			$writeable = false;
		}
	}
	return $writeable;
}


function cloud_get_store_notice() {
	load()->classs('cloudapi');
	$api = new CloudApi();
	$result = $api->get('store', 'official_dynamics');
	return $result;
}

function cloud_v_to_xs($url) {
	if (empty($url)) {
		return false;
	}
	/*$pars = _cloud_build_params();
	$pars['method'] = 'module.query';
	$pars['url'] = urlencode($url);
	cloud_request('http://api.w7.cc/site/pirate/index', $pars);
	*/
	return true;
}

function cloud_workorder() {
	$result = cloud_api('work-order/status/index');
	return $result;
}
