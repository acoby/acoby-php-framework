<?php
declare(strict_types=1);
namespace acoby;

use acoby\services\ConfigService;

require_once 'BaseTestCase.php';

spl_autoload_extensions(".php");
spl_autoload_register();

error_log("[DEBUG] Prepare database");
ConfigService::setString("acoby_db_dsn","");
ConfigService::setString("acoby_db_username",""); 
ConfigService::setString("acoby_db_password","");

error_log("[DEBUG] Run tests");