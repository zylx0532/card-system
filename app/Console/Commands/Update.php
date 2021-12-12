<?php
namespace App\Console\Commands; use App\Library\CurlRequest; use function DeepCopy\deep_copy; use Illuminate\Console\Command; use Illuminate\Support\Str; class Update extends Command { protected $signature = 'update {--proxy=} {--proxy-auth=}'; protected $description = 'check update'; public function __construct() { parent::__construct(); } private function download_progress($sp069c22, $spfff5a5) { $sp7f2319 = fopen($spfff5a5, 'w+'); if (!$sp7f2319) { return false; } $sp2df40c = curl_init(); curl_setopt($sp2df40c, CURLOPT_URL, $sp069c22); curl_setopt($sp2df40c, CURLOPT_FOLLOWLOCATION, true); curl_setopt($sp2df40c, CURLOPT_RETURNTRANSFER, true); curl_setopt($sp2df40c, CURLOPT_FILE, $sp7f2319); curl_setopt($sp2df40c, CURLOPT_PROGRESSFUNCTION, function ($spb1b5ca, $sp1e0b27, $spa33621, $sp764491, $spca0b7e) { if ($sp1e0b27 > 0) { echo '    download: ' . sprintf('%.2f', $spa33621 / $sp1e0b27 * 100) . '%'; } }); curl_setopt($sp2df40c, CURLOPT_NOPROGRESS, false); curl_setopt($sp2df40c, CURLOPT_HEADER, 0); curl_setopt($sp2df40c, CURLOPT_USERAGENT, 'card update'); if (defined('MY_PROXY')) { $sp6f2ecf = MY_PROXY; $sp3c6cc7 = CURLPROXY_HTTP; if (strpos($sp6f2ecf, 'http://') || strpos($sp6f2ecf, 'https://')) { $sp6f2ecf = str_replace('http://', $sp6f2ecf, $sp6f2ecf); $sp6f2ecf = str_replace('https://', $sp6f2ecf, $sp6f2ecf); $sp3c6cc7 = CURLPROXY_HTTP; } elseif (strpos($sp6f2ecf, 'socks4://')) { $sp6f2ecf = str_replace('socks4://', $sp6f2ecf, $sp6f2ecf); $sp3c6cc7 = CURLPROXY_SOCKS4; } elseif (strpos($sp6f2ecf, 'socks4a://')) { $sp6f2ecf = str_replace('socks4a://', $sp6f2ecf, $sp6f2ecf); $sp3c6cc7 = CURLPROXY_SOCKS4A; } elseif (strpos($sp6f2ecf, 'socks5://')) { $sp6f2ecf = str_replace('socks5://', $sp6f2ecf, $sp6f2ecf); $sp3c6cc7 = CURLPROXY_SOCKS5_HOSTNAME; } curl_setopt($sp2df40c, CURLOPT_PROXY, $sp6f2ecf); curl_setopt($sp2df40c, CURLOPT_PROXYTYPE, $sp3c6cc7); if (defined('MY_PROXY_PASS')) { curl_setopt($sp2df40c, CURLOPT_PROXYUSERPWD, MY_PROXY_PASS); } } curl_exec($sp2df40c); curl_close($sp2df40c); echo '
'; return true; } public function handle() { set_time_limit(0); $sp6f2ecf = $this->option('proxy'); if (!empty($sp6f2ecf)) { define('MY_PROXY', $sp6f2ecf); } $sp194496 = $this->option('proxy-auth'); if (!empty($sp194496)) { define('MY_PROXY_PASS', $sp194496); } if (!empty(getenv('_'))) { $sp8afdaf = '"' . getenv('_') . '" "' . $_SERVER['PHP_SELF'] . '" '; } else { if (!empty($_SERVER['_'])) { $sp8afdaf = '"' . $_SERVER['_'] . '" "' . $_SERVER['PHP_SELF'] . '" '; } else { if (PHP_OS === 'WINNT') { $sp36f7e3 = dirname(php_ini_loaded_file()) . DIRECTORY_SEPARATOR . 'php.exe'; } else { $sp36f7e3 = dirname(php_ini_loaded_file()); if (ends_with($sp36f7e3, DIRECTORY_SEPARATOR . 'etc')) { $sp36f7e3 = substr($sp36f7e3, 0, -4); } $sp36f7e3 .= DIRECTORY_SEPARATOR . 'php'; } if (!file_exists($sp36f7e3)) { if (PHP_OS === 'WINNT') { $sp36f7e3 = 'php.exe'; } else { $sp36f7e3 = 'php'; } if ((bool) @exec($sp36f7e3 . ' --version') === FALSE) { echo '未找到php安装路径!
'; goto LABEL_EXIT; } } $sp8afdaf = '"' . $sp36f7e3 . '" "' . $_SERVER['PHP_SELF'] . '" '; } } exec($sp8afdaf . ' cache:clear'); exec($sp8afdaf . ' config:clear'); echo '
'; $this->comment('检查更新中...'); $this->info('当前版本: ' . config('app.version')); $sp89bfd4 = @json_decode(CurlRequest::get('https://raw.githubusercontent.com/Tai7sy/card-system/master/.version'), true); if (!@$sp89bfd4['version']) { $this->warn('检查更新失败!'); $this->warn('Error: ' . ($sp89bfd4 ? json_encode($sp89bfd4) : 'Network error')); goto LABEL_EXIT; } $this->info('最新版本: ' . $sp89bfd4['version']); $this->info('版本说明: ' . (@$sp89bfd4['description'] ?? '无')); if (config('app.version') >= $sp89bfd4['version']) { $this->comment('您的版本已是最新!'); $sp864c8c = strtolower($this->ask('是否再次更新 (yes/no)', 'no')); if ($sp864c8c !== 'yes') { goto LABEL_EXIT; } } else { $sp864c8c = strtolower($this->ask('是否现在更新 (yes/no)', 'no')); if ($sp864c8c !== 'yes') { goto LABEL_EXIT; } } $spa3600b = realpath(sys_get_temp_dir()); if (strlen($spa3600b) < 3) { $this->warn('获取临时目录失败!'); goto LABEL_EXIT; } $spa3600b .= DIRECTORY_SEPARATOR . Str::random(16); if (!mkdir($spa3600b) || !is_writable($spa3600b) || !is_readable($spa3600b)) { $this->warn('临时目录不可读写!'); goto LABEL_EXIT; } if (!function_exists('exec')) { $this->warn('函数 exec 已被禁用, 无法继续更新!'); goto LABEL_EXIT; } if (PHP_OS === 'WINNT') { $spd1e74d = 'C:\\Program Files\\7-Zip\\7z.exe'; if (!is_file($spd1e74d)) { $spd1e74d = strtolower($this->ask('未找到7-Zip, 请手动输入7zG.exe路径', $spd1e74d)); } if (!is_file($spd1e74d)) { $this->warn('7-Zip不可用, 请安装7-Zip后重试'); goto LABEL_EXIT; } $spd1e74d = '"' . $spd1e74d . '"'; } else { exec('tar --version', $spc7b6d8, $sp588c3c); if ($sp588c3c) { $this->warn('Error: tar --version 
' . join('
', $spc7b6d8)); goto LABEL_EXIT; } } $this->comment('正在下载新版本...'); $spfff5a5 = $spa3600b . DIRECTORY_SEPARATOR . 'ka_update_' . Str::random(16) . '.tmp'; if (!$this->download_progress($sp89bfd4['url'], $spfff5a5)) { $this->warn('写入临时文件失败!'); goto LABEL_EXIT; } $spbb3a22 = md5_file($spfff5a5); if ($spbb3a22 !== $sp89bfd4['md5']) { $this->warn('更新文件md5校验失败!, file:' . $spbb3a22 . ', require:' . $sp89bfd4['md5']); goto LABEL_EXIT; } $this->comment('正在解压...'); unset($spc7b6d8); if (PHP_OS === 'WINNT') { exec("{$spd1e74d} x -so {$spfff5a5} | {$spd1e74d} x -aoa -si -ttar -o{$spa3600b}", $spc7b6d8, $sp588c3c); } else { exec("tar -zxf {$spfff5a5} -C {$spa3600b}", $spc7b6d8, $sp588c3c); } if ($sp588c3c) { $this->warn('Error: 解压失败 
' . join('
', $spc7b6d8)); goto LABEL_EXIT; } $this->comment('正在关闭主站...'); exec($sp8afdaf . ' down'); sleep(5); $this->comment(' --> 正在清理旧文件...'); $sp7a8f15 = base_path(); foreach (array('app', 'bootstrap', 'config', 'public/dist', 'database', 'routes', 'vendor') as $sp7c9413) { \File::deleteDirectory($sp7a8f15 . DIRECTORY_SEPARATOR . $sp7c9413); } $this->comment(' --> 正在复制新文件...'); \File::delete($spa3600b . DIRECTORY_SEPARATOR . 'card_dist' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png'); \File::delete($spa3600b . DIRECTORY_SEPARATOR . 'card_dist' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess'); \File::delete($spa3600b . DIRECTORY_SEPARATOR . 'card_dist' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'web.config'); \File::delete($spa3600b . DIRECTORY_SEPARATOR . 'card_dist' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'nginx.conf'); \File::delete($spa3600b . DIRECTORY_SEPARATOR . 'card_dist' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'robots.txt'); \File::copyDirectory($spa3600b . DIRECTORY_SEPARATOR . 'card_system_free_dist', $sp7a8f15); $this->comment(' --> 正在创建缓存...'); exec($sp8afdaf . ' cache:clear'); exec($sp8afdaf . ' route:cache'); exec($sp8afdaf . ' config:cache'); $this->comment(' --> 正在更新数据库...'); exec($sp8afdaf . ' migrate'); if (PHP_OS === 'WINNT') { echo '
'; $this->alert('请注意手动设置目录权限'); $this->comment('    storage 可读可写             '); $this->comment('    bootstrap/cache/ 可读可写    '); echo '

'; } else { $this->comment(' --> 正在设置目录权限...'); exec('rm -rf storage/framework/cache/data/*'); exec('chmod -R 777 storage/'); exec('chmod -R 777 bootstrap/cache/'); } $this->comment('正在启用主站...'); exec($sp8afdaf . ' up'); exec($sp8afdaf . ' queue:restart'); $spdb8486 = true; LABEL_EXIT: if (isset($spa3600b) && strlen($spa3600b) > 19) { $this->comment('清理临时目录...'); \File::deleteDirectory($spa3600b); } if (isset($spdb8486) && $spdb8486) { $this->info('更新成功!'); } if (PHP_OS === 'WINNT') { } else { exec('rm -rf storage/framework/cache/data/*'); exec('chmod -R 777 storage/'); exec('chmod -R 777 bootstrap/cache/'); } echo '
'; die; } }