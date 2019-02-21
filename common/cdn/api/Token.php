<?php
/**
 * @file
 * contains demo\util\Token
 * 获取Token类
 */

namespace demo\api;
require_once (COMMON.'/cdn/util/Encrypt.php');
require_once (COMMON.'/cdn/util/Request.php');
use \demo\util\Encrypt;
use demo\util\Request;

class Token {
  /**
   * @var string 用户名
   */
  const USERNAME = '66173';

  /**
   * @var string 密码
   */
  const PASSWORD = '66173qwe123';

  /**
   * @var string token
   */
  private static $token;

  /**
   * @var int token过期时间
   */
  private static $expire;

  /**
   * 返回token
   */
  public function token() {
      if (!empty(self::$token) && self::$expire > time()) {
          return self::$token;
      }
      $send_data = array(
          'username' => self::USERNAME,
          'password' => Encrypt::encrypt(self::PASSWORD, 'verycloud#cryptpass')
      );

      $url = Request::$api_url . '/API/OAuth/authorize';

      $return = Request::sendRequest($url, $send_data);
      if ($return['code'] == 1) {
          self::$token = $return['access_token'];
          self::$expire = $return['expires'];
          return $return['access_token'];
      }
      return '';
  }
}