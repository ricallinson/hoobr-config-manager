<?php
namespace php_require\hoobr_config_reader;

class HoobrConfigReader {

    private $config = array();

    public function __construct($module, $overrideDir, $nodeModulesDir) {

        $this->overrideDir = $overrideDir;

        $this->nodeModulesDir = $nodeModulesDir;

        $this->read($module);
    }

    private function read($module) {

        global $require;

        $pathlib = $require("php-path");

        $overridePath = $pathlib->join($this->overrideDir, $module);
        $defaultPath = $pathlib->join($this->nodeModulesDir, $module, "lib", "config");

        $overrideConfig = $require($overridePath);
        $defaultConfig = $require($defaultPath);

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
            return false;
        }

        return $this->config[$key];
    }
}

$module->exports = function ($module, $overrideDir = "./", $nodeModulesDir = "") {
    return new HoobrConfigReader($module, $overrideDir, $nodeModulesDir);
};
