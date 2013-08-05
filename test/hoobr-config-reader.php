<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

/*
    Now we "require()" the file to test.
*/

$module = new stdClass();
require(__DIR__ . "/../index.php");
$reader = $module->exports;

/*
    Now we test it.
*/

describe("hoobr-config-reader", function () use ($reader) {

    it("should return [value2]", function () use ($reader) {

        $config = $reader("sample", "./fixtures", "./fixtures/node_modules");

        assert($config->get("key2") === "value2");
    });

    it("should return [val1]", function () use ($reader) {

        $config = $reader("sample", "./fixtures", "./fixtures/node_modules");

        $array = $config->get();

        assert($array["key1"] === "val1");
    });

    it("should return [true]", function () use ($reader) {

        $config = $reader("sample", "./fixtures", "./fixtures/node_modules");

        assert($config->put("key3", "value3") === true);
        assert($config->get("key3") === "value3");
    });

    it("should return [false]", function () use ($reader) {

        $config = $reader("sample", "./fixtures", "./fixtures/node_modules");

        assert($config->put("key4", "val4") === false);
        assert($config->get("key4") === null);
    });
});
