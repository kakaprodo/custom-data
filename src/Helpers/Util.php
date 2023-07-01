<?php

namespace Kakaprodo\CustomData\Helpers;

class Util
{
    /**
     * Grab the folder name from a given path string
     */
    public static function folderFromPath($path)
    {
        $folderPath = explode('app/', $path);

        return $folderPath[1] ?? '';
    }

    /**
     * the folder name where all action classes reside
     */
    public static function actionFolder($addNameSpace = null)
    {
        $addNameSpace = $addNameSpace ? ("\\" . $addNameSpace) : "";

        return self::folderFromPath(config('custom-data.action_path'))
            . "\\" . config('custom-data.action_folder')
            . $addNameSpace;
    }

    /**
     * the folder name where all custom data classes reside
     */
    public static function dataFolder($addNameSpace = null)
    {
        $addNameSpace = $addNameSpace ? ("\\" . $addNameSpace) : "";

        return self::folderFromPath(config('custom-data.data_path'))
            . "\\" . config('custom-data.data_folder')
            . $addNameSpace;
    }
}
