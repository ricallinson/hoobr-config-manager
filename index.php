<?php
namespace php_require\hoobr_config_reader;

$req = $require("php-http/request");
$res = $require("php-http/response");
$render = $require("php-render-php");
$pathlib = $require("php-path");
$configReader = $require("hoobr-config-manager/lib/parser");
$utils = $require("hoobr-packages/lib/utils");

/*
    Show the sidebar with links to all modules.
*/

$exports["admin-sidebar"] = function () use ($req, $render, $pathlib, $utils, $configReader) {

    $module = $req->param("config-module");
    $config = $configReader($module, $req->cfg("cfgroot"));
    $showBuckets = count($config->get()) > 0;
    $bucketId = $req->param("config-bucket-id");
    $buckets = array("1234");
    $modules = $utils->getModuleList($req->cfg("approot"));

    return $render($pathlib->join(__DIR__, "views", "admin-sidebar.php.html"), array(
        "buckets" => $buckets,
        "modules" => $modules,
        "currentModule" => $module,
        "currentBucketId" => $bucketId,
        "showBuckets" => $showBuckets
    ));
};

/*
    This action is for providing the main admin module.
*/

$exports["admin-main"] = function () use ($require, $req, $render, $pathlib, $configReader) {

    $module = $req->param("config-module");
    $bucketId = $req->param("config-bucket-id");

    $config = $configReader($module, $req->cfg("cfgroot"));

    $defaults = $require($config->defaultModule);

    if (!count($defaults)) {
        return $render($pathlib->join(__DIR__, "views", "admin-empty.php.html"), array(
            "module" => $module
        ));
    }

    $overrideConfig = $require($config->overrideModule);

    $bucketConfig = $require($config->makeBucketModulePath($bucketId));

    $table = array();

    foreach ($defaults as $key => $default) {

        $override = $req->find($key, $overrideConfig);

        $bucket = $req->find($key, $bucketConfig);

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

    $res->redirect("?page=admin&module=hoobr-config-manager&action=main&config-module=" . $module . "&config-bucket-id=" . $bucketId);
};

$exports["admin-new-bucket"] = function () use ($req, $res) {

    $module = $req->param("config-module");
    $bucketId = uniqid();

    $res->redirect("?page=admin&module=hoobr-config-manager&action=main&config-module=" . $module . "&config-bucket-id=" . $bucketId);
};
