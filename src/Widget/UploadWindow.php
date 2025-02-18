<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Widget;

use Gm;
use Gm\Helper\Html;
use Gm\Panel\Widget\Form;
use Gm\Panel\Helper\ExtForm;

/**
 * Виджет для формирования интерфейса загрузки файла пакета виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Widget
 * @since 1.0
 */
class UploadWindow extends \Gm\Panel\Widget\EditWindow
{
    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $this->title = '#{upload.title}';
        $this->titleTpl = $this->title;
        $this->width = 470;
        $this->autoHeight = true;
        $this->layout = 'fit';
        $this->resizable = false;
        $this->iconCls = 'g-icon-m_upload';

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form->autoScroll = true;
        $this->form->router->setAll([
            'route' => $this->creator->route('/upload'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'submit' => '{route}/perfom'
            ] 
        ]);
        $this->form->setStateButtons(
            Form::STATE_CUSTOM,
            ExtForm::buttons([
                'help' => ['subject' => 'upload'], 
                'submit' => [
                    'text'    => '#Upload', 
                    'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_upload', 
                    'handler' => 'onFormSubmit'                
                ],
                'cancel'
            ])
        );
        $this->form->items = [
            [
                'xtype'    => 'container',
                'padding'  => 7,
                'defaults' => [
                    'labelAlign' => 'right',
                    'labelWidth' => 110,
                    'width'      => '100%'
                ],
                'items' => [
                    // т.к. параметры ("_csrf", "X-Gjax") не передаются через заголовок, 
                    // то передаём их через метод POST
                    [
                        'xtype' => 'hidden',
                        'name'  => 'X-Gjax',
                        'value' => true
                    ],
                    [
                        'xtype' => 'hidden',
                        'name'  => Gm::$app->request->csrfParamName,
                        'value' => Gm::$app->request->getCsrfTokenFromHeader()
                    ],
                    [
                        'xtype'      => 'filefield',
                        'name'       => 'packageFile',
                        'fieldLabel' => '#File name',
                        'allowBlank' => true
                    ]
                ]
            ],
            [
                'xtype' => 'label',
                'ui'    => 'note',
                'html'  => 
                    $this->creator->t(
                        'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}', 
                        [
                            Html::a(
                                $this->creator->t('(more details)'), 
                                '#', 
                                [
                                    'onclick' => ExtForm::jsAppWidgetLoad('@backend/config/upload')
                                ]
                            )
                        ]
                    )
            ]
        ];
    }
}