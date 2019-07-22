<?php
require_once 'alipay_core.function.php'; class AlipayNotify { var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&'; var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?'; var $alipay_config; function __construct($sp487939) { $this->alipay_config = $sp487939; } function AlipayNotify($sp487939) { $this->__construct($sp487939); } function verifyNotify() { if (empty($_POST)) { return false; } else { $sp5777f3 = $this->getSignVeryfy($_POST, $_POST['sign']); $spa46c15 = 'false'; if (!empty($_POST['notify_id'])) { $spa46c15 = $this->getResponse($_POST['notify_id']); } if (preg_match('/true$/i', $spa46c15) && $sp5777f3) { return true; } else { return false; } } } function verifyReturn() { if (empty($_GET)) { return false; } else { $sp5777f3 = $this->getSignVeryfy($_GET, $_GET['sign']); $spa46c15 = 'false'; if (!empty($_GET['notify_id'])) { $spa46c15 = $this->getResponse($_GET['notify_id']); } if (preg_match('/true$/i', $spa46c15) && $sp5777f3) { return true; } else { return false; } } } function getSignVeryfy($sp5f0fbc, $spa109d2) { $sp67f981 = paraFilter($sp5f0fbc); $sp012af7 = argSort($sp67f981); $sp4a09c0 = createLinkString($sp012af7); switch (strtoupper(trim($this->alipay_config['sign_type']))) { case 'MD5': $spb8f336 = md5Verify($sp4a09c0, $spa109d2, $this->alipay_config['key']); break; default: $spb8f336 = false; } return $spb8f336; } function getResponse($sp1d3c7b) { $sp69068f = strtolower(trim($this->alipay_config['transport'])); $sp669101 = trim($this->alipay_config['partner']); if ($sp69068f == 'https') { $sp2ad67c = $this->https_verify_url; } else { $sp2ad67c = $this->http_verify_url; } $sp2ad67c = $sp2ad67c . 'partner=' . $sp669101 . '&notify_id=' . $sp1d3c7b; $spa46c15 = getHttpResponseGET($sp2ad67c, $this->alipay_config['cacert']); return $spa46c15; } }