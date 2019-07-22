<?php
namespace App\Library\Pay\JiPays; use App\Library\Pay\ApiInterface; use Illuminate\Support\Facades\Log; include_once 'common.php'; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp53f8aa) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp53f8aa; $this->url_return = SYS_URL . '/pay/return/' . $sp53f8aa; } function goPay($spbe80b7, $spa3e681, $sp45f07e, $sp873488, $sp5213ee) { if (!isset($spbe80b7['id'])) { throw new \Exception('请填写id'); } if (!isset($spbe80b7['key'])) { throw new \Exception('请填写key'); } $sp429fcc = sprintf('%.2f', $sp5213ee / 100); $spdc9a36 = $spbe80b7['payway']; switch ($spdc9a36) { case '8001': case '8002': $sp908e43 = 'wechat'; break; case '8004': case '8006': $sp908e43 = 'aliqr'; break; default: throw new \Exception('支付渠道错误'); } $sp2c8827 = SYS_URL . '/qrcode/pay/' . $spa3e681 . '/query'; $spa26894 = array('mch_id' => $spbe80b7['id'], 'sign_type' => 'MD5', 'charset' => 'utf-8', 'version' => '1.0', 'timestamp' => date('Y-m-d H:i:s'), 'notify_url' => $this->url_notify, 'payment_code' => $spdc9a36, 'out_trade_no' => $spa3e681, 'total_fee' => $sp429fcc, 'body' => $sp873488); $sp9ad127 = create_link_string($spa26894); $spa26894['sign'] = md5($sp9ad127 . '&key=' . $spbe80b7['key']); $sp79148a = array('Content-Type:application/x-www-form-urlencoded;charset=utf-8', 'X-Requested-With:XMLHttpRequest'); $sp7fd7bb = 'http://pay.jipays.com/gateway'; $sp3db1b2 = rtrim($sp7fd7bb, '/'); $sp00a165 = curl_http(rtrim($sp3db1b2, '/'), $spa26894, 'post', $sp79148a); $spb9589c = @json_decode($sp00a165, true); if (!$spb9589c || !isset($spb9589c['state'])) { Log::error('Pay.JiPays.goPay.order Error#1: ' . $sp00a165); throw new \Exception('获取付款信息超时, 请刷新重试'); } if ($spb9589c['state'] == '0') { Log::error('Pay.JiPays.goPay.order Error#2: ' . $sp00a165); throw new \Exception($spb9589c['msg']); } if (is_mobile() && is_weixin()) { header('Location:' . $spb9589c['data']['jump_url']); die; } $sp3db1b2 = @strlen($spb9589c['data']['qrcode_url']) ? $spb9589c['data']['qrcode_url'] : $spb9589c['data']['jump_url']; if (strlen($sp3db1b2)) { header('location: /qrcode/pay/' . $spa3e681 . '/' . strtolower($sp908e43) . '?url=' . urlencode($sp3db1b2)); } else { Log::error('Pay.JiPays.goPay.order Error#3: ' . $sp00a165); throw new \Exception('获取付款信息失败, 请联系客服反馈'); } die; } function verify($spbe80b7, $sp04f0f8) { $sp3bce01 = isset($spbe80b7['isNotify']) && $spbe80b7['isNotify']; if ($sp3bce01) { $spa26894 = array('mch_id' => $spbe80b7['id'], 'sign_type' => 'MD5', 'charset' => 'utf-8', 'version' => '1.0', 'out_trade_no' => $_POST['out_trade_no'], 'timestamp' => $_POST['timestamp'], 'payment_code' => $_POST['payment_code'], 'body' => $_POST['body'], 'attach' => $_POST['attach'], 'total_fee' => $_POST['total_fee'], 'trade_no' => $_POST['trade_no'], 'channel_trade_no' => $_POST['channel_trade_no'], 'trade_status' => $_POST['trade_status'], 'payment_time' => $_POST['payment_time'], 'sign' => $_POST['sign']); if (md5(create_link_string($spa26894) . '&key=' . $spbe80b7['key']) !== $spa26894['sign']) { Log::error('Pay.JiPays.verify, sign error $post:' . json_encode($_POST)); echo 'fail'; return false; } if ($_POST['trade_status'] === 'TRADE_FINISHED') { $sp7c88f3 = $_POST['out_trade_no']; $spd63ffb = $_POST['trade_no']; $sp04f0f8($sp7c88f3, (int) round($_POST['total_fee'] * 100), $spd63ffb); } echo 'success'; return true; } else { $spa3e681 = @$spbe80b7['out_trade_no']; if (strlen($spa3e681) < 5) { throw new \Exception('交易单号未传入'); } $spa26894 = array('mch_id' => $spbe80b7['id'], 'sign_type' => 'MD5', 'charset' => 'utf-8', 'version' => '1.0', 'timestamp' => date('Y-m-d H:i:s'), 'out_trade_no' => $spa3e681); $spa26894['sign'] = md5(create_link_string($spa26894) . '&key=' . $spbe80b7['key']); $sp79148a = array('Content-Type:application/x-www-form-urlencoded;charset=utf-8', 'X-Requested-With:XMLHttpRequest'); $sp7fd7bb = 'http://pay.jipays.com/gateway'; $sp3db1b2 = rtrim($sp7fd7bb, '/') . '/trade_query'; $sp00a165 = curl_http(rtrim($sp3db1b2, '/'), $spa26894, 'post', $sp79148a); $spb9589c = @json_decode($sp00a165, true); if (!$spb9589c || !isset($spb9589c['state'])) { Log::error('Pay.JiPays.verify Error#1: ' . $sp00a165); throw new \Exception('查询超时, 请刷新重试'); } if ($spb9589c['state'] == '0') { Log::error('Pay.JiPays.verify.verify Error#2: ' . $sp00a165); throw new \Exception($spb9589c['msg']); } if ($spb9589c['data']['trade_status'] === 'TRADE_FINISHED') { $sp7c88f3 = $spb9589c['data']['out_trade_no']; $spd63ffb = $spb9589c['data']['trade_no']; $sp04f0f8($sp7c88f3, (int) round($spb9589c['data']['total_fee'] * 100), $spd63ffb); return true; } return false; } } }