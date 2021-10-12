<?php
namespace App\Http\Controllers\Auth; use App\Http\Controllers\Controller; use Illuminate\Auth\Events\PasswordReset; use Illuminate\Http\Request; use Illuminate\Http\Response; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Password; class ResetPasswordController extends Controller { public function reset(Request $sp510ef3) { $this->validate($sp510ef3, array('token' => 'required', 'email' => 'required|email', 'password' => 'required|confirmed|min:6')); $sp15bacb = Password::broker()->reset($sp510ef3->only('email', 'password', 'password_confirmation', 'token'), function ($sp24cedd, $sp7a8ba8) { $this->resetPassword($sp24cedd, $sp7a8ba8); }); return $sp15bacb == Password::PASSWORD_RESET ? response(array()) : response(array('message' => trans($sp15bacb)), 400); } public function change(Request $sp510ef3) { $this->validate($sp510ef3, array('old' => 'required|string', 'password' => 'required|string|min:6|max:32|confirmed')); $sp24cedd = Auth::user(); if (!Hash::check($sp510ef3->post('old'), $sp24cedd->password)) { return response(array('message' => '旧密码错误，请检查'), Response::HTTP_BAD_REQUEST); } $sp1f4391 = $this->resetPassword($sp24cedd, $sp510ef3->post('password')); return response(array(), 200, array('Authorization' => 'Bearer ' . $sp1f4391)); } public static function resetPassword($sp24cedd, $sp7a8ba8, $sp827c4a = true) { $sp24cedd->password = Hash::make($sp7a8ba8); $sp24cedd->setRememberToken(time()); $sp24cedd->saveOrFail(); event(new PasswordReset($sp24cedd)); if ($sp827c4a) { return Auth::login($sp24cedd); } else { return true; } } }