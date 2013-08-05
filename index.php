<?php
namespace php_require\hoobr_config_reader;

class HoobrConfigReader {

    private $config = array();

    public function __construct($module) {
        $this->read($module);
    }

    private function read($module) {

        global $require;

        $pathlib = $require("php-path");
        $defaultConfig = $require($pathlib->join($module, "lib", "config"));
        $overrideConfig = $require($pathlib->join("./", $module));

        $this->config = array_merge($defaultConfig, $overrideConfig);
    }

    public function put($key, $val) {

        if (!isset($this->config[$key])) {
            return false;
        }

        $this->config[$key] = $val;

        return true;
    }

    public function get($key) {

        if (!isset($this->config[$key])) {
            return false;
        }

        return $this->config[$key];
    }
}

$module->exports = function ($module) {
    return new HoobrConfig($module);
};
