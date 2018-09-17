<?php

class CRM_RswIdCard_Utils_Random {

  /**
   * Generate a random string, using a cryptographically secure 
   * pseudorandom number generator (random_int)
   * 
   * For PHP 7, random_int is a PHP core function
   * For PHP 5.x, depends on https://github.com/paragonie/random_compat
   * (The random_compat package is in this extension's packages/random_compat
   * There is a require_once to it in rswidcard.php.)
   * 
   * @param int $length      How many characters do we want?
   * @param string $keyspace A string of all possible characters
   *                         to select from
   * @return string
   *
   * Taken from:
   * @link https://stackoverflow.com/a/34149536
   */
  public static function random_str(
      $length,
      $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
  ) {
      $str = '';
      $max = mb_strlen($keyspace, '8bit') - 1;
      if ($max < 1) {
          throw new Exception('$keyspace must be at least two characters long');
      }
      for ($i = 0; $i < $length; ++$i) {
          $str .= $keyspace[random_int(0, $max)];
      }
      return $str;
  }
}

?>
