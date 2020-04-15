<?php
/**
 * OAK Framework v1.0
 * 
 * @version 1.0
 * @author Murat ALABACAK <alabacakm@gmail.com>
 */
define("OAK_START", 	microtime());
define("OAK_ENV", 		"development"); // Also .env

/**
 * Required path definitions
 */
define("ROOT_PATH", 	realpath(__DIR__."/"));
define("APP_PATH", 		realpath(ROOT_PATH."/app/"));
define("SYSTEM_PATH", 	realpath(ROOT_PATH."/system/"));
define("PUBLIC_PATH", 	realpath(ROOT_PATH."/public/"));
define("STORAGE_PATH", 	realpath(ROOT_PATH."/storage/"));
