<?php
namespace App\Http\Middleware; use Closure; use Illuminate\Support\Facades\Auth; class RedirectIfAuthenticated { public function handle($sp510ef3, Closure $sp9c8a28, $sp6f9ebc = null) { if (Auth::guard($sp6f9ebc)->check()) { return redirect('/home'); } return $sp9c8a28($sp510ef3); } }