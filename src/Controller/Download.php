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
use Gm\FilePackager\FilePackager;
use Gm\Panel\Controller\BaseController;

/**
 * Контроллер скачивания файла пакета виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Download extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'index';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verb' => [
                'class'    => '\Gm\Filter\VerbFilter',
                'autoInit' => true,
                'actions'  => [
                    ''     => ['POST', 'ajax' => 'GJAX'],
                    'file' => ['GET']
                ]
            ],
            'audit' => [
                'class'    => '\Gm\Panel\Behavior\AuditBehavior',
                'autoInit' => true,
                'allowed'  => '*',
                'enabled'  => $this->enableAudit
            ]
        ];
    }

    /**
     * Действие "index" подготавливает пакет виджета для скачивания.
     * 
     * @return Response
     */
    public function indexAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse(Response::FORMAT_JSONG);
        /** @var \Gm\WidgetManager\WidgetManager Менеджер виджетов */
        $manager = Gm::$app->widgets;

        /** @var null|string $widgetId Идентификатор установленного виджета */
        $widgetId = Gm::$app->request->post('id');
        if (empty($widgetId)) {
            $message = Gm::t('backend', 'Invalid argument "{0}"', ['id']);

            Gm::debug('Error', ['error' => $message]);
            $response
                ->meta->error($message);
            return $response;
        }

        /** @var null|array $params Параметры установленного виджета */
        $params = $manager->getRegistry()->get($widgetId);
        // виджет с указанным идентификатором не установлен
        if ($params === null) {
            $message = $this->module->t('There is no widget with the specified id "{0}"', [$widgetId]);

            Gm::debug('Error', ['error' => $message]);
            $response
                ->meta->error($message);
            return $response;
        }

        /** @var null|array $version Параметры установленного виджета */
        $version = $manager->getVersion($widgetId);
        // виджет с указанным идентификатором не установлен
        if ($version === null) {
            $message = $this->module->t('There is no widget with the specified id "{0}"', [$widgetId]);

            Gm::debug('Error', ['error' => $message]);
            $response
                ->meta->error($message);
            return $response;
        }

        /** @var string $packageName Название файла пакета */
        $packageName = FilePackager::generateFilename($widgetId, $version['version']);
        /** @var FilePackager Файл пакета  */
        $packager = new FilePackager([
            'filename' => Gm::alias('@runtime') . DS . $packageName,
        ]);

        /** @var \Gm\FilePackager\Package $package Пакет */
        $package = $packager->getPackage([
            'format' => 'json',
            'path'   => Gm::alias('@runtime')
        ]);
        $package->id     = $widgetId;
        $package->type   = 'widget';
        $package->author = $version['author'];
        $package->date   = $version['versionDate'];
        $package->name   = 'Widget "' . $version['name'] . '" v' . $version['version'];
        $package->note   = $version['description'];

        // добавление файлов в пакет
        $package->addFiles(Gm::getAlias('@module' . $params['path']), '@module' . $params['path']);

        // проверка и сохранение файла пакета
        if (!$package->save(true)) {
            $message = $package->getError();

            Gm::debug('Error', ['error' => $message]);
            $response
                ->meta->error($message);
            return $response;
        }

        // архивация пакета
        if (!$packager->pack($package)) {
            $message = $package->getError();

            Gm::debug('Error', ['error' => $message]);
            $response
                ->meta->error($message);
            return $response;
        }

        $response
            ->meta
                // всплывающие сообщение
                ->cmdPopupMsg($this->t('The widget package will now be loaded'), $this->t('Downloading'), 'success')
                // загрузка файла
                ->cmdGm('download', ['@backend/marketplace/wmanager/download/file/' . $params['rowId']]);
        return $response;
    }

    /**
     * Действие "file" скачивает файл пакета виджета.
     * 
     * @return Response
     */
    public function fileAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse(Response::FORMAT_RAW);
        /** @var \Gm\WidgetManager\WidgetManager Менеджер виджетов */
        $manager = Gm::$app->widgets;

        /** @var null|int $widgetId Идентификатор установленного виджета */
        $widgetId = (int) Gm::$app->router->get('id');
        if (empty($widgetId)) {
            $message = Gm::t('backend', 'Invalid argument "{0}"', ['id']);

            Gm::debug('Error', ['error' => $message]);
            return $response->setContent($message);
        }

        /** @var null|array $params Параметры установленного виджета */
        $params = $manager->getRegistry()->getAt($widgetId);
        // виджет с указанным идентификатором не установлен
        if ($params === null) {
            $message = $this->module->t('There is no widget with the specified id "{0}"', [$widgetId]);

            Gm::debug('Error', ['error' => $message]);
            return $response->setContent($message);
        }

        /** @var null|array $version Параметры установленного виджета */
        $version = $manager->getVersion($params['id']);
        // виджет с указанным идентификатором не установлен
        if ($version === null) {
            $message = $this->module->t('There is no widget with the specified id "{0}"', [$params['id']]);

            Gm::debug('Error', ['error' => $message]);
            return $response->setContent($message);
        }

        /** @var string $packageName Название файла пакета */
        $filename = Gm::alias('@runtime') . DS . FilePackager::generateFilename($params['id'], $version['version']);
        if (!file_exists($filename)) {
            $message = Gm::t('app', 'File "{0}" not found', [$filename]);

            Gm::debug('Error', ['error' => $message]);
            return $response->setContent($message);
        }

        $response->sendFile($filename);
        return $response;
    }
}
