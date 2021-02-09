<?php

namespace Queue\Queue;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use RuntimeException;

class WorkersNames {

    /**
     * 
     * @return string
     */
    public static function getRamdomName() {
        $name = "";
        $names = self::getNames();
        if (!empty($names)) {
            $name = $names[mt_rand(0, count($names) - 1)];
        }
        return $name;
    }

    /**
     * 
     * @return array
     */
    public static function getNames() {
        $names = [];
        $pluginPath = Plugin::path('Queue');
        $file = $pluginPath . 'src' . DS . 'Queue' . DS . 'names.json';
        if (file_exists($file)) {
            $names_from_file = file_get_contents($file);
            if ($names_from_file !== "") {
                $names = json_decode($names_from_file, true);
            }
        }
        return $names;
    }

}
