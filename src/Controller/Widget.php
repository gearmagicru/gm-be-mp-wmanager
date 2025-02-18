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
use Gm\Filesystem\Filesystem;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Controller\BaseController;
use Gm\Backend\Marketplace\WidgetManager\Widget\InformationTab;

/**
 * Контроллер удаления и демонтажа виджета.
 * 
 * Действия контроллера:
 * - unmount, удаление установленного виджета без удаления его из репозитория;
 * - uninstall, полностью удаление установленного виджета;
 * - update, обновление конфигурации установленных виджетов;
 * - delete, удаление не установленного виджета из репозитория;
 * - info, информация о виджете.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Widget extends BaseController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

    /**
     * Действие "unmount" выполняет удаление установленного виджета без удаления его 
     * из репозитория.
     * 
     * @return Response
     */
    public function unmountAction(): Response
    {
        /** @var \Gm\WidgetManager\WidgetManager */
        $widgets = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        // идентификатор виджета в базе данных
        $widgetId = $request->getPost('id', null, 'int');
        if (empty($widgetId)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', [$widgetId]));
            return $response;
        }

        /** @var null|array Конфигурация установленного виджета */
        $widgetConfig = $widgets->getRegistry()->getInfo($widgetId, true);
        if ($widgetConfig === null) {
            $response
                ->meta->error($this->module->t('Widget with specified id "{0}" not found', [$widgetId]));
            return $response;
        }

        // локализация виджета
        $localization = $widgets->selectName($widgetConfig['rowId']);
        if ($localization) {
            $name = $localization['name'] ?? SYMBOL_NONAME;
        } else {
            $name = $moduleConfig['name'] ?? SYMBOL_NONAME;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$widgets->installerExists($widgetConfig['path'])) {
            $response
                ->meta->error(
                    $this->module->t('The widget installer at the specified path "{0}" does not exist', [$widgetConfig['path']])
                );
            return $response;
        }

        // каждый виджет обязан иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller $installer Установщик виджета */
        $installer = $widgets->getInstaller([
            'module'    => $this->module,
            'namespace' => $widgetConfig['namespace'],
            'path'      => $widgetConfig['path'],
            'widgetId'  => $widgetId
        ]);

        // если не получилось создать установщик
        if ($installer === null) {
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        // демонтируем виджет
        if ($installer->unmount()) {
            $response
                ->meta
                    ->cmdPopupMsg(
                        $this->module->t('Unmounting of widget "{0}" completed successfully', [$name]), 
                        $this->t('Unmounting'), 
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
     * Действие "uninstall" выполняет полностью удаление установленного виджета.
     * 
     * @return Response
     */
    public function uninstallAction():Response
    {
        /** @var \Gm\WidgetManager\WidgetManager */
        $widgets = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        // идентификатор виджета в базе данных
        $widgetId = $request->getPost('id', null, 'int');
        if (empty($widgetId)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array Конфигурация установленного виджета */
        $widgetConfig = $widgets->getRegistry()->getInfo($widgetId, true);
        if ($widgetConfig === null) {
            $response
                ->meta->error($this->module->t('Widget with specified id "{0}" not found', [$widgetId]));
            return $response;
        }

        // локализация виджета
        $localization = $widgets->selectName($widgetConfig['rowId']);
        if ($localization) {
            $name = $localization['name'] ?? SYMBOL_NONAME;
        } else {
            $name = $widgetConfig['name'] ?? SYMBOL_NONAME;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$widgets->installerExists($widgetConfig['path'])) {
            $response
                ->meta->error(
                    $this->module->t('The widget installer at the specified path "{0}" does not exist', [$widgetConfig['path']])
                );
            return $response;
        }

        // каждый виджет обязано иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller $installer Установщик виджета */
        $installer = $widgets->getInstaller([
            'module'    => $this->module,
            'namespace' => $widgetConfig['namespace'],
            'path'      => $widgetConfig['path'],
            'widgetId'  => $widgetId
        ]);

        // если не получилось создать установщик
        if ($installer === null) {
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        // удаление виджета
        if ($installer->uninstall()) {
            $response
                ->meta
                    ->cmdPopupMsg(
                        $this->module->t('Uninstalling of widget "{0}" completed successfully', [$name]), 
                        $this->t('Uninstalling'), 
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
     * Действие "update" обновляет конфигурацию установленных виджетов.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        // обновляет конфигурацию установленных виджетов
        Gm::$app->widgets->update();
        $response
            ->meta->success(
                $this->t('Widgets configuration files are updated'), 
                $this->t('Updating widgets'), 
                'custom', 
                $this->module->getAssetsUrl() . '/images/icon-update-config.svg'
            );
        return $response;
    }

    /**
     * Действие "delete" выполняет удаление не установленного виджета из репозитория.
     * 
     * @return Response
     */
    public function deleteAction(): Response
    {
        /** @var \Gm\WidgetManager\WidgetManager */
        $widgets = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string $installId Идентификатор установки виджета */
        $installId = Gm::$app->request->post('installId');

        /** @var string|array $decrypt Расшифровка идентификатора установки виджета */
        $decrypt = $widgets->decryptInstallId($installId);
        if (is_string($decrypt)) {
            $response
                ->meta->error($decrypt);
            return $response;
        }

        /** @var null|array $installConfig Параметры конфигурации установки виджета */
        $installConfig = $widgets->getConfigInstall($decrypt['path']);
        if (empty($installConfig)) {
            $response
                ->meta->error(
                    $this->module->t('Widget installation configuration file is missing')
                );
            return $response;
        }

        // если виджет установлен
        if ($widgets->getRegistry()->has($installConfig['id'])) {
            $response
                ->meta->error(
                    $this->module->t('It is not possible to remove the widget from the repository because it\'s installed')
                );
            return $response;
        }

        // попытка удаления всех файлов виджета
        if (Filesystem::deleteDirectory(Gm::$app->modulePath . $decrypt['path'])) {
            $response
                ->meta
                    ->cmdPopupMsg(
                        $this->t('Deleting of widget completed successfully'), 
                        $this->t('Deleting'), 
                        'accept'
                    )
                    ->cmdReloadGrid($this->module->viewId('grid'));
        } else {
            $response
                ->meta->error(
                    Gm::t('app', 'Could not perform directory deletion "{0}"', [Gm::$app->modulePath . $decrypt['path']])
                );
        }
        return $response;
    }

    /**
     * Действие "info" возвращает информацию о виджете.
     * 
     * @return Response
     */
    public function infoAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string $widgetId Идентификатор виджета */
        $widgetId = Gm::$app->request->get('id');
        if (empty($widgetId)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var InformationTab $tab */
        $tab = new InformationTab();
        /** @var null|array $widgetInfo*/
        $widgetInfo = $tab->getWidgetInfo($widgetId);

        // если виджет не найден
        if ($widgetInfo === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$widgetId]));
            return $response;
        }

        // панель (Ext.panel Sencha ExtJS)
        $tab->panel->html = $this->getViewManager()->renderPartial('widget-info', $widgetInfo);
        // панель вкладки компонента (Gm.view.tab.Components GmJS)
        $tab->title = $this->module->t('{info.title}', [$widgetInfo['name']]);
        $tab->icon  = Gm::$app->moduleUrl . $widgetInfo['path'] . '/assets/images/icon_small.svg';
        $tab->tooltip = [
            'icon'  => Gm::$app->moduleUrl . $widgetInfo['path'] . '/assets/images/icon.svg',
            'title' => $tab->title,
            'text'  => $widgetInfo['description']
        ];

        $response
            ->setContent($tab->run())
            ->meta
                ->addWidget($tab);
        return $response;
    }
}
