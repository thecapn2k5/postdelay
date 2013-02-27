<?php
class AppInfo {
  public static function appID() {
	if ($_SERVER['REMOTE_ADDR'] == '::1') {
		return '497389580317915'; # localhost
	} else {
		return '163628067121578'; # hostgator account
	}
  }
  public static function appSecret() {
	if ($_SERVER['REMOTE_ADDR'] == '::1') {
		return 'f58ae5e2d4aafeff7c6a0f5f15734fec'; # localhost
	} else {
		return '8f96b758eb85189d6f7ba8505e291d61'; # hostgator account
	}
  }
  public static function getUrl($path = '/') {
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
      || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
    ) {
      $protocol = 'https://';
    }
    else {
      $protocol = 'http://';
    }
    return $protocol . $_SERVER['HTTP_HOST'] . $path;
  }
}