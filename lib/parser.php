<?php
namespace php_require\hoobr_config_reader;

class HoobrConfigReader {

    private $config = array();

    public $moduleName = null;

    public $defaultModule = null;

    public $overrideModule = null;

    public function __construct($module, $overrideDir, $defaultsDir) {

        global $require;

        $pathlib = $require("php-path");

        $moduleName = $module;

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

        $bucketConfig = $require($this->makeBucketModulePath($bucketId));

        $this->config = array_merge($this->config, $bucketConfig);
    }

    public function write() {

        global $require;

        $defaultConfig = $require($this->defaultModule);

        return $this->writeDelta($this->config, $defaultConfig, $this->overrideModule);
    }

    public function writeBucket($id, $config) {
        return $this->writeDelta($config, $this->get(), $this->makeBucketModulePath($id));
    }

    public function writeDelta($config, $base, $filepath) {

        /*
            Build the PHP array module.
        */

        $file = "<?php\n\$module->exports = array(\n\n";

        foreach ($config as $key => $value) {

            /*
                Only store values that re different from the defaults.
            */

            if ($base[$key] !== $value) {
                $file .= "    \"" . $key . "\" => \"" . $value . "\",\n";
            }
        }

        $phpstring = substr($file, 0, -2) . "\n\n);\n";

        $tmpfile = $filepath . "." . uniqid(true) . ".php";
        $defaultFile = $filepath . ".php";
        $backupFile = $filepath . "." . round(microtime(true), 0) . ".php";

        // var_dump($tmpfile, $defaultFile, $backupFile);

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
            Copy the default into a backup (but only if it exists).
        */

        $status = true;

        if (is_file($defaultFile)) {
            $status = copy($defaultFile, $backupFile);
        }

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

    /*
        Deletes configuration files up to the $history count.

        Set history to 0 to delete all files.
    */

    public function delete($history = 4) {
        return $this->deleteFiles($this->overrideModule, $history, "bucket");
    }

    public function deleteBucket($id, $history = 4) {
        return $this->deleteFiles($this->makeBucketModulePath($id), $history);
    }

    public function deleteFiles($filepath, $history, $skip = "") {

        global $require;

        $pathlib = $require("php-path");

        $dir = dirname($filepath);

        $files = scandir($dir);

        $configs = array();

        $match = basename($filepath);

        foreach ($files as $file) {

            if (!in_array($file, array(".", ".."))) {

                if (strpos($file, $match) === 0 && strpos($file, $skip) === false) {
                    array_push($configs, $file);
                }
            }
        }

        if (count($configs) < $history) {
            return true;
        }

        sort($configs); // make sure

        // The last item is always the current config so move it to the top.
        // array_unshift($configs, array_pop($configs));
        $delete = array_slice($configs, 0, count($configs) - $history);

        $status = true;

        // now delete each file in the $delete array.
        foreach ($delete as $file) {
            if (!unlink($pathlib->join($dir, $file))) {
                $status = false;
            }
        }

        return $status;
    }

    public function makeBucketModulePath($bucketId) {
        return $this->overrideModule . ".bucket." . $bucketId;
    }

    public function listBucketIds() {

        $ids = array();

        $dir = dirname($this->overrideModule);

        $files = scandir($dir);

        foreach ($files as $file) {

            if (!in_array($file, array(".", "..")) && strpos($file, $this->moduleName . ".bucket.") !== false) {

                /*
                    Here it is assumed that the bucket file name is in the form;

                    [module].bucket.[bucketId].php
                */

                $bucketId = explode(".", $file);

                if (count($bucketId) === 4) {
                    array_push($ids, $bucketId[2]);
                }
            }
        }

        return $ids;
    }
}

$module->exports = function ($module, $overrideDir = "", $defaultsDir = "") {
    return new HoobrConfigReader($module, $overrideDir, $defaultsDir);
};
