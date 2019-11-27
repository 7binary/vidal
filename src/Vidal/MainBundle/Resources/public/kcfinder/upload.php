<?php

/** This file is part of KCFinder project
  *
  *      @desc Upload calling script
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

//if (!isset($_SESSION['_sf2_attributes']['_security_everything'])
//    || !preg_match('/(ROLE_ADMIN|ROLE_SUPERADMIN)/', $_SESSION['_sf2_attributes']['_security_everything'])) {
//    echo '<h1>This page is for admins only</h1>';
//    exit;
//}

require "core/autoload.php";
$uploader = new uploader();

$uploader->upload();

?>