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
use Gm\Backend\Marketplace\WidgetManager\Widget\UpdateWindow;

/**
 * Контроллер обновления виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Update extends FormController
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
    public function createWidget(): UpdateWindow
    {
        /** @var UpdateWindow $window Окно обновления виджета (Ext.window.Window Sencha ExtJS) */
        $window = new UpdateWindow();
        $window->title = $this->t('{update.title}');
        // шаги обновления виджета: ['заголовок', выполнен]
        $window->steps->extract  = [$this->t('Extract files from the update package'), true];
        $window->steps->copy     = [$this->t('Copying files to the widget repository'), true];
        $window->steps->validate = [$this->t('Checking widget files and configuration'), true];
        $window->steps->update   = [$this->t('Update widget data'), false];
        $window->steps->register = [$this->t('Widget registry update'), false];

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->router['route'] = $this->module->route('/update');
        return $window;
    }

    /**
     * Действие "complete" завершает обновление виджета.
     * 
     * @return Response
     */
    public function completeAction(): Response
    {
        // добавляем шаблон локализации для обновления (см. ".extension.php")
        $this->module->addTranslatePattern('update');

        /** @var \Gm\WidgetManager\WidgetManager Менеджер виджетов */
        $manager = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string $widgetId Идентификатор установленного виджета */
        $widgetId = Gm::$app->request->post('id');
        if (empty($widgetId)) {
            $response
                ->meta->error(Gm::t('backend', 'Invalid argument "{0}"', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams Параметры установленного виджета */
        $widgetParams = $manager->getRegistry()->get($widgetId);
        // виджет с указанным идентификатором не установлен
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$widgetId]));
            return $response;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$manager->installerExists($widgetParams['path'])) {
            $response
                ->meta->error($this->module->t('The widget installer at the specified path "{0}" does not exist', [$widgetParams['path']]));
            return $response;
        }

        // каждый виджет обязан иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller $installer Установщик виджета */
        $installer = $manager->getInstaller([
            'module'    => $this->module, 
            'namespace' => $widgetParams['namespace'],
            'path'      => $widgetParams['path'],
        ]);

        // если установщик не создан
        if ($installer === null) {
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        // обновляет виджет
        if ($installer->update()) {
            $info = $installer->getWidgetInfo();
            $response
                ->meta
                    ->cmdPopupMsg(
                        $this->module->t('Update of widget "{0}" completed successfully', [$info ? $info['name'] : SYMBOL_NONAME]),
                        $this->t('Updating'),
                        'accept'
                    )
                    ->cmdReloadGrid($this->module->viewId('grid'));
        } else {
            $response
                ->meta->error($installer->getError());
        }
        return $response;
    }

    /**
     * Действие "view" выводит интерфейс установщика виджета.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        // добавляем шаблон локализации для обновления (см. ".extension.php")
        $this->module->addTranslatePattern('update');

        /** @var \Gm\WidgetManager\WidgetManager Менеджер виджетов */
        $manager = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string $widgetId Идентификатор установленного виджета */
        $widgetId = Gm::$app->request->post('id');
        if (empty($widgetId)) {
            $response
                ->meta->error(Gm::t('backend', 'Invalid argument "{0}"', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams Параметры установленного виджета */
        $widgetParams = $manager->getRegistry()->get($widgetId);
        // виджет с указанным идентификатором не установлен
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$widgetId]));
            return $response;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$manager->installerExists($widgetParams['path'])) {
            $response
                ->meta->error($this->module->t('The widget installer at the specified path "{0}" does not exist', [$widgetParams['path']]));
            return $response;
        }

        // каждый виджет обязан иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller $installer Установщик виджета */
        $installer = $manager->getInstaller([
            'module'    => $this->module, 
            'namespace' => $widgetParams['namespace'],
            'path'      => $widgetParams['path']
        ]);

        // если установщик не создан
        if ($installer === null) {
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        // проверка конфигурации обновляемого виджета
        if (!$installer->validateUpdate()) {
            $response
                ->meta->error(
                    $this->module->t('Unable to update the widget, there were errors in the files of the new version of the widget')
                    . '<br>' . $installer->getError()
                );
            return $response;
        }

        /** @var UpdateWindow $widget */
        $widget = $installer->getWidget();
        // если установщик не имеет виджет
        if ($widget === null) {
            $widget = $this->getWidget();
        }
        $widget->info = $installer->getWidgetInfo();

        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}
