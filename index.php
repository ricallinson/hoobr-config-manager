<?php
namespace php_require\hoobr_config_reader;

$req = $require("php-http/request");
$res = $require("php-http/response");
$render = $require("php-render-php");
$pathlib = $require("php-path");
$configReader = $require("hoobr-config-manager/lib/parser");

$exports["admin-sidebar"] = function () use ($req, $render) {
    return "";
};

/*
    This action is for providing the main admin module.
*/

$exports["admin-main"] = function () use ($require, $req, $render, $pathlib, $configReader) {

    $module = $req->param("config-module");
    $bucketId = $req->param("config-bucket-id", 1234);

    $config = $configReader($module, $req->cfg("cfgroot"));

    $defaults = $require($config->defaultModule);

    $overrides = $require($config->overrideModule);

    $buckets = $require($config->makeBucketModulePath($bucketId));

    $table = array();

    foreach ($defaults as $key => $default) {

        $override = $req->find($key, $overrides);

        $bucket = $req->find($key, $buckets);

        $table[$key] = array(
            "default" => $default,
            "override" => $override,
            "bucket" => $bucket
        );
    }

    return $render($pathlib->join(__DIR__, "views", "admin-main.php.html"), array(
        "module" => $module,
        "table" => $table,
        "bucketId" => $bucketId
    ));
};

$exports["admin-save"] = function () use ($req, $res) {

    $module = $req->param("config-module");
    $bucketId = $req->param("config-bucket-id");

    $res->redirect("?page=admin&module=hoobr-config-manager&action=main&config-module=" . $module . "&bucket-id=" . $bucketId);
};
