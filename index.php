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

$exports["admin-sidebar"] = function () use ($req, $render, $pathlib, $utils) {

    $list = $utils->getModuleList($req->cfg("approot"));

    return $render($pathlib->join(__DIR__, "views", "admin-sidebar.php.html"), array(
        "list" => $list,
        "current" => $req->param("config-module")
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
        "bucketId" => $bucketId,
        "buckets" => array()
    ));
};

$exports["admin-save"] = function () use ($req, $res) {

    $module = $req->param("config-module");
    $bucketId = $req->param("config-bucket-id");

    $res->redirect("?page=admin&module=hoobr-config-manager&action=main&config-module=" . $module . "&bucket-id=" . $bucketId);
};
