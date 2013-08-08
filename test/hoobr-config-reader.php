<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

/*
    Now we "require()" the file to test.
*/

$module = new stdClass();
require(__DIR__ . "/../lib/index.php");
$reader = $module->exports;

/*
    Now we test it.
*/

describe("hoobr-config-reader", function () use ($reader) {

    global $require;

    $pathlib = $require("php-path");
    $overrideModuleDir = $pathlib->join(__DIR__, "./fixtures");
    $defaultsModuleDir = $pathlib->join(__DIR__, "./fixtures/node_modules");

    it("should return [value2]", function () use ($reader, $overrideModuleDir, $defaultsModuleDir) {

        $config = $reader("sample", $overrideModuleDir, $defaultsModuleDir);

        assert($config->get("key2") === "value2");
    });

    it("should return [val1]", function () use ($reader, $overrideModuleDir, $defaultsModuleDir) {

        $config = $reader("sample", $overrideModuleDir, $defaultsModuleDir);

        $array = $config->get();

        assert($array["key1"] === "val1");
    });

    it("should return [true]", function () use ($reader, $overrideModuleDir, $defaultsModuleDir) {

        $config = $reader("sample", $overrideModuleDir, $defaultsModuleDir);

        assert($config->put("key3", "value3") === true);
        assert($config->get("key3") === "value3");
    });

    it("should return [false]", function () use ($reader, $overrideModuleDir, $defaultsModuleDir) {

        $config = $reader("sample", $overrideModuleDir, $defaultsModuleDir);

        assert($config->put("key4", "val4") === false);
        assert($config->get("key4") === null);
    });

    it("should return []", function () use ($reader, $overrideModuleDir, $defaultsModuleDir) {

        $config = $reader("sample", $overrideModuleDir, $defaultsModuleDir);

        $result = $config->write();

        assert($result === true);
    });
});
