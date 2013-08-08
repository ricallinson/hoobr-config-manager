<?php
namespace php_require\hoobr_config_reader;

/*
    This action is for providing the main admin module.
*/

$module->exports["admin-main"] = function () use ($render, $pathlib) {
    return $render($pathlib->join(__DIR__, "views", "admin-main.php.html"));
};
