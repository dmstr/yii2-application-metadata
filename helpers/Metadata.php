<?php

namespace dmstr\helpers;

/**
 * This is just an example.
 */
class Metadata
{

    public static function getModuleControllers($module = null)
    {
        if ($module === null) {
            $module = \Yii::$app;
        } else {
            $module = \Yii::$app->getModule($module);
        }
        foreach (scandir($module->getControllerPath()) AS $i => $name) {
            if (substr($name, 0, 1) == '.') {
                continue;
            }
            $controllers[] = \yii\helpers\Inflector::camel2id(str_replace('Controller.php', '', $name));
        }
        return $controllers;
    }

    public static function getAllControllers()
    {
        foreach (\Yii::$app->getModules() AS $module) {
            $controllers[] = self::getModuleControllers();
        }
        return $controllers;
    }

}
