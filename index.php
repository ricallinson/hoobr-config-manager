<?php
namespace php_require\hoobr_config_reader;

class HoobrConfigReader {

    private $config = array();

    public function __construct($module, $overrideDir, $defaultsDir) {

        global $require;

        $pathlib = $require("php-path");

        $this->overrideModule = $module;

        if ($overrideDir) {
            $this->overrideModule = $pathlib->join($overrideDir, $module);
        }

        $this->defaultModule = $pathlib->join($module, "lib", "config");

        if ($defaultsDir) {
            $this->defaultModule = $pathlib->join($defaultsDir, $this->defaultModule);
        }

        $this->read();
    }

    private function read() {

        global $require;

        /*
            Set the config and merge in any overrides found.
        */

        $defaultConfig = $require($this->defaultModule);
        $overrideConfig = $require($this->overrideModule);

        $this->config = array_merge($defaultConfig, $overrideConfig);

        /*
            Are we in a bucket?

            If so we process all info here so we don't slow down the request for standard users.
        */

        $bucketId = $require("php-http/request")->cfg("site/bucket");

        if (!$bucketId) {
            return;
        }

        $bucketConfig = $require($this->overrideModule . ".bucket." . $bucketId);

        $this->config = array_merge($this->config, $bucketConfig);
    }

    public function write() {

        global $require;

        $defaultConfig = $require($this->defaultModule);

        /*
            Build the PHP array module.
        */

        $file = "<?php\n\$module->exports = array(\n";

        foreach ($this->config as $key => $value) {

            /*
                Only store values that re different from the defaults.
            */

            if ($defaultConfig[$key] !== $value) {
                $file .= "    \"" . $key . "\" => \"" . $value . "\",\n";
            }
        }

        $phpstring = substr($file, 0, -2) . "\n);\n";

        $tmpfile = $this->overrideModule . "." . uniqid(true) . ".php";
        $defaultFile = $this->overrideModule . ".php";
        $backupFile = $this->overrideModule . "." . round(microtime(true), 0) . ".php";

        var_dump($tmpfile, $defaultFile, $backupFile);

        /*
            Write the tmp file.
        */

        $bytesWriten = file_put_contents($tmpfile, $phpstring);

        if ($bytesWriten !== strlen($phpstring)) {
            echo "hoobr-config-reader: Error writing file.";
            unlink($tmpfile);
            return false;
        }

        /*
            Copy the default into a backup.
        */

        $status = copy($defaultFile, $backupFile);

        if ($status === false) {
            echo "hoobr-config-reader: Error copying source file.";
            unlink($tmpfile);
            return false;
        }

        /*
            Replace the default with the tmp file.
        */

        $status = rename($tmpfile, $defaultFile);

        if ($status === false) {
            echo "hoobr-config-reader: Error renaming temporary file.";
            unlink($tmpfile);
            return false;
        }

        return true;
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

$module->exports = function ($module, $overrideDir = "", $defaultsDir = "") {
    return new HoobrConfigReader($module, $overrideDir, $defaultsDir);
};
