<?php

// ////////////////////////////////////////////////////////////////////////////////////////////

set_time_limit(0);
ini_set('max_execution_time', '90000');
ini_set('memory_limit', '256M');
ini_set('realpath_cache_size', '16M');
ini_set('realpath_cache_ttl', '1200');

if (($_GET['pwd']) != 'sde45f') {
	die('Forbidden');
}

// ////////////////////////////////////////////////////////////////////////////////////////////

function stripslashes_r($array)
{
	foreach($array as $key => $value) {
		$array[$key] = is_array($value) ? stripslashes_r($value) : stripslashes($value);
	}

	return $array;
}

// ////////////////////////////////////////////////////////////////////////////////////////////

define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));

function detect_utf_encoding($text)
{
	$first2 = substr($text, 0, 2);
	$first3 = substr($text, 0, 3);
	$first4 = substr($text, 0, 3);
	if ($first3 == UTF8_BOM) return 'UTF-8';
	elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE';
	elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE';
	elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE';
	elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE';
	return false;
}

// ////////////////////////////////////////////////////////////////////////////////////////////

if (get_magic_quotes_gpc()) {
	$_GET = stripslashes_r($_GET);
	$_POST = stripslashes_r($_POST);
	$_COOKIE = stripslashes_r($_COOKIE);
	$_REQUEST = stripslashes_r($_REQUEST);
}

if ($_GET['a'] == 'view') {
	header('Content-type: text/plain;');
	echo (implode(file($_GET['f'])));
	exit;
}

header('Content-type: text/html; charset=utf-8');
set_time_limit(8640000);

// ////////////////////////////////////////////////////////////////////////////////////////////

if ($_POST['a'] == 'delfile') {
	unlink($_POST['file']);
	if (!file_exists($_POST['file'])) {
		echo 'removed ' . $_POST['file'];
	}
	else {
		echo '<font color=red>error in removing ' . $_POST['file'] . "</font><br />";
	}

	exit;
}

// ////////////////////////////////////////////////////////////////////////////////////////////

if ($_POST['a'] == 'delall') {
	$list = explode("\n", $_POST['files']);
	for ($i = 0; $i < count($list); $i++) {
		$file = trim($list[$i]);
		if ($file == '') continue;
		unlink($file);
		if (!file_exists($file)) {
			echo 'removed ' . $file . "<br />";
		}
		else {
			echo '<font color=red>error in removing ' . $file . "</font><br />";
		}
	}

	exit;
}

// ////////////////////////////////////////////////////////////////////////////////////////////

function scan_dir($dirname)
{
	global $text, $retext, $replace, $ext, $cnt, $list, $reg_exp, $orig_text, $timeround, $extyes;
	$dir = opendir($dirname);
	while (($file = @readdir($dir)) !== false) {
		if ($file != "." && $file != "..") {
			$file_name = $dirname . "/" . $file;
			if (is_link($file_name)) continue;
			if (is_file($file_name)) {
				$ext_name = substr(strrchr($file_name, '.'), 1);
				if (strpos($file, $text) !== false) {
					$GLOBALS['fn'][] = $file_name;
				}

				if (in_array($ext_name, $ext) || $file_name == $dirname . '/fixer.php') continue;
				
				if ($extyes != null && count($extyes) > 0) {
					if (!in_array($ext_name, $extyes)) continue;
				}
				
				$mtime = filemtime($file_name);
				if ($timeround > 0) {
					if (($mtime < $timeround - 86400) || 
					    ($mtime > $timeround + 86400)) {
						continue;
					}
				}
				
				if (filesize($file_name) < $_POST['maxsize'] * 1024) {
					$content = file_get_contents($file_name);
				}
				else {
					$content = '';
				}

				$content = preg_replace("|\r|", "", $content);
				$text = preg_replace("|\r|", "", $text);
				$res = false;
				if ($reg_exp) {
					$mod = detect_utf_encoding($text) ? 'u' : '';
					if (preg_match('|' . $text . '|smi' . $mod, $content, $found)) {
						$res = true;
					}
				}
				else {
					$res = strpos($content, $text);
				}

				if ($res !== false) {
					$cnt++;
					if ($replace) {
						if ($reg_exp) {
							$mod = detect_utf_encoding($text) ? 'u' : '';
							$content = preg_replace('|' . $text . '|smi' . $mod, $retext, $content);
						}
						else {
							$content = str_replace($text, $retext, $content);
						}

						$old_p = - 1;
						if (!is_writable($file_name)) {
							$old_p = fileperms($file_name);
							chmod($file_name, 0666);
						}

						file_put_contents($file_name, $content);
						if ($old_p > - 1) {
							chmod($file_name, $old_p);
						}
					}

					echo '<tr><td>' . $cnt . '</td><td><a href="#" style="color: red" onclick="cmd(\'delfile&file=' . $file_name . '\'); return false;"><b>[&#x2623;]</b></a></td><td>' . filesize($file_name) . '&nbsp;&nbsp;&nbsp;</td><td>' . $mtime . ' ' . date('d/m/Y H:i:s', $mtime) . '&nbsp;&nbsp;&nbsp;</td><td><a href="?pwd=sde45f&a=view&f=' . $file_name . '" target=_blank>' . $file_name . '</a></td><td>' . '</td></tr>';
					$GLOBALS['list'][] = $file_name;
				}
			}

			if (is_dir($file_name)) {
				if (!is_link($file_name)) {
					scan_dir($file_name);
				}
			}
		}
	}

	closedir($dir);
}

// ////////////////////////////////////////////////////////////////////////////////////////////

?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<meta name="Author" content="Greg Zemskov, Revisium.com" />
<meta name="robots" content="noindex, nofollow" />
<title>FIXXZER -- Revisium Fixing Tool</title>

<style type="text/css">
body 
{
   background: #89daff;
   color: #1d2b32;
   font-family: Georgia;
   font-size: 11px;
}

TEXTAREA,INPUT 
{
padding: 10px;
font-family: Georgia;
font-size: 11px;
background: #004667;
color: #cdefff;
}

INPUT.go
{
padding: 10px;
font-family: Georgia;
font-size: 13px;
background: #007067;
color: #cdefff;

}

</style>

<script language="javascript">
var ajaxHttpRequest;

/**
 * Handle ajax response
 */
function handleResponse() {

    // skip REQUEST_SENT

    if (ajaxHttpRequest.readyState == 2)
       return; 


    // skip PARTIAL RESPONSE

    if (ajaxHttpRequest.readyState == 3)
       return; 


    // process normal page response

    if (ajaxHttpRequest.readyState = 4 && ajaxHttpRequest.status == 200) {
       var ajaxResponse = ajaxHttpRequest.responseText;

       document.getElementById('ajax_result').innerHTML = (ajaxResponse);
    }
}

/**
 * Perform recursively scanning procedure
 */
function cmd(action)
{
  if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
     ajaxHttpRequest = new XMLHttpRequest();
  } else { // code for IE6, IE5
     ajaxHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
  }

  /**
   * Ajax processing
   */
  ajaxHttpRequest.onreadystatechange = handleResponse;
  var arguments = "a=" + action;

  ajaxHttpRequest.open("POST", '<?php
echo basename($_SERVER['PHP_SELF']) ?>?pwd=sde45f&<?php echo rand(1, time()); ?>', true);
  ajaxHttpRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  ajaxHttpRequest.send(arguments);
}

function deleteAll(txtBox) {
   var o = document.getElementById(txtBox);

   cmd('delall&files=' + o.value);
}

</script>


</head>

<body style="margin: 0; padding: 0;">
<div id="ajax_result" style="padding: 5px; background: #EAFFEA; border: 2px solid #B0FFB0; margin-bottom: 10px"></div>


<?php

if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data)
	{
		$f = fopen($filename, 'w');
		if (!$f) {
			return false;
		}
		else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

if (isset($_POST['submit'])) {
	$err_arr = array(
		0 => '',
		1 => ''
	);
	if ($_POST['text'] == '' || $_POST['dir'] == '') {
		$err = ' style="border: 1px solid red"';
		if ($_POST['text'] == '') $err_arr[0] = $err;
		if ($_POST['dir'] == '') $err_arr[1] = $err;
	}
	else {
		$timeround = isset($_POST['timeround']) ? $_POST['timeround'] : 0;
		$dir = trim($_POST['dir']);
		$text = trim($_POST['text']);
		$orig_text = $text;
		$reg_exp = isset($_POST['reg_exp']) ? $_POST['reg_exp'] : 0;
		$replace = isset($_POST['replace']) ? $_POST['replace'] : 0;
		if ($reg_exp) {
			$text = quotemeta($text);
			$text = str_replace('|', '\|', $text);
			$text = str_replace('@@any@@', '.+?', $text);
			$text = str_replace('@@digit@@', '\d+', $text);
			$text = str_replace('@@space@@', '\s*', $text);
		}

		$retext = trim($_POST['retext']);
		$ext = explode(',', $_POST['ext']);
		if (!empty($_POST['extyes'])) {
			$extyes = explode(',', $_POST['extyes']);
		} else {
			$extyes = null;
		}
		
		$cnt = 0;
		$list = array();
		$start_time = microtime(true);
		echo '<div id="search_pane" style="padding: 10px; width: 98%; background: #FFEAEA; border: 2px solid #FFB0B0; margin-bottom: 20px"><div style="height: 300px; overflow: auto"><b>Found:</b> <table cellspacing=0 cellpadding=2>';
		$GLOBALS['fn'] = array();
		$GLOBALS['list'] = array();
		
		scan_dir($dir);
		echo '</table></div><p><textarea cols=130 id="list" rows=15>' . implode("\n", $GLOBALS['list']) . '</textarea><p><a href="#" onclick="if (confirm(\'Sure?\')) deleteAll(\'list\'); return false;" style="font-size: 19px; color: red"><b>&#x2622; Remove All</b></a></p><br/>';
		echo 'Found in filenames:<br/><textarea cols=130 rows=15>' . implode("\n", $GLOBALS['fn']) . '</textarea><br/>';
		if (!count($GLOBALS['list'])) {
			echo '<script language="javascript">document.getElementById("search_pane").style.display="none";</script></div><p align=center>Nothing Found :-(</p><div>';
		}
		else {
			$exec_time = microtime(true) - $start_time;
			printf("<p><b>Spent: %f sec.</b></div>", $exec_time);
		}
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////

?>

<div style="text-align: center;">

    <form method="post">
        <table cellpadding="5" cellspacing="0" border="0" align="center">
            <tr>
                <td align="right">
                    Search for:
                </td>
                <td>
                    <textarea<?php
echo $err_arr[0]; ?> name="text" cols="60" rows="7"><?php
echo $orig_text; ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">
                    Replace with:
                </td>
                <td>
                    <textarea name="retext" cols="60" rows="2"><?php
echo isset($retext) ? $retext : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">
                    <b>RegExp Mode (@@any@@, @@space@@, @@digit@@):</b>
                </td>
                <td>
                    <input style="font-size: 20px" type="checkbox"<?php
echo isset($reg_exp) && $reg_exp == 1 ? ' checked' : ''; ?> name="reg_exp" value="1" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    <b>Replace:</b>
                </td>
                <td>
                    <input style="font-size: 20px" type="checkbox"<?php
echo isset($replace) && $replace == 1 ? ' checked' : ''; ?> name="replace" value="1" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    Search for extentions:
                </td>
                <td>
                    <input type="text" size="100" name="extyes" value="<?php
echo isset($_POST['extyes']) ? $_POST['extyes'] : ''; ?>" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    Ignore extentions:
                </td>
                <td>
                    <input type="text" size="100" name="ext" value="<?php
echo isset($_POST['ext']) ? $_POST['ext'] : 'gif,jpg,jpeg,png,zip,rar,pdf,css,flv,mp3,mp4,MP4,jpa,tar,JPG,mpg,doc,xls,ini,docx,xlsx'; ?>" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    Max file size:
                </td>
                <td>
                    <input type="text" size="6" name="maxsize" value="<?php
echo isset($_POST['maxsize']) ? $_POST['maxsize'] : 600; ?>" /> Kb
                </td>
            </tr>
            <tr>
                <td align="right">
                    Folder:
                </td>
                <td>
                    <input<?php
echo $err_arr[1]; ?> type="text" size="33" name="dir" value="<?php
echo isset($dir) ? $dir : '.'; ?>" title='Search in folder' />

                </td>

            </tr>
            <tr>
                <td align="right">
                    Timestamp:
                </td>
                <td>
                    <input type="text" size="33" name="timeround" value="<?php
echo isset($timeround) ? $timeround : '0'; ?>" title='Search in timestamp neightborhood' /> Cur: <?php echo time(); ?>

                </td>

            </tr>
            <tr>
				<td></td>
                <td>
                    <br /><input type="submit" name="submit" class="go" value="Search / Replace" />
                </td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>