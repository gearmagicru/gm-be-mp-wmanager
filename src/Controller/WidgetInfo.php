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
use Gm\Panel\Controller\BaseController;
use Gm\Backend\Marketplace\WidgetManager\Widget\InformationTab;

/**
 * Контроллер информации о виджете.
 * 
 * Действия контроллера:
 * - index, информация о виджете;
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class WidgetInfo extends BaseController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

    /**
     * Действие "info" возвращает информацию о виджете.
     * 
     * @return Response
     */
    public function indexAction(): Response
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
