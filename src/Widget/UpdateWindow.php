<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Widget;

use Gm\Helper\Html;
use Gm\Stdlib\Collection;
use Gm\Panel\Widget\Form;
use Gm\Panel\Widget\Window;

/**
 * Виджет для формирования интерфейса обновления виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Widget
 * @since 1.0
 */
class UpdateWindow extends Window
{
    /**
     * Виджет для формирования интерфейса формы.
     * 
     * @var Form
     */
    public Form $form;

    /**
     * Маршрут заврешения обновления виджета.
     * 
     * @var string
     */
    public string $route = '';

    /**
     * Шаги обновления виджета.
     * 
     * Указываются в виде пар: `[['название', выполнен], ['название', не выполнен], ....]`.
     * 
     * @var Collection
     */
    public Collection $steps;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.window.Window',
        'Gm.view.form.Panel'
    ];

    /**
     * {@inheritdoc}
     */
    public array $css = ['/update.css'];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->steps = new Collection();

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form = new Form([
            'id'     => 'update-form', // => gm-mp-wmanager-update-form
            'router' => [
                'route' => $this->route,
                'state' => Form::STATE_CUSTOM,
                'rules' => ['complete' => '{route}/complete'] 
            ],
            'bodyPadding' => 0
        ], $this);

        // панель кнопок формы (Ext.form.Panel.buttons Sencha ExtJS)
        $this->form->setStateButtons(Form::STATE_CUSTOM, [
            'action' => [
                'iconCls'     => 'g-icon-svg g-icon_size_14 g-icon-m_reset',
                'text'        => '#Complete update',
                'handlerArgs' => ['routeRule' => 'complete']
            ],
            'cancel'
        ]);

        // окно (Ext.window.Window Sencha ExtJS)
        $this->id        = 'update'; // => g-wmanager-update
        $this->ui        = 'light';
        $this->cls       = 'gm-mp-wmanager-update';
        $this->iconCls   = 'g-icon-svg g-icon-m_color_default g-icon_size_14 g-icon-m_reset';
        $this->width     = 650;
        $this->height    = 360;
        $this->layout    = 'fit';
        $this->padding   = 0;
        $this->resizable = false;
        $this->items     = [$this->form];
        $this->responsiveConfig = [
            'height < 360' => ['height' => '99%'],
            'width < 650' => ['width' => '99%'],
        ];
    }

    /**
     * Возвращает заголовок окна.
     * 
     * @return array
     */
    protected function renderHeader(): array
    {
        return [
            'xtype' => 'displayfield',
            'cls'   => 'g-form__display__header g-form__display__header_icon',
            'width' => '100%',
            'value' => Html::tags([
                Html::img($this->info['icon'] ?? '', ['class' => 'g-icon g-icon_size_32'], false),
                Html::tag('div', $this->info['name'] ?? '', ['class' => 'g-form__display__text']),
                Html::tag('div', $this->info['description'] ?? '', ['class' => 'g-form__display__subtext'])
            ])
        ];
    }

    /**
     * Возвращает название окна.
     * 
     * @return array
     */
    public function renderTitle(): string
    {
        return $this->title ? sprintf($this->title, $this->info['name'] ?? '') : '';
    }

    /**
     * Возвращает шаги обновления виджета.
     * 
     * @return array
     */
    public function renderSteps(): array
    {
        $html = '';
        foreach ($this->steps as $step) {
            $title  = $step[0] ?? '';
            $active = ($step[1] ?? true) ? ' active' : '';
            $html .= '<div class="gm-mp-wmanager-update__step' . $active .'"><i class="fal fa-check"></i> ' . $title . '</div>';
        }
        return [
            'xtype' => 'container',
            'cls'   => 'gm-mp-wmanager-update__steps',
            'html'  => $html
        ];
    }

    /**
     * Возвращает элементы формы окна.
     * 
     * @return array
     */
    public function renderFormItems(): array
    {
        return [
            $this->renderHeader(),
            $this->renderSteps(),
            [
                'xtype' => 'container',
                'cls'   => 'gm-mp-wmanager-update__notice',
                'html'  => '#{update.notice}'
            ],
            [
                'xtype' => 'hidden',
                'name'  => 'id',
                'value' => $this->info['install']['id'] ?? null
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        parent::beforeRender();

        $this->form->items = $this->renderFormItems();
        $this->title = $this->renderTitle();
        return true;
    }
}
