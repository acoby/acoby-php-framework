<?php
declare(strict_types=1);
namespace acoby;

require_once 'BaseTestCase.php';


spl_autoload_extensions(".php");
spl_autoload_register();

error_log("[DEBUG] Run tests");