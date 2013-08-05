<?php
namespace php_require\hoobr_config_reader;

class HoobrConfigReader {

    private $config = array();

    public function __construct($module, $overrideDir, $nodeModulesDir) {

        global $require;

        $pathlib = $require("php-path");

        $this->overrideModule = $pathlib->join($overrideDir, $module);

        $this->defaultModule = $pathlib->join($nodeModulesDir, $module, "lib", "config");

        $this->read();
    }

    private function read() {

        global $require;

        $defaultConfig = $require($this->defaultModule);
        $overrideConfig = $require($this->overrideModule);

        $this->config = array_merge($defaultConfig, $overrideConfig);
    }

    public function put($key, $val) {

        if (!isset($this->config[$key])) {
            return false;
        }

        $this->config[$key] = $val;

        return true;
    }

    public function get($key = null) {

        if ($key === null) {
            return $this->config;
        }

        if (!isset($this->config[$key])) {
            return null;
        }

        return $this->config[$key];
    }
}

$module->exports = function ($module, $overrideDir = "./", $nodeModulesDir = "") {
    return new HoobrConfigReader($module, $overrideDir, $nodeModulesDir);
};
