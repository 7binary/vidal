<?php
header("Content-Type:text/html; charset=UTF-8");
?>
<h1>Экспресс-проверка защиты сайта</h1>

<p>Выборочная проверка файлов, каталогов на возможность записи, а также проверка настроек PHP.</p>

<ol>
<?php
   if (function_exists('php_ini_loaded_file') && is_callable('php_ini_loaded_file')) {
	   $path_ini = php_ini_loaded_file();
   } else {
	   $path_ini = 'php.ini';
   }
 
   
  // проверяем запрещенные функции
   $l_CmdList = explode(',', 'popen,exec,ftp_exec,system,passthru,proc_open,shell_exec');

   $l_Disabled = true;
   $l_NotDisabled = array();
   foreach ($l_CmdList as $l_F) {
      if (function_exists($l_F) && is_callable($l_F)) {
         $l_Disabled = false;
         $l_NotDisabled[] = $l_F; 
      }
   }

  if ($l_Disabled) {
     echo '<li><font color=green>Системные функции запрещены - безопасно, <br>для отключения защиты поставьте символ "точка с запятой" ; перед строкой disable_functions=... в  файле ' .  $path_ini . '</font><br/><br/>';
  } else {
     echo '<li><font color=red>Защита не работает, так как разрешены системные функции PHP: ' . implode(', ', $l_NotDisabled) . '</font><br/>Данные функции должны быть запрещены в файле ' . $path_ini . ' согласно этой <a href="https://revisium.com/ru/clients_faq/#q10">инструкции</a>.</br><br/>';
  }

  // проверяем выборочные файлы  
  $l_FileList = explode(',', 'index.php,wp-config.php,wp-settings.php,configuration.php,.htaccess,administrator/index.php,administrator/,wp-includes,wp-admin,templates,manager,includes/router.php,components/com_contact/views,manager/templates,modules/user');

  $l_Protected = true; 
  $l_UnprotectedList = array();
  foreach ($l_FileList as $l_F) {
      if (file_exists($l_F) && ((fileperms($l_F) & 000222) > 0)) {
         $l_Protected = false;
         $l_UnprotectedList[] = $l_F; 
      }
   }

   if ($l_Protected) {
     echo '<li><font color=green>Системные файлы защищены от записи и изменений - безопасно</font><br/><br/>';
  } else {
     echo '<li><font color=red>Защита не работает, так как часть системных файлов доступна для записи и изменений (примеры таких файлов): ' . implode(', ', $l_UnprotectedList) . '</font><br/>Все системные файлы должны быть сделаны "только для чтения" согласно этой <a href="https://revisium.com/ru/clients_faq/#q10">инструкции</a>.</br><br/>';
  }
 
?>
</ol>

<p>Если оба пункта зеленые - защита работает, если хотя бы один из них красный - значит защита работает не полностью.</p>

Подробная инструкция на отключение и восстановление защиты приведена на странице <a href="https://revisium.com/ru/clients_faq/#q10">https://revisium.com/ru/clients_faq/#q10</a>