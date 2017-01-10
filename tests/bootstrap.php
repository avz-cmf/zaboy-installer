<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.01.17
 * Time: 12:35
 */
error_reporting(E_ALL | E_STRICT);

// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
// Setup autoloading
require 'vendor/autoload.php';

require_once 'config/env_configurator.php';
