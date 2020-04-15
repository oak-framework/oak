<?php
/**
 * OAK Framework v1.0
 * 
 * @version 1.0
 * @author Murat ALABACAK <alabacakm@gmail.com>
 */

/**
 * Require the globals
 */
require_once __DIR__.'/../globals.php';

/**
 * Require the bootloader
 */
require_once SYSTEM_PATH.'/Core/Boot/init.php';

/**
 * Start the application
 */
\System\Core\Boot\Application::start();