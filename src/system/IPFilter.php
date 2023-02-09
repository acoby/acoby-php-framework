<?php
declare(strict_types=1);

namespace acoby\system;

use Exception;

/**
 * A filter for IPv4 addresses. The filter contains a list of found solution how to work with IP addresses.
 *
 * @version 1.0
 * @author Thoralf Rickert-Wendt
 */
class IPFilter {
  private $_allowed_ips = array();

  /**
   * Creates a new Filter. The optional parameter allowed_ips must be a list of
   * strings containing the following syntax:
   * - one or more single IPv4/IPv6
   * - one or more network definitions in form ip/mask or ip/prefix
   * - one or more wildcards like 127.* - IPv6 not supported
   * - one or more sections like 10.0.0.0-10.0.1.0 - IPv6 not supported
   *
   * @param array $allowed_ips
   */
  public function __construct(array $allowed_ips = null) {
    if ($allowed_ips != null) {
      foreach($allowed_ips as $ip) {
        $ip = trim($ip);
        if (strpos($ip,'/')) {
          $cidr = $this->getNetwork($ip)."/".$this->getMask($ip);
          $this->_allowed_ips[] = $cidr;
        } else {
          $this->_allowed_ips[] = $ip;
        }
      }
    }
  }

  /**
   * Returns the first IP from the given allowed IPs in the constructor.
   * This is useful to start an iteration process.
   *
   * @return string The first well formed IP
   */
  public function first($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,'.')) {
        // ipv4
        if (strpos($allowed_ip, '/')) {
          return $this->increment($this->getIPv4Network($allowed_ip),0);
        } else if (strpos($allowed_ip, '*')) {
          return $this->increment($this->getIPv4Wildcard($allowed_ip),0);
        } else if (strpos($allowed_ip, '-')) {
          return $this->getIPv4Section($allowed_ip);
        }
      } else if (strpos($allowed_ip,':')) {
        // ipv6
        if (strpos($allowed_ip, '/')) {
          return $this->increment($this->getIPv6Network($allowed_ip),0);
        } else if (strpos($allowed_ip, '*')) {
          return $this->increment($this->getIPv6Wildcard($allowed_ip),0);
        } else if (strpos($allowed_ip, '-')) {
          return $this->getIPv6Section($allowed_ip);
        }
      }
    }
    return null;
  }

  /**
   *
   * @param string $cidr
   * @return string|NULL
   */
  public function getMask(string $cidr) :?string {
    if (strpos($cidr, '/')) {
      $cidr = explode('/', $cidr);
      return $cidr[1];
    }
    return null;
  }
  
  /**
   * 
   * @param string $net
   * @return string|NULL
   */
  public function getNetmask(string $net) :?string {
    if (strpos($net, '/')) {
      $cidr = explode('/', $net);
      $int = intval($cidr[1]);
      return long2ip(-1 << (32 - (int)$int));
    }
    return null;
  }

  /**
   * Returns a CIDR number for a netmask of a.b.c.d
   * @return number
   */
  public function getCIDR(string $mask) :string {
    if ($mask == "0.0.0.0") return "0";
    $long = ip2long($mask);
    $base = ip2long('255.255.255.255');
    return "".(32-log(($long ^ $base)+1,2));
  }

  /**
   * Returns the network part of an IP address
   *
   * @param string $ip
   * @return string
   */
  public function getNetwork(string $ip) :?string {
    if (strpos($ip,'.')) {
      return $this->getIPv4Network($ip);
    } else if (strpos($ip,':')) {
      return $this->getIPv6Network($ip);
    }
    return null;
  }

  /**
   * Returns the network part of an IP address
   *
   * @param string $cidr
   * @return string
   */
  public function getIPv4Network(string $cidr, bool $full = false) :?string {
    if (strpos($cidr, '/')) {
      $cidr = explode('/', $cidr);

      if (strlen($cidr[1])>3) {
        $netmask = IPFilter::getCIDR($cidr[1]);
      } else {
        $netmask = $cidr[1];
      }

      $net = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$netmask))));
      if ($full === true) {
        $net.= "/".$netmask;
      }
      return $net;
    }
    return null;
  }

  /**
   * Returns the network part of an IP address
   * @param string $ip
   * @return string
   */
  public static function getIPv6Network(string $cidr, bool $full = false) :?string {
    if (strpos($cidr, '/')) {
      list($ip, $mask) = explode('/', $cidr);

      $ip = IPFilter::dtr_pton($ip);

      $hosts = (128 - $mask);
      $networks = 128 - $hosts;

      $_m = str_repeat("1", $networks).str_repeat("0", $hosts);

      $_hexMask = null;
      foreach( str_split( $_m, 4) as $segment){
        $_hexMask .= base_convert( $segment, 2, 16);
      }

      $mask1 = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $_hexMask), 0, -1);

      $mask1 = IPFilter::dtr_pton($mask1);

      if ($full) {
        return IPFilter::dtr_ntop( $ip & $mask1)."/".$mask;
      } else {
        return IPFilter::dtr_ntop( $ip & $mask1);
      }
    }
    return null;
  }

  /**
   * Returns the IPv6 network cidr of an address of type <ip>/<prefix>
   */
  public static function getIPv6NetworkCIDR(string $address) {
    list($ip, $mask) = explode('/', $address);
    return IPFilter::getIPv6Network($ip.'/'.$mask).'/'.$mask;
  }

  /**
   * Returns the the first IP of a IPv4 wildcard address
   *
   * @param string $ip Something like 10.0.*
   * @return string a String with 10.0.0.0
   */
  public function getIPv4Wildcard(string $ip) :?string {
    if (strpos($ip, '*')) {
      // 10.0.*
      $ip_arr = explode('.', $ip);
      $first = "";
      for($i = 0;$i < count($ip_arr);$i++) {
        if ($ip_arr[$i] == '*') {
          if ($i < 4) {
            $first = $first.str_repeat("0.", 4-$i);
            $first = substr($first,0,strlen($first)-1);
          }
          break;
        } else {
          $first .= $ip_arr[$i].".";
        }
      }
      return $first;
    }
    return null;
  }


  /**
   * Returns the the first IP of a IPv6 wildcard address
   *
   * @param string $ip Something like fd00:1:*
   * @return string a String with fd00:1::
   */
  public function getIPv6Wildcard(string $ip) :?string {
    if (strpos($ip, '*')) {
      // 10.0.*
      $ip_arr = explode(':', $ip);
      $first = "";
      for($i = 0;$i < count($ip_arr);$i++) {
        if ($ip_arr[$i] == '*') {
          $first .= ":";
          break;
        } else {
          $first .= $ip_arr[$i].":";
        }
      }
      return $first;
    }
    return null;
  }

  /**
   * Returns the the first IP of a IPv6 wildcard address
   *
   * @param string $ip Something like 10.0.0.0-10.0.0.255
   * @return string a String with 10.0.0.0
   */
  public function getIPv4Section(string $ip) :?string {
    if (strpos($ip, '-')) {
      $ip_arr = explode('-', $ip);
      return $this->increment($ip_arr[0],0);
    }
    return null;
  }

  /**
   * Returns the the first IP of a IPv6 wildcard address
   *
   * @param string $ip Something like fd00:1::0-fd00:1::ffff
   * @return string a String with fd00:1::0
   */
  public function getIPv6Section(string $ip) :?string {
    if (strpos($ip, '-')) {
      $ip_arr = explode('-', $ip);
      return $this->increment($ip_arr[0],0);
    }
    return null;
  }

  /**
   * Returns the first IPv4 from the given allowed IPs in the constructor.
   * This is useful to start an iteration process.
   *
   * @return string The first well formed IP
   */
  public function firstIPv4($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,'.')) {
        if (strpos($allowed_ip, '/')) {
          // ip/mask
          return $this->increment($this->getIPv4Network($allowed_ip),0);
        } else if (strpos($allowed_ip, '*')) {
          // 10.0.*
          return $this->increment($this->getIPv4Wildcard($allowed_ip),0);
        } else if (strpos($allowed_ip, '-')) {
          // 10.0.0.0-10.0.0.255
          return $this->getIPv4Section($allowed_ip);
        }
      }
    }
    return null;
  }


  /**
   * Returns the first IPv6 from the given allowed IPs in the constructor.
   * This is useful to start an iteration process.
   *
   * @return string The first well formed IP
   */
  public function firstIPv6($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,':')) {
        if (strpos($allowed_ip, '/')) {
          // ip/mask
          return $this->increment($this->getIPv6Network($allowed_ip),0);
        } else if (strpos($allowed_ip, '*')) {
          // fd00:1:*
          return $this->increment($this->getIPv6Wildcard($allowed_ip),0);
        } else if (strpos($allowed_ip, '-')) {
          // fd00:1::0-fd00:1::ffff
          return $this->getIPv6Section($allowed_ip);
        }
      }
    }
    return null;
  }

  /**
   * Returns the last IP from the given allowed IPs in the constructor.
   * This is useful to end an iteration process.
   *
   * @return string The last well formed IP
   */
  public function last($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,'.')) {
        // ipv4
        return $this->lastIPv4($allowed_ip);
      } else if (strpos($allowed_ip,':')) {
        // ipv6
        return $this->lastIPv6($allowed_ip);
      }
    }
    return null;
  }


  /**
   * Returns the last IPv4 from the given allowed IPs in the constructor.
   * This is useful to end an iteration process.
   *
   * @return string The last well formed IP
   */
  public function lastIPv4($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,'.')) {
        if (strpos($allowed_ip, '/')) {
          return $this->lastIPv4Mask($allowed_ip);
        } else if (strpos($allowed_ip, '*')) {
          return $this->lastIPv4Wildcard($allowed_ip);
        } else if (strpos($allowed_ip, '-')) {
          return $this->lastIPv4Section($allowed_ip);
        }
      }
    }
    return null;
  }


  /**
   * Returns the last IPv4 from the given allowed IPs in the constructor.
   * This is useful to end an iteration process.
   *
   * @return string The last well formed IP
   */
  public function lastIPv6($ip = null) :?string {
    $array = $this->_allowed_ips;
    if (isset($ip)) {
      $array = array($ip);
    }
    foreach($array as $allowed_ip) {
      if (strpos($allowed_ip,':')) {
        if (strpos($allowed_ip, '/')) {
          return $this->lastIPv6Mask($allowed_ip);
        } else if (strpos($allowed_ip, '*')) {
          return $this->lastIPv6Wildcard($allowed_ip);
        } else if (strpos($allowed_ip, '-')) {
          return $this->lastIPv6Section($allowed_ip);
        }
      }
    }
    return null;
  }

  /*
   * Returns last ip of 10.0.0.0/24
   *
   * @param string $cidr
   * @return string|NULL
   */
  private function lastIPv4Mask(string $cidr) :?string {
    // ip/mask
    list($ip, $mask) = explode('/', $cidr);

    $maskBinStr = str_repeat("1", intval($mask)).str_repeat("0", 32-intval($mask));
    $inverseMaskBinStr = str_repeat("0", intval($mask)).str_repeat("1",  32-intval($mask));

    $ipLong = ip2long($ip);
    $ipMaskLong = bindec($maskBinStr);
    $inverseIpMaskLong = bindec($inverseMaskBinStr);
    $netWork = $ipLong & $ipMaskLong;

    // $start = $netWork;
    return long2ip($netWork | $inverseIpMaskLong);
  }

  /*
   * Returns last ip of 10.0.*
   *
   * @param string $ip
   * @return string|NULL
   */
  private function lastIPv4Wildcard(string $ip) :?string {
    // 10.0.*
    $ip_arr = explode('.', $ip);
    $first = "";
    for($i = 0;$i < count($ip_arr);$i++) {
      if ($ip_arr[$i] == '*') {
        if ($i < 4) {
          $first = $first.str_repeat("255.", 4-$i);
          $first = substr($first,0,strlen($first)-1);
        }
        break;
      } else {
        $first .= $ip_arr[$i].".";
      }
    }
    return $first;
  }


  /*
   * Returns last ip of 10.0.0.0-10.0.0.255
   *
   * @param string $ip
   * @return string|NULL
   */
  private function lastIPv4Section(string $ip) :?string {
    $ip_arr = explode('-', $ip);
    return $this->increment($ip_arr[1],0);
  }

  /*
   * Returns last ip of fd00:1::/64
   *
   * @param string $cidr
   * @return string|NULL
   */
  private function lastIPv6Mask(string $cidr) :?string {
    // ip/mask
    list($ip, $mask) = explode('/', $cidr);
    $firstaddrbin = inet_pton($ip);
    $lastaddrhex = bin2hex($firstaddrbin);
    $flexbits = 128 - $mask;

    $pos = 31;
    while ($flexbits > 0) {
      $orig = substr($lastaddrhex, $pos, 1);
      $origval = hexdec($orig);
      $newval = $origval | (pow(2, min(4, $flexbits)) - 1);
      $new = dechex($newval);
      $lastaddrhex = substr_replace($lastaddrhex, $new, $pos, 1);
      $flexbits -= 4;
      $pos -= 1;
    }

    $lastaddrbin = hex2bin($lastaddrhex);

    $lastaddrstr = inet_ntop($lastaddrbin);

    return $lastaddrstr;
  }

  /*
   * Returns last ip of fd00:1:*
   *
   * @param string $ip
   * @return string|NULL
   */
  private function lastIPv6Wildcard(string $ip) :?string {
    throw new Exception('Comparing an IP with a wildcard IPv6 is currently not supported');
  }

  /*
   * Returns last ip of fd00:1::0-fd00:1::ffff
   *
   * @param string $cidr
   * @return string|NULL
   */
  private function lastIPv6Section(string $ip) :?string {
    $ip_arr = explode('-', $ip);
    return $this->increment($ip_arr[1],0);
  }
  /**
   * Checks, if the given IP is in IP range. Be aware, that comparing IPv6 with IPv4 always return false.
   *
   * @param string $ip an IPv4/IPv6 address
   * @return bool true, if it is in allowed IPs range
   */
  public function inrange(string $ip, string $range = null) :bool {
    if ($this->isIP($ip)) {
      $allowed_ips = $this->_allowed_ips;
      if ($range !== null) $allowed_ips = array($range);

      foreach($allowed_ips as $allowed_ip) {
        if ((strpos($allowed_ip, ':') && strpos($ip,':')) || (strpos($allowed_ip, '.') && strpos($ip,'.'))) {
          if (strpos($allowed_ip, '*')) {
            return $this->_sub_checker_wildcard($allowed_ip, $ip);
          } else if (strpos($allowed_ip, '/')) {
            return $this->_sub_checker_mask($allowed_ip, $ip);
          } else if (strpos($allowed_ip, '-')) {
            return $this->_sub_checker_section($allowed_ip, $ip);
          } else if ($this->isIP($allowed_ip)) {
            return $this->_sub_checker_single($allowed_ip, $ip);
          }
        }
      }
    }
    return false;
  }

  /**
   * Returns if the given string is a valid IPv4/IPv6 address.
   *
   * @param string $ip
   * @return bool
   */
  public function isIP(string $ip) :bool {
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) return false;
    return true;
  }

 /**
  * Returns if the given string is a valid IPv4 address.
  *
  * @param string $ip
  * @return bool
  */
  public function isIPv4(string $ip, bool $withCIDR = false) :bool {
    if (strpos($ip, '/')) {
      list($ip, $mask) = explode('/', $ip);
      $mask = intval($mask);
      if ($mask < 0 || $mask > 32) return false;
      return $this->isIPv4($ip);
    } else {
      if ($withCIDR) return false;
      if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return true;
    }
    
    return false;
  }
  
  /**
  * Returns if the given string is a valid IPv6 address.
   *
   * @param string $ip
   * @return bool
   */
  public function isIPv6(string $ip, bool $withCIDR = false) :bool {
    if (strpos($ip, '/')) {
      list($ip, $mask) = explode('/', $ip);
      $mask = intval($mask);
      if ($mask < 0 || $mask > 128) return false;
      return $this->isIPv6($ip);
    } else {
      if ($withCIDR) return false;
      if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return true;
    }
    return false;
  }

  /*
   * converts inet_pton output to string with bits
   */
  private function _inet_to_bits($inet) :string {
    $unpacked = unpack('A16', $inet);
    $unpacked = str_split($unpacked[1]);
    $binaryip = '';
    foreach ($unpacked as $char) {
      $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }
    // because for example fd00:1:: should be returned completely with 128 bits.
    while (strlen($binaryip) < 128) {
      $binaryip .= "0";
    }
    return $binaryip;
  }

  /*
   * Compares two IPs. They must match each other.
   *
   * @param string $allowed_ip
   * @param string $ip
   * @return bool
   */
  private function _sub_checker_single(string $allowed_ip, string $ip) :bool {
    if ($this->isIPv4($ip) && strpos($allowed_ip,'.')) {
      return (ip2long($allowed_ip) == ip2long($ip));
    } else if ($this->isIPv6($ip) && strpos($allowed_ip,':')) {
      $packed_ip = inet_pton($ip);
      $binaryip = $this->_inet_to_bits($packed_ip);

      $packed_net = inet_pton($allowed_ip);
      $binarynet = $this->_inet_to_bits($packed_net);

      return ($binaryip == $binarynet);
    } else {
      return false;
    }
  }

  /*
   * Compares two IPs. They must match each other.
   *
   * @param string $allowed_ip
   * @param string $ip
   * @return bool
   */
  private function _sub_checker_wildcard(string $allowed_ip, string $ip) :bool {
    if ($this->isIPv4($ip) && strpos($allowed_ip,'.')) {
      $allowed_ip_arr = explode('.', $allowed_ip);
      $ip_arr = explode('.', $ip);
      for($i = 0;$i < count($allowed_ip_arr);$i++) {
        if ($allowed_ip_arr[$i] == '*') {
          return true;
        } else {
          if (false == ($allowed_ip_arr[$i] == $ip_arr[$i])) {
            return false;
          }
        }
      }
    } else if ($this->isIPv6($ip) && strpos($allowed_ip,':')) {
      throw new Exception('Comparing an IP with a wildcard IPv6 is currently not supported');
    }

    return false;
  }

  /*
   * Compares two IPs. They must match each other.
   *
   * @param string $allowed_ip
   * @param string $ip
   * @return bool
   */
  private function _sub_checker_mask(string $allowed_ip, string $ip) :bool {
    if ($this->isIPv4($ip) && strpos($allowed_ip,'.')) {
      list ($net, $mask) = explode ("/", $allowed_ip);
      $ip_net = ip2long ($net);
      $ip_mask = ~((1 << (32 - $mask)) - 1);
      $ip_ip = ip2long ($ip);
      $ip_ip_net = $ip_ip & $ip_mask;
      return ($ip_ip_net == $ip_net);
    } else if ($this->isIPv6($ip) && strpos($allowed_ip,':')) {
      $packed_ip = inet_pton($ip);
      $binaryip = $this->_inet_to_bits($packed_ip);

      list($net,$maskbits)=explode('/',$allowed_ip);
      $net = inet_pton($net);
      $binarynet = $this->_inet_to_bits($net);
      $maskbits = intval($maskbits);

      $ip_net_bits=substr($binaryip,0,$maskbits);
      $net_bits   =substr($binarynet,0,$maskbits);

      return !($ip_net_bits!==$net_bits);
    } else {
      return false;
    }
  }

  /*
   * Compares two IPs. They must match each other.
   *
   * @param string $allowed_ip
   * @param string $ip
   * @return bool
   */
  private function _sub_checker_section(string $allowed_ip, string $ip) :bool {
    if ($this->isIPv4($ip) && strpos($allowed_ip,'.')) {
      list($begin, $end) = explode('-', $allowed_ip);
      $begin = ip2long($begin);
      $end = ip2long($end);
      $ip = ip2long($ip);
      return ($ip >= $begin && $ip <= $end);
    } else if ($this->isIPv6($ip) && strpos($allowed_ip,':')) {
      throw new Exception('Comparing an IP with a section IPv6 is currently not supported');
    } else {
      return false;
    }
  }

  /**
   * Takes an IP address and adds the given increment value to it and returns the newly incremented address.
   *
   * @param string $ip
   * @param int $increment
   * @return string|NULL
   */
  public function increment(string $ip, int $increment) :?string {
    if (strpos($ip, '/')>0) $ip = $this->first($ip);
    if ($this->isIPv4($ip)) {
      return $this->ipv4_increment($ip, $increment);
    } else if ($this->isIPv6($ip)) {
      return $this->ipv6_increment($ip, $increment);
    } else {
      return null;
    }
  }

  /**
   * Increments an IPv4 address
   *
   * @param string $ip
   * @param int $increment
   * @return string
   */
  public function ipv4_increment(string $ip, int $increment) :string {
    $long = ip2long($ip);

    $long = $long + $increment;

    return long2ip($long);
  }

  /**
   * Increments an IPv6 address
   *
   * @param string $ip
   * @param int $increment
   * @return string
   */
  public function ipv6_increment(string $ip, int $increment) :string {
    $addr = inet_pton ( $ip );

    for ( $i = strlen ( $addr ) - 1; $increment > 0 && $i >= 0; --$i ) {
      $val = ord($addr[$i]) + $increment;
      $increment = $val / 256;
      $addr[$i] = chr($val % 256);
    }

    $newIp = inet_ntop ( $addr );
    if (substr($newIp, -1) === ":") {
      return $newIp . "0";
    } else {
      return $newIp;
    }
  }

  /**
   * Expands an IPv6 address to a complete string
   *
   * @param string $ip for example fd00:1::
   * @return string returns an expanded IPv6 address like fd00:0001:0000:0000:0000:0000:0000:0000
   */
  public function expandIPv6(string $ip) :string {
    $hex = unpack("H*hex", inet_pton($ip));
    $ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
    return $ip;
  }

  /**
   * dtr_pton
   *
   * Converts a printable IP into an unpacked binary string
   *
   * @author Mike Mackintosh - mike@bakeryphp.com
   * @param string $ip
   * @return string $bin
   */
  public static function dtr_pton(string $ip) :?string {
    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
      return current( unpack( "a4", inet_pton( $ip ) ) );
    } elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
      return current( unpack( "a16", inet_pton( $ip ) ) );
    }

    throw new Exception("Please supply a valid IPv4 or IPv6 address");

    return false;
  }

  /**
   * dtr_ntop
   *
   * Converts an unpacked binary string into a printable IP
   *
   * @author Mike Mackintosh - mike@bakeryphp.com
   * @param string $str
   * @return string $ip
   */
  public static function dtr_ntop(string $str) :?string {
    if (strlen( $str ) == 16 OR strlen( $str ) == 4 ){
      return inet_ntop( pack( "a".strlen( $str ) , $str ) );
    }

    throw new Exception( "Please provide a 4 or 16 byte string" );

    return false;
  }

  /**
   * Checks, if an array of IPs contains a specific IP. Does not make IP calculation.
   *
   * @param array $iplist
   * @param string $ip
   * @return bool
   */
  public function inList(array $iplist, string $ip) :bool {
    $ip = $this->format($ip,true);
    foreach ($iplist as $anip) {
      $anip = $this->format($anip,true);
      if ($anip == $ip) return true;
    }
    return false;
  }

  /**
   * Reformats an IP address to fit all needs
   * @param string $ip
   * @param bool $cidr
   * @return string|NULL
   */
  public function format(string $ip, bool $cidr = false) :?string {
    if (strpos($ip,'.')) {
      $ip = long2ip(ip2long($ip));
      if ($cidr) $ip .= "/32";
      return $ip;
    } else if (strpos($ip,':')) {
      $ip = IPFilter::dtr_ntop(IPFilter::dtr_pton($ip));
      if ($cidr) $ip .= "/128";
      return $ip;
    }
    return null;
  }

  /**
   *
   * @param string $base
   * @param array $index
   * @param string $hostname
   * @param string $mask
   * @return string|NULL
   */
  public function calc(string $base, array $index, string $hostname, string $mask = null) :?string {
    $increment = array_search($hostname,$index);
    if ($increment === FALSE) return null;
    if (strpos($base,'/')>0) {
      $net = $this->first($base);
      $ip = $this->increment($net,$increment);
      if (!$this->inrange($ip,$base)) {
        return null;
      }
    } else {
      $ip = $this->increment($base,$increment);
    }
    if ($mask !== null) $ip .= "/".$mask;
    return $ip;
  }
}

