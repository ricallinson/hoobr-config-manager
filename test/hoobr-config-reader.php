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

    it("should return ", function () use ($reader) {

        $config = $reader("sample", "./fixtures", "./fixtures/node_modules");

        assert($config->get("key2") === "value2");
    });
});
