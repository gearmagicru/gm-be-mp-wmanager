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
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Widget\Form as WForm;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер редактирования виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Form extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

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
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var null|array $moduleInfo Информация о виджете */
        $widgetInfo = null;
        // идентификатор виджета
        if ($identifier = $this->getIdentifier()) {
            $widgetInfo = Gm::$app->widgets->getRegistry()->getInfo($identifier);
        }

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        if ($widgetInfo) {
            $window->icon = $widgetInfo['icon'];
        }
        $window->ui = 'install';
        $window->title = $this->t('{form.title}');
        $window->titleTpl = sprintf('%s <span>%s</span>', $this->t('{form.title}'),  $this->t('{form.subtitle}'));
        $window->width = 520;
        $window->autoHeight = true;
        $window->layout = 'fit';
        $window->resizable = false;

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->autoScroll = true;
        $window->form->router->route = $this->module->route('/form');
        // определяем свой набор кнопок
        $window->form->setStateButtons(WForm::STATE_UPDATE, ['help' => ['subject' => 'edit'], 'save', 'cancel']);
        $viewFile = 'form';
        // если есть информация о виджете
        if ($viewFile) {
            // если виджет системный (настройки нельзя менять)
            $lock = (bool) ($widgetInfo['lock'] ?? false);
            $viewFile = $lock ? 'form-lock' : $viewFile;
        }
        // подстановка переменных в шаблон
        $window->form->loadJSONFile($viewFile, 'items', [
            // языковая панель вкладок с полями
            '@languageTabs' => ExtForm::languageTabs(function ($tag) {
                return [
                    [
                        'xtype'      => 'textfield',
                        'fieldLabel' => '#Name',
                        'name'       => $tag ? 'locale[' . $tag. '][name]' : 'name',
                        'labelWidth' => 105,
                        'anchor'     => '100%',
                        'width'      => 447,
                        'maxLength'  => 255,
                        'allowBlank' => false
                    ],
                    [
                        'xtype'      => 'textfield',
                        'fieldLabel' => '#Description',
                        'name'       => $tag ? 'locale[' . $tag. '][description]' : 'description',
                        'labelWidth' => 105,
                        'anchor'     => '100%',
                        'width'      => 447,
                        'maxLength'  => 255,
                        'allowBlank' => false
                    ],
                ];
            }, true, [], ['layout' => 'anchor'])
        ]);
        return $window;
    }
}