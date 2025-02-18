<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Controller;

use Gm;
use Gm\Panel\Http\Response;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер настройки шорткода виджета.
 * 
 * Действия контроллера:
 * - view, вывод интерфейса настроек шорткода виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class ShortcodeSettings extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
                return Gm::t(BACKEND, "{{$this->actionName} settings action}");

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} settings action}")
                );
        }
    }

    /**
     * Возвращает идентификатор выбранного виджета.
     *
     * @return int
     */
    public function getIdentifier(): int
    {
        return (int) Gm::$app->router->get('id');
    }

    /**
     * Действие "view" выводит интерфейс настроек шорткода виджета.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'Parameter "{0}" not specified', ['id']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        /** @var null|string $tagName Имя тега */
        $tagName = Gm::$app->request->getQuery('name');
        if (empty($tagName)) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'Parameter "{0}" not specified', ['name']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        /** @var null|array $widgetParams Параметры виджета */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        if ($widgetParams === null) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'There is no widget with the specified id "{0}"', ['$id']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        /** @var null|array $install Параметры установки виджета */
        $install = Gm::$app->widgets->getRegistry()->getConfigInstall($id);
        // если нет параметров установки
        if ($install === null) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'There is no widget with the specified id "{0}"', ['$id']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        /** @var array|null $shortcode Параметры указанного шорткода виджета */
        $shortcode = $install['editor']['shortcodes'][$tagName] ?? null;
        if (empty($shortcode)) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'Parameter passed incorrectly "{0}"', ['shortcodes[' . $tagName . ']']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        // если нет настроек шорткода
        if (empty($shortcode['settings'])) {
            return $this->errorResponse(
                GM_MODE_DEV ?
                    Gm::t('app', 'The value for parameter "{0}" is missing', ['shortcodes[settings]']) :
                    $this->module->t('Unable to show widget shortcode settings')
            );
        }

        // для доступа к пространству имён объекта
        Gm::$loader->addPsr4($widgetParams['namespace']  . NS, Gm::$app->modulePath . $widgetParams['path'] . DS . 'src');

        $settingsClass = $widgetParams['namespace'] . NS . $shortcode['settings'];
        if (!class_exists($settingsClass)) {
            return $this->errorResponse(
                $this->module->t('Unable to create widget object "{0}"', [$settingsClass])
            );
        }

        // т.к. виджет самостоятельно не может подкличать свою локализацию, то делает это менеджер виджетов (Extension), 
        // подключая локализации виджета себе
        $category = Gm::$app->translator->getCategory($this->module->id);
        $category->patterns['widget'] = [
            'basePath' => Gm::$app->modulePath . $widgetParams['path'] . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $this->module->addTranslatePattern('widget');

        /** @var object|Gm\Panel\Widget\ShortcodeSettingsWindow $widget Виджет настроек шорткода */
        $widget = Gm::createObject($settingsClass);
        if ($widget instanceof Gm\Panel\Widget\ShortcodeSettingsWindow) {
            $widget->form->controller = 'gm-mp-wmanager-shortcodesettings';
            $widget
                ->setNamespaceJS('Gm.be.mp.wmanager')
                ->addRequire('Gm.be.mp.wmanager.ShortcodeSettingsController');    
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}
