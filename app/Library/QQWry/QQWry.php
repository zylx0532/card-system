<?php
namespace App\Library\QQWry; class QQWry { private $fp; private $firstIP; private $lastIP; private $totalIP; public function __construct($sp75f256 = false) { if ($sp75f256 === false) { $sp75f256 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'qqwry.dat'; } $this->fp = 0; if (($this->fp = @fopen($sp75f256, 'rb')) !== false) { $this->firstIP = $this->getLong(); $this->lastIP = $this->getLong(); $this->totalIP = ($this->lastIP - $this->firstIP) / 7; register_shutdown_function(array(&$this, '__destruct')); } } public function __destruct() { if ($this->fp) { fclose($this->fp); } $this->fp = 0; } private function getLong() { $sp33dd24 = unpack('Vlong', fread($this->fp, 4)); return $sp33dd24['long']; } private function _getLong3() { $sp33dd24 = unpack('Vlong', fread($this->fp, 3) . chr(0)); return $sp33dd24['long']; } private function _packIP($sp77e6c7) { return pack('N', intval(ip2long($sp77e6c7))); } private function _getString($sp3a86ba = '') { $spb6cbbf = fread($this->fp, 1); while (ord($spb6cbbf) > 0) { $sp3a86ba .= $spb6cbbf; $spb6cbbf = fread($this->fp, 1); } return $sp3a86ba; } private function _getArea() { $sp6ac168 = fread($this->fp, 1); switch (ord($sp6ac168)) { case 0: $spd251e5 = ''; break; case 1: case 2: fseek($this->fp, $this->_getLong3()); $spd251e5 = $this->_getString(); break; default: $spd251e5 = $this->_getString($sp6ac168); break; } return $spd251e5; } public function getLocation($sp77e6c7) { if (!$this->fp) { return '请下载qqwry.dat放在app/Library/QQWry目录下'; } $sp1d66e2['ip'] = gethostbyname($sp77e6c7); $sp77e6c7 = $this->_packIP($sp1d66e2['ip']); $spec109b = 0; $sp1ab951 = $this->totalIP; $spceba59 = $this->lastIP; while ($spec109b <= $sp1ab951) { $sp59ca22 = floor(($spec109b + $sp1ab951) / 2); fseek($this->fp, $this->firstIP + $sp59ca22 * 7); $sp58e45b = strrev(fread($this->fp, 4)); if ($sp77e6c7 < $sp58e45b) { $sp1ab951 = $sp59ca22 - 1; } else { fseek($this->fp, $this->_getLong3()); $sp1e8ab0 = strrev(fread($this->fp, 4)); if ($sp77e6c7 > $sp1e8ab0) { $spec109b = $sp59ca22 + 1; } else { $spceba59 = $this->firstIP + $sp59ca22 * 7; break; } } } fseek($this->fp, $spceba59); $sp1d66e2['beginip'] = long2ip($this->getLong()); $sp1bf814 = $this->_getLong3(); fseek($this->fp, $sp1bf814); $sp1d66e2['endip'] = long2ip($this->getLong()); $sp6ac168 = fread($this->fp, 1); switch (ord($sp6ac168)) { case 1: $sp8cf35f = $this->_getLong3(); fseek($this->fp, $sp8cf35f); $sp6ac168 = fread($this->fp, 1); switch (ord($sp6ac168)) { case 2: fseek($this->fp, $this->_getLong3()); $sp1d66e2['country'] = $this->_getString(); fseek($this->fp, $sp8cf35f + 4); $sp1d66e2['area'] = $this->_getArea(); break; default: $sp1d66e2['country'] = $this->_getString($sp6ac168); $sp1d66e2['area'] = $this->_getArea(); break; } break; case 2: fseek($this->fp, $this->_getLong3()); $sp1d66e2['country'] = $this->_getString(); fseek($this->fp, $sp1bf814 + 8); $sp1d66e2['area'] = $this->_getArea(); break; default: $sp1d66e2['country'] = $this->_getString($sp6ac168); $sp1d66e2['area'] = $this->_getArea(); break; } if ($sp1d66e2['country'] == ' CZ88.NET') { $sp1d66e2['country'] = '未知'; } if ($sp1d66e2['area'] == ' CZ88.NET') { $sp1d66e2['area'] = ''; } return iconv('gbk', 'utf-8//IGNORE', $sp1d66e2['country'] . $sp1d66e2['area']); } }