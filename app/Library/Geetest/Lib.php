<?php
namespace App\Library\Geetest; class Lib { const GT_SDK_VERSION = 'php_3.2.0'; public static $connectTimeout = 1; public static $socketTimeout = 1; private $response; public $captcha_id; public $private_key; public function __construct($sp46e0a9, $spff9dbe) { $this->captcha_id = $sp46e0a9; $this->private_key = $spff9dbe; } public function pre_process($spacf00d = null) { $sp069c22 = 'http://api.geetest.com/register.php?gt=' . $this->captcha_id; if ($spacf00d != null and is_string($spacf00d)) { $sp069c22 = $sp069c22 . '&user_id=' . $spacf00d; } $spbb0d1b = $this->send_request($sp069c22); if (strlen($spbb0d1b) != 32) { $this->failback_process(); return 0; } $this->success_process($spbb0d1b); return 1; } private function success_process($spbb0d1b) { $spbb0d1b = md5($spbb0d1b . $this->private_key); $sp1b1403 = array('success' => 1, 'gt' => $this->captcha_id, 'challenge' => $spbb0d1b); $this->response = $sp1b1403; } private function failback_process() { $spfc24e0 = md5(rand(0, 100)); $spf994ae = md5(rand(0, 100)); $spbb0d1b = $spfc24e0 . substr($spf994ae, 0, 2); $sp1b1403 = array('success' => 0, 'gt' => $this->captcha_id, 'challenge' => $spbb0d1b); $this->response = $sp1b1403; } public function get_response_str() { return json_encode($this->response); } public function get_response() { return $this->response; } public function success_validate($spbb0d1b, $sp8538ba, $spcfa884, $spacf00d = null) { if (!$this->check_validate($spbb0d1b, $sp8538ba)) { return 0; } $sp5b8b32 = array('seccode' => $spcfa884, 'sdk' => self::GT_SDK_VERSION); if ($spacf00d != null and is_string($spacf00d)) { $sp5b8b32['user_id'] = $spacf00d; } $sp069c22 = 'http://api.geetest.com/validate.php'; $sp37bd7f = $this->post_request($sp069c22, $sp5b8b32); if ($sp37bd7f == md5($spcfa884)) { return 1; } else { if ($sp37bd7f == 'false') { return 0; } else { return 0; } } } public function fail_validate($spbb0d1b, $sp8538ba, $spcfa884) { if ($sp8538ba) { $spbf479d = explode('_', $sp8538ba); try { $spfec5a0 = $this->decode_response($spbb0d1b, $spbf479d['0']); $sp5cf10d = $this->decode_response($spbb0d1b, $spbf479d['1']); $sp64cb37 = $this->decode_response($spbb0d1b, $spbf479d['2']); $sp0b6745 = $this->get_failback_pic_ans($sp5cf10d, $sp64cb37); $sp864c8c = abs($spfec5a0 - $sp0b6745); } catch (\Exception $sp0b065e) { return 1; } if ($sp864c8c < 4) { return 1; } else { return 0; } } else { return 0; } } private function check_validate($spbb0d1b, $sp8538ba) { if (strlen($sp8538ba) != 32) { return false; } if (md5($this->private_key . 'geetest' . $spbb0d1b) != $sp8538ba) { return false; } return true; } private function send_request($sp069c22) { if (function_exists('curl_exec')) { $sp2df40c = curl_init(); curl_setopt($sp2df40c, CURLOPT_URL, $sp069c22); curl_setopt($sp2df40c, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout); curl_setopt($sp2df40c, CURLOPT_TIMEOUT, self::$socketTimeout); curl_setopt($sp2df40c, CURLOPT_RETURNTRANSFER, 1); $sp5b8b32 = curl_exec($sp2df40c); if (curl_errno($sp2df40c)) { $sp0e7cbe = sprintf('curl[%s] error[%s]', $sp069c22, curl_errno($sp2df40c) . ':' . curl_error($sp2df40c)); $this->triggerError($sp0e7cbe); } curl_close($sp2df40c); } else { $spaf3b4e = array('http' => array('method' => 'GET', 'timeout' => self::$connectTimeout + self::$socketTimeout)); $sp72a26f = stream_context_create($spaf3b4e); $sp5b8b32 = file_get_contents($sp069c22, false, $sp72a26f); } return $sp5b8b32; } private function post_request($sp069c22, $sp28cfaa = '') { if (!$sp28cfaa) { return false; } $sp5b8b32 = http_build_query($sp28cfaa); if (function_exists('curl_exec')) { $sp2df40c = curl_init(); curl_setopt($sp2df40c, CURLOPT_URL, $sp069c22); curl_setopt($sp2df40c, CURLOPT_RETURNTRANSFER, 1); curl_setopt($sp2df40c, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout); curl_setopt($sp2df40c, CURLOPT_TIMEOUT, self::$socketTimeout); if (!$sp28cfaa) { curl_setopt($sp2df40c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); } else { curl_setopt($sp2df40c, CURLOPT_POST, 1); curl_setopt($sp2df40c, CURLOPT_POSTFIELDS, $sp5b8b32); } $sp5b8b32 = curl_exec($sp2df40c); if (curl_errno($sp2df40c)) { $sp0e7cbe = sprintf('curl[%s] error[%s]', $sp069c22, curl_errno($sp2df40c) . ':' . curl_error($sp2df40c)); $this->triggerError($sp0e7cbe); } curl_close($sp2df40c); } else { if ($sp28cfaa) { $spaf3b4e = array('http' => array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded
' . 'Content-Length: ' . strlen($sp5b8b32) . '
', 'content' => $sp5b8b32, 'timeout' => self::$connectTimeout + self::$socketTimeout)); $sp72a26f = stream_context_create($spaf3b4e); $sp5b8b32 = file_get_contents($sp069c22, false, $sp72a26f); } } return $sp5b8b32; } private function decode_response($spbb0d1b, $sp259b3f) { if (strlen($sp259b3f) > 100) { return 0; } $sp6bc830 = array(); $sp03f84c = array(); $sp10efc9 = array('0' => 1, '1' => 2, '2' => 5, '3' => 10, '4' => 50); $sp7aa4d7 = 0; $sp50f1e3 = 0; $sp744d27 = str_split($spbb0d1b); $spa084fa = str_split($sp259b3f); for ($sp1148f5 = 0; $sp1148f5 < strlen($spbb0d1b); $sp1148f5++) { $sp4ae387 = $sp744d27[$sp1148f5]; if (in_array($sp4ae387, $sp03f84c)) { continue; } else { $spbf479d = $sp10efc9[$sp7aa4d7 % 5]; array_push($sp03f84c, $sp4ae387); $sp7aa4d7++; $sp6bc830[$sp4ae387] = $spbf479d; } } for ($sp3ae197 = 0; $sp3ae197 < strlen($sp259b3f); $sp3ae197++) { $sp50f1e3 += $sp6bc830[$spa084fa[$sp3ae197]]; } $sp50f1e3 = $sp50f1e3 - $this->decodeRandBase($spbb0d1b); return $sp50f1e3; } private function get_x_pos_from_str($sp889b76) { if (strlen($sp889b76) != 5) { return 0; } $sp9fda5d = 0; $sp09111b = 200; $sp9fda5d = base_convert($sp889b76, 16, 10); $sp1b1403 = $sp9fda5d % $sp09111b; $sp1b1403 = $sp1b1403 < 40 ? 40 : $sp1b1403; return $sp1b1403; } private function get_failback_pic_ans($spc2d31f, $spcd682b) { $spa209ee = substr(md5($spc2d31f), 0, 9); $sp1364c3 = substr(md5($spcd682b), 10, 9); $spff7429 = ''; for ($sp1148f5 = 0; $sp1148f5 < 9; $sp1148f5++) { if ($sp1148f5 % 2 == 0) { $spff7429 = $spff7429 . $spa209ee[$sp1148f5]; } elseif ($sp1148f5 % 2 == 1) { $spff7429 = $spff7429 . $sp1364c3[$sp1148f5]; } } $spf5b819 = substr($spff7429, 4, 5); $sp0b6745 = $this->get_x_pos_from_str($spf5b819); return $sp0b6745; } private function decodeRandBase($spbb0d1b) { $sped5a6d = substr($spbb0d1b, 32, 2); $sp0851d5 = array(); for ($sp1148f5 = 0; $sp1148f5 < strlen($sped5a6d); $sp1148f5++) { $sp5e8138 = ord($sped5a6d[$sp1148f5]); $sp1b1403 = $sp5e8138 > 57 ? $sp5e8138 - 87 : $sp5e8138 - 48; array_push($sp0851d5, $sp1b1403); } $spe65e64 = $sp0851d5['0'] * 36 + $sp0851d5['1']; return $spe65e64; } private function triggerError($sp0e7cbe) { } }