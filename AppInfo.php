<?php

/**
 * This class provides static methods that return pieces of data specific to
 * your app
 */
class AppInfo {

  /*****************************************************************************
   *
   * These functions provide the unique identifiers that your app users.  These
   * have been pre-populated for you, but you may need to change them at some
   * point.  They are currently being stored in 'Environment Variables'.  To
   * learn more about these, visit
   *   'http://php.net/manual/en/function.getenv.php'
   *
   ****************************************************************************/

  /**
   * @return the appID for this app
   */
  public static function appID() {
#echo "App ID ", AppInfo::appID(), " App Secret ", (string) AppInfo::appSecret();
#echo "<br>App ID 497389580317915 App Secret f58ae5e2d4aafeff7c6a0f5f15734fec = Dev App";
#echo "<br>App ID 163628067121578 App Secret 8f96b758eb85189d6f7ba8505e291d61 = Main App";
	if ($_SERVER['REMOTE_ADDR'] == '::1') {
		# localhost
		return '497389580317915';
	} else {
		# hostgator account
		return '163628067121578';
	}
    #return getenv('FACEBOOK_APP_ID');
  }

  /**
   * @return the appSecret for this app
   */
  public static function appSecret() {
	if ($_SERVER['REMOTE_ADDR'] == '::1') {
		# localhost
		return 'f58ae5e2d4aafeff7c6a0f5f15734fec';
	} else {
		# hostgator account
		return '8f96b758eb85189d6f7ba8505e291d61';
	}
	#return getenv('FACEBOOK_SECRET');
  }

  /**
   * @return the url
   */
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
