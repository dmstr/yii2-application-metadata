<?php

namespace dmstr\helpers;

use Yii;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Provides extended application information
 */
class Metadata
{
    public static function getModules($sorted = true)
    {
        $modules = Yii::$app->getModules();
        if ($sorted) {
            # ksort($modules);
        }
        return $modules;
    }

    public static function getModuleControllers($module = null)
    {
        if ($module === null) {
            $module = \Yii::$app;
        } elseif ($module instanceof Module) {
            //$module = $module;
        } else {
            $module = \Yii::$app->getModule($module);
        }

        $controllers = [];
        foreach (scandir($module->getControllerPath()) AS $i => $name) {
            if (substr($name, 0, 1) == '.') {
                continue;
            }
            #echo $module->getControllerPath();
            $controller    = \yii\helpers\Inflector::camel2id(str_replace('Controller.php', '', $name));
            $route         = ($module->id == 'app') ? '' : '/' . $module->id;
            $c             = Yii::$app->createController($route);
            $controllers[] = [
                'name'    => $controller,
                'module'  => $module->id,
                'route'   => $route . '/' . $controller,
                'url'     => Yii::$app->urlManager->createUrl($route . '/' . $controller),
                'actions' => self::getControllerActions($c[0]),
            ];
        }
        return $controllers;
    }

    public static function getAllControllers()
    {
        $controllers = self::getModuleControllers();
        foreach (\Yii::$app->getModules() AS $id => $module) {
            #var_dump($module);
            $controllers = ArrayHelper::merge($controllers, self::getModuleControllers($id));
        }

        return $controllers;
    }

    /**
     * Returns all available actions of the specified controller.
     * Taken from Yii2 HelpController
     *
     * @param Controller $controller the controller instance
     *
     * @return array all available action IDs.
     */
    public static function getControllerActions($controller)
    {
        #$controller = Yii::$app->createController($controller['name']);
        if (!$controller) {
            return [];
        }
        #if (!is_object($controller)) exit;
        $actions = [];
        foreach ($controller->actions() AS $name => $importedActions) {
            $actions[] = [
                'name'  => $name,
                'route' => Yii::$app->urlManager->createUrl($controller->id . '/' . $name)
            ];
        }
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                $action    = Inflector::camel2id(substr($name, 6), '-', true);
                $actions[] = [
                    'name'  => $action,
                    'route' => Yii::$app->urlManager->createUrl($controller->id . '/' . $action)
                ];
            }
        }
        //sort($actions);
        return $actions;
        #return array_unique($actions);
    }
}
