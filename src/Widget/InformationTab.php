<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Widget;

use Gm;
use Gm\Panel\Widget\Widget;
use Gm\Panel\Widget\TabWidget;

/**
 * Виджет для формирования вкладки c информацией о виджете.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Widget
 * @since 1.0
 */
class InformationTab extends TabWidget
{
    /**
     * Панель вкладки (Ext.panel.Panel Sencha ExtJS).
     * 
     * @var Widget
     */
    public Widget $panel;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель вкладки (Ext.panel.Panel Sencha ExtJS)
        $this->panel = new Widget([
            'bodyCls'    => 'g-widget-info__body',
            'scrollable' => true
        ], $this);

        $this->bodyPadding = 0;
        $this->id    = 'tab-info';
        $this->cls   = 'g-module-info g-panel_background';
        $this->items = [$this->panel];
    }

    /**
     * Возвращает информацию о виджете.
     * 
     * @param string $widgetId Идентификатор виджета.
     * 
     * @return array|null
     */
    public function getWidgetInfo(string $widgetId): ?array
    {
        /** @var \Gm\WidgetManager\WidgetManager $widgets Менеджер виджетов */
        $widgets = Gm::$app->widgets;
        /** @var \Gm\WidgetManager\WidgetRegistry $registry Установленные виджеты */
        $registry = $widgets->getRegistry();

        /** @var array|null $info Информация о виджете */
        $info = $registry->getInfo($widgetId, true);
        if ($info === null) {
            return null;
        }

        /* Локализация виджета для определения имени и описания */
        $name = $widgets->selectName($info['rowId']);
        // если есть перевод
        if ($name) {
            $info['name'] = $name['name'];
            $info['description'] = $name['description'];
        }

        /* Раздел "Модуль установлен" */
        // дата установки модуля
        $info['createdDate'] = null;
        // пользователь устанавливавший модуль
        $info['createdUser'] = null;
        // модуль из базы данных
        $widget = $widgets->selectOne($widgetId, true);
        if ($widget) {
            if ($widget['createdDate']) {
                $info['createdDate'] = Gm::$app->formatter->toDateTime($widget['createdDate']);
            }
            if ($widget['createdUser']) {
                $userId = (int) $widget['createdUser'];
                /** @var \Gm\Panel\User\UserIdentity $user */
                $user = Gm::userIdentity();
                /** @var \Gm\Panel\User\UserProfile $profile */
                $profile = Gm::userIdentity()->getProfile();
                // переопределяем
                $info['createdUser'] = [
                    'user'    => $user->findOne(['id' => $userId ]),
                    'profile' => $profile->findOne(['user_id' => $userId])
                ];
            }
        }
        return $info;
    }
}
