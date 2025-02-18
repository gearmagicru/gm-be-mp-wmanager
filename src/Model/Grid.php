<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Model;

use Gm;
use Gm\WidgetManager\WidgetManager;
use Gm\Panel\Data\Model\ArrayGridModel;

/**
 * Модель данных вывода сетки установленных и устанавливаемых виджетов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Model
 * @since 1.0
 */
class Grid extends ArrayGridModel
{
    /**
     * Менеджер виджетов.
     *
     * @see Grid::buildQuery()
     * 
     * @var WidgetManager
     */
    protected WidgetManager $widgets;

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'fields' => [
                ['id'], // уникальный идентификатор записи в базе данных
                ['lock'], // системность
                ['widgetId'], // уникальный идентификатор виджета
                ['widgetUse'], // назначение
                ['path'], // каталог виджета
                ['icon'], // значок виджета
                ['enabled'], // доступность
                ['name'], // имя виджета
                ['description'], // описание виджета
                ['namespace'], // пространство имён
                ['version'], // номер версии
                ['versionAuthor'], // автор версии
                ['versionDate'], // дата версии
                ['details'], // подробная информации о версии виджета
                ['infoUrl'], // маршрут к получению информации о виджете
                ['settingsUrl'], // маршрут к настройкам виджета
                ['status'], // статус виджета: установлен (1), не установлен (0)
                ['clsCellLock'], // CSS-класс строки таблицы блокировки виджета
                ['rowCls'], // стиль строки
                ['installId'], // идентификатор установки виджета
            ],
            'filter' => [
                'type' => ['operator' => '='],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_DELETE, function ($someRecords, $result, $message) {
                // обновление конфигурации установленных виджетов
                Gm::$app->widgets->update();
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\GridController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            })
            ->on(self::EVENT_AFTER_SET_FILTER, function ($filter) {
                /** @var \Gm\Panel\Controller\GridController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            });
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function buildQuery($builder): array
    {
        // менеджер виджетов
        $this->widgets = Gm::$app->widgets;

        /** @var \Gm\WidgetManager\WidgetRegistry $installed Установленные виджеты */
        $installed = $this->widgets->getRegistry();
        /** @var \Gm\WidgetManager\WidgetRepository $repository Репозиторий виджетов */
        $repository = $this->widgets->getRepository();

        // вид фильтра
        $type = $this->directFilter ? $this->directFilter['type']['value'] ?? '' : 'installed';
        switch($type) {
            // все виджеты (установленные + не установленные)
            case 'all':
                return array_merge(
                    $installed->getListInfo(true, false, 'rowId', ['icon' => true, 'version' => true]),
                    $repository->find('Widget', 'nonInstalled', ['icon' => true, 'version' => true, 'name' => true])
                );

                // установленные виджеты
            case 'installed':
                return $installed->getListInfo(true, false, 'rowId', ['icon' => true, 'version' => true]);

                // не установленные виджеты
            case 'nonInstalled':
                return $repository->find('Widget', 'nonInstalled', ['icon' => true, 'version' => true, 'name' => true]);
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFetchRow($row, $rowKey): array
    {
        $details      = ''; // подробная информации о версии виджета
        $settingsUrl  = '::disabled'; // маршрут к настройкам виджета
        $infoUrl      = '::disabled'; // маршрут к получению информации о виджете
        //var_dump($version);
        $installId    = ''; // идентификатор установки виджета
        $namespace    = $row['namespace'] ?? '';  // пространство имён виджета
        $status       = ($row['rowId'] ?? 0) ? 1 : 0; // статус виджета
        $use          = $row['use'] ?? ''; // назначение модуля: BACKEND, FRONTEND
        $popupMenuItems = [[3, 'disabled'], [2, 'disabled']]; // контекстное меню записи
        // версия виджета
        if (empty($row['version'])) {
            $version = ['version' => '', 'versionDate' => '', 'author' => '']; 
            $verDate   = '';
            $verAuthor = '';
            $verNumber = '';
        } else {
            $version = $row['version'];
            $verDate   = $version['versionDate'] ? Gm::$app->formatter->toDate($version['versionDate']) : '';
            $verAuthor = $version['author'] ?? '';
            $verNumber = $version['version'] ?? '';
        }

        // Определение версии виджета
        if ($verNumber)
            $details = $verDate ? $verNumber . ' / ' . $verDate : $verNumber;
        else
            $details = $verDate ? $this->t('from') . ' ' . $verDate :  $this->t('unknow');

        /* Виджет установлен */
        if ($status === 1) {
            $id       = $row['rowId']; // уникальный идентификатор записи в базе данных
            $widgetId = $row['id']; // уникальный идентификатор виджета
            $path     = $row['path']; // каталог виджета
            $icon     = $row['icon']; // значок виджета
            $enabled  = (int) $row['enabled']; // доступность
            $name     = $row['name']; // имя виджета
            $desc     = $row['description']; // описание виджета
            $lock     = $row['lock']; // системность
            $rowCls   = 'gm-mp-wmanager-grid-row_installed'; // стиль строки
            // маршрут к настройкам виджета
            if ($row['hasSettings']) {
                $settingsUrl = '@backend/marketplace/wmanager/wsettings/view/' . $id;
                $popupMenuItems[1][1] = 'enabled';
            }
            // маршрут к получению информации о виджете
            $infoUrl = '@backend/marketplace/wmanager/winfo?id=' . $widgetId;
            $popupMenuItems[0][1] = 'enabled';
            /* Виджет не установлено */
        } else {
            $id        = uniqid(); // уникальный идентификатор записи в базе данных
            $widgetId  = $row['id']; // уникальный идентификатор виджета
            $path      = $row['path'] ?? ''; // каталог виджета
            $icon      = $row['icon']; // значок виджета
            $enabled   = -1; // доступность (скрыть)
            $name      = $row['name']; // имя виджета
            $desc      = $row['description']; // описание виджета
            $lock      = false; // системность
            $rowCls    = 'gm-mp-wmanager-grid-row_notinstalled'; // стиль строки
            $installId = $this->widgets->encryptInstallId($path, $namespace);
        }

        return [
            'id'             => $id, // уникальный идентификатор записи в базе данных
            'lock'           => $lock, // системность
            'widgetId'       => $widgetId, // уникальный идентификатор виджета
            'widgetUse'      => $use ? $this->t($use) : $this->t('unknow'), // назначение
            'path'           => $path, // каталог виджета
            'icon'           => $icon, // значок виджета
            'enabled'        => $enabled, // доступность
            'name'           => $name, // имя виджета
            'description'    => $desc, // описание виджета
            'namespace'      => $namespace, // пространство имён
            'version'        => $verNumber, // номер версии
            'versionAuthor'  => $verAuthor, // автор версии
            'versionDate'    => $verDate, // дата версии
            'details'        => $details, // подробная информации о версии виджета
            'infoUrl'        => $infoUrl, // маршрут к получению информации о виджете
            'settingsUrl'    => $settingsUrl, // маршрут к настройкам виджета
            'status'         => $status, // статус виджета: установлен (1), не установлен (0)
            'clsCellLock'    => $lock ? 'g-cell-lock' : '', // CSS-класс строки таблицы блокировки виджета
            'popupMenuTitle' => $name, // заголовок контекстного меню записи
            'popupMenuItems' => $popupMenuItems, // доступ к элементам контекстного меню записи
            'rowCls'         => $rowCls, // стиль строки
            'installId'      => $installId, // идентификатор установки виджета
        ];
    }
}
