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
use Gm\Backend\Marketplace\WidgetManager\Widget\InstallWindow;

/**
 * Контроллер установки виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Install extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     * 
     * @return InstallWindow
     */
    public function createWidget(): InstallWindow
    {
        return new InstallWindow();
    }

    /**
     * Действие "complete" завершает установку расширения.
     * 
     * @return Response
     */
    public function completeAction(): Response
    {
        // добавляем шаблон локализации для установки (см. ".widget.php")
        $this->module->addTranslatePattern('install');

        /** @var \Gm\WidgetManager\WidgetManager $manager Менеджер виджетов */
        $manager = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string Идентификатор установки виджета */
        $installId = Gm::$app->request->post('installId');

        /** @var string|array Расшифровка идентификатора установки виджета */
        $decrypt = $manager->decryptInstallId($installId);
        if (is_string($decrypt)) {
            Gm::debug('Install', [
                'method'    => get_class($manager) . '::decryptInstallId()',
                'installId' => $installId
            ]);
            $response
                ->meta->error($decrypt);
            return $response;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$manager->installerExists($decrypt['path'])) {
            Gm::debug('Install', [
                'method'    => get_class($manager) . '::decryptInstallId()',
                'installId' => $installId
            ]);
            $response
                ->meta->error($this->module->t('The widget installer at the specified path "{0}" does not exist', [$decrypt['path']]));
            return $response;
        }
        
        // каждый виджет обязано иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller $installer Установщик виджета */
        $installer = $manager->getInstaller([
            'module'    => $this->module, 
            'namespace' => $decrypt['namespace'],
            'path'      => $decrypt['path'], 
            'installId' => $installId
        ]);

        // если установщик не создан
        if ($installer === null) {
            Gm::debug('Install', [
                'method' => get_class($manager) . '::getInstaller()',
                'error'  => $this->t('Unable to create widget installer'),
                'params' => [
                    'module'    => get_class($this->module),
                    'namespace' => $decrypt['namespace'],
                    'path'      => $decrypt['path'], 
                    'installId' => $installId
                ]
            ]);
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        // устанавливает виджет
        if ($installer->install()) {
            $response
                ->meta
                    ->cmdPopupMsg(
                        $this->module->t('Widget installation "{0}" completed successfully', [$installer->info['name']]),
                        $this->t('Installing'),
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
        // добавляем шаблон локализации для установки (см. ".widget.php")
        $this->module->addTranslatePattern('install');

        /** @var \Gm\WidgetManager\WidgetManager */
        $widgets = Gm::$app->widgets;
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|string Идентификатор установки виджета */
        $installId = Gm::$app->request->post('installId');

        /** @var string|array Расшифровка идентификатора установки виджета */
        $decrypt = $widgets->decryptInstallId($installId);
        if (is_string($decrypt)) {
            Gm::debug('Install', [
                'method'    => get_class($widgets) . '::decryptInstallId()',
                'installId' => $installId
            ]);
            $response
                ->meta->error($decrypt);
            return $response;
        }

        // если виджет не имеет установщика "Installer\Installer.php"
        if (!$widgets->installerExists($decrypt['path'])) {
            Gm::debug('Install', [
                'method' => get_class($widgets) . '::installerExists',
                'error'  => $this->module->t('The widget installer at the specified path "{0}" does not exist', [$decrypt['path']])
            ]);
            $response
                ->meta->error($this->module->t('The widget installer at the specified path "{0}" does not exist', [$decrypt['path']]));
            return $response;
        }

        // каждый виджет обязано иметь установщик, управление установщиком передаётся текущему модулю
        /** @var \Gm\WidgetManager\WidgetInstaller|null $installer Установщик виджета */
        $installer = $widgets->getInstaller([
            'module'    => $this->module, 
            'namespace' => $decrypt['namespace'],
            'path'      => $decrypt['path'], 
            'installId' => $installId
        ]);

        // если установщик не создан
        if ($installer === null) {
            Gm::debug('Install', [
                'method' => get_class($widgets) . '::getInstaller()',
                'error'  => $this->t('Unable to create widget installer'),
                'params' => [
                    'module'    => get_class($this->module),
                    'namespace' => $decrypt['namespace'],
                    'path'      => $decrypt['path'], 
                    'installId' => $installId
                ]
            ]);
            $response
                ->meta->error($this->t('Unable to create widget installer'));
            return $response;
        }

        /** @var null|\Gm\Panel\Widget\BaseWidget|\Gm\View\Widget $widget */
        $widget = $installer->getWidget();
        // если установщик не имеет виджет
        if ($widget === null) {
            /** @var InstallWindow $widget */
            $widget = $this->getWidget();
        }
        $widget->info = $installer->getWidgetInfo();

       // проверка конфигурации устанавливаемого виджета
        if (!$installer->validateInstall()) {
            Gm::debug('Install', [
                'method' => get_class($installer) . '::validateInstall()',
                'error'  => $installer->getError()
            ]);
            $widget->notice = $installer->getError();
        }

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
