<?php
namespace App\Http\Controllers\Shop; use App\System; use Carbon\Carbon; use Illuminate\Database\Eloquent\Relations\Relation; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use App\Library\Response; use App\Library\Geetest; use Illuminate\Support\Facades\Cookie; class Order extends Controller { function get(Request $sp510ef3) { if (\App\System::_getInt('vcode_shop_search') === 1) { $this->validateCaptcha($sp510ef3); } $sp90af04 = \App\Order::where('created_at', '>=', (new Carbon())->addDay(-\App\System::_getInt('order_query_day', 30))); $spb2a0bb = $sp510ef3->post('type', ''); if ($spb2a0bb === 'cookie') { $sp9dd25c = Cookie::get('customer'); if (strlen($sp9dd25c) !== 32) { return Response::success(); } $sp90af04->where('customer', $sp9dd25c); } elseif ($spb2a0bb === 'order_no') { $sp31fa09 = $sp510ef3->post('order_no', ''); if (strlen($sp31fa09) !== 19) { return Response::success(); } $sp90af04->where('order_no', $sp31fa09); } elseif ($spb2a0bb === 'contact') { $sp9c4e0c = $sp510ef3->post('contact', ''); if (strlen($sp9c4e0c) < 6) { return Response::success(); } $sp90af04->where('contact', $sp9c4e0c); if (System::_getInt('order_query_password_open')) { $sp5ce090 = $sp510ef3->post('query_password', ''); if (strlen($sp5ce090) < 6) { return Response::success(); } $sp90af04->where('query_password', $sp5ce090); } } else { return Response::fail(trans('shop.search_type.required')); } $sp9bc39e = array('id', 'created_at', 'order_no', 'contact', 'status', 'send_status', 'count', 'paid'); if (1) { $sp9bc39e[] = 'product_name'; $sp9bc39e[] = 'contact'; $sp9bc39e[] = 'contact_ext'; } $spe24324 = $sp90af04->orderBy('id', 'DESC')->get($sp9bc39e); $spa740ca = ''; return Response::success(array('list' => $spe24324, 'msg' => count($spe24324) ? $spa740ca : '')); } }