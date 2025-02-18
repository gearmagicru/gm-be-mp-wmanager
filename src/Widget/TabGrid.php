<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Widget;

use Gm\Panel\Helper\ExtGrid;
use Gm\Panel\Helper\HtmlGrid;
use Gm\Panel\Helper\HtmlNavigator as HtmlNav;

/**
 * Виджет для формирования интерфейса вкладки с сеткой данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package  Gm\Backend\Marketplace\WidgetManager\Widget
 * @since 1.0
 */
class TabGrid extends \Gm\Panel\Widget\TabGrid
{
    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // столбцы (Gm.view.grid.Grid.columns GmJS)
        $this->grid->columns = [
            ExtGrid::columnNumberer(),
            ExtGrid::columnAction(),
            [
                'xtype'     => 'templatecolumn',
                'text'      => '#Name',
                'dataIndex' => 'name',
                'tpl'       => HtmlGrid::tag(
                    'div',
                    [
                        HtmlGrid::tag(
                            'div', 
                            '', 
                            [
                                'class' => 'gm-mp-wmanager-grid-cell-i__icon', 
                                'style' => 'background-image:url({icon})'
                            ]
                        ),
                        HtmlGrid::tag(
                            'div', 
                            '{name}', 
                            ['class' => 'gm-mp-wmanager-grid-cell-i__title']
                        ),
                        HtmlGrid::tag(
                            'div', 
                            '{description}', 
                            ['class' => 'gm-mp-wmanager-grid-cell-i__desc']
                        ),
                        HtmlGrid::tag(
                            'div', 
                            $this->creator->t('Version') . ': <span>{details}</span>', 
                            ['class' => 'gm-mp-wmanager-grid-cell-i__ver']
                        ),
                    ],
                    ['class' => 'gm-mp-wmanager-grid-cell-i gm-mp-wmanager-grid-cell-i_offset {clsCellLock}']
                ),
                'cellTip'   => '{description}',
                'filter'    => ['type' => 'string'],
                'width'     => 400
            ],
            [
                'text'      => '#Widget id',
                'dataIndex' => 'widgetId',
                'cellTip'   => '{widgetId}',
                'filter'    => ['type' => 'string'],
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'width'     => 150
            ],
            [
                'text'      => '#Path',
                'dataIndex' => 'path',
                'cellTip'   => '{path}',
                'filter'    => ['type' => 'string'],
                'hidden'    => true,
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'width'     => 150
            ],
            [
                'text'      => '#Author',
                'dataIndex' => 'versionAuthor',
                'cellTip'   => '{versionAuthor}',
                'hidden'    => true,
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'width'     => 150
            ],
            [
                'text'      => '#Version',
                'dataIndex' => 'version',
                'cellTip'   => '{version}',
                'sortable'  => true,
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'width'     => 90
            ],
            [
                'text'      => '#Use',
                'dataIndex' => 'widgetUse',
                'cellTip'   => '{widgetUse}',
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'width'     => 140
            ],
            [
                'xtype' => 'g-gridcolumn-control',
                'width' => 90,
                'tdCls' => 'gm-mp-wmanager-grid-td_offset',
                'items' => [
                    [
                        'iconCls'   => 'g-icon-svg g-icon_size_16 g-icon-m_wrench g-icon-m_color_default g-icon-m_is-hover',
                        'dataIndex' => 'settingsUrl',
                        'tooltip'   => '#Widget settings',
                        'handler'   => 'loadWidgetFromCell'
                    ],
                    [
                        'iconCls'   => 'g-icon-svg g-icon_size_16 g-icon-m_info-circle g-icon-m_color_default g-icon-m_is-hover',
                        'dataIndex' => 'infoUrl',
                        'tooltip'   => '#Widget info',
                        'handler'   => 'loadWidgetFromCell'
                    ]
                ]
            ],
            [
                'text'      => ExtGrid::columnIcon('g-icon-m_unlock', 'svg'),
                'tooltip'   => '#Widget enabled',
                'xtype'     => 'g-gridcolumn-switch',
                'collectData' =>['name', 'widgetId'],
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'dataIndex' => 'enabled'
            ],
            [
                'xtype'    => 'templatecolumn',
                'text'     => '#Status',
                'sortable' => true,
                'width'    => 120,
                'align'    => 'center',
                'tdCls'     => 'gm-mp-wmanager-grid-td_offset',
                'tpl'      => HtmlGrid::tplSwitch(
                    [
                        [
                            HtmlGrid::tag(
                                'span', 
                                $this->creator->t('not installed'), 
                                ['class' => 'gm-mp-wmanager__status gm-mp-wmanager__status_not-installed']
                            ),
                            '0'
                        ],
                        [
                            HtmlGrid::tag(
                                'span', 
                                $this->creator->t('installed'), 
                                ['class' => 'gm-mp-wmanager__status gm-mp-wmanager__status_installed']
                            ),
                            '1'
                        ]
                    ],
                    'status'
                ),
                'dataIndex' => 'status'
            ],
        ];

        // панель инструментов (Gm.view.grid.Grid.tbar GmJS)
        $this->grid->tbar = [
            'padding' => 1,
            'items'   => ExtGrid::buttonGroups([
                'edit' => [
                    'items' => [
                        // инструмент "Установить" (Install)
                        ExtGrid::button([
                            'xtype'         => 'gm-mp-wmanager-button-install',
                            'iconCls'       => 'g-icon-svg gm-mp-wmanager__icon-install',
                            'text'          => '#Install',
                            'tooltip'       => '#Widget install',
                            'msgMustSelect' => '#You need to select a widget'
                        ]),
                        // инструмент "Удалить" (Uninstall)
                        ExtGrid::button([
                            'xtype'         => 'gm-mp-wmanager-button-uninstall',
                            'iconCls'       => 'g-icon-svg gm-mp-wmanager__icon-uninstall',
                            'text'          => '#Uninstall',
                            'tooltip'       => '#Completely delete an installed widget',
                            'msgConfirm'    => '#Are you sure you want to completely delete the installed widget?',
                            'msgMustSelect' => '#You need to select a widget',
                            'handler'       => 'onSendData',
                            'handlerArgs'   => ['route' => $this->creator->route('/widget/uninstall')]
                        ]),
                        '-',
                        // инструмент "Удалить" (Delete)
                        ExtGrid::button([
                            'xtype'         => 'gm-mp-wmanager-button-delete',
                            'iconCls'       => 'g-icon-svg gm-mp-wmanager__icon-delete',
                            'text'          => '#Delete',
                            'tooltip'       => '#Delete an uninstalled widget from the repository',
                            'msgConfirm'    => '#Are you sure you want to delete the uninstalled widget from the repository?',
                            'msgMustSelect' => '#You need to select a widget'
                        ]),
                        // инструмент "Демонтаж" (Unmount)
                        ExtGrid::button([
                            'xtype'         => 'gm-mp-wmanager-button-unmount',
                            'iconCls'       => 'g-icon-svg gm-mp-wmanager__icon-unmount',
                            'text'          => '#Unmount',
                            'tooltip'       => '#Delete an installed widget without removing it from the repository',
                            'msgConfirm'    => '#Are you sure you want to remove the installed widget without removing it from the repository?',
                            'msgMustSelect' => '#You need to select a widget',
                            'handler'       => 'onSendData',
                            'handlerArgs'   => ['route' => $this->creator->route('/widget/unmount')]
                        ]),
                        '-',
                        // инструмент "Скачать" (Download)
                        ExtGrid::button([
                            'xtype'         => 'gm-mp-wmanager-button-download',
                            'iconCls'       => 'g-icon-svg gm-mp-wmanager__icon-download',
                            'text'          => '#Download',
                            'tooltip'       => '#Downloads widget package file',
                            'msgMustSelect' => '#You need to select a widget'
                        ]),
                        // инструмент "Загрузить" (Upload)
                        ExtGrid::button([
                            'iconCls'     => 'g-icon-svg gm-mp-wmanager__icon-upload',
                            'text'        => '#Upload',
                            'tooltip'     => '#Uploads widget package file',
                            'handler'     => 'loadWidget',
                            'handlerArgs' => ['route' => $this->creator->route('/upload')]
                        ]),
                        '-',
                        'edit',
                        'refresh',
                        // инструмент "Обновить" (Update)
                        ExtGrid::button([
                            'text'        => '#Update',
                            'tooltip'     => '#Update configurations of installed widgets',
                            'iconCls'     => 'g-icon-svg gm-mp-wmanager__icon-update-config',
                            'handler'     => 'onSendData',
                            'handlerArgs' => ['route' => $this->creator->route('/widget/update')]
                        ])
                    ]
                ],
                'columns',
                 // группа инструментов "Поиск"
                 'search' => [
                    'items' => [
                        'help',
                        'search',
                        // инструмент "Фильтр"
                        'filter' => [
                            'form' => [
                                'cls'      => 'g-popupform-filter',
                                'width'    => 400,
                                'height'   => 'auto',
                                'action'   => $this->creator->route('/grid/filter', true),
                                'defaults' => ['labelWidth' => 100],
                                'items'    => [
                                    [
                                        'xtype'      => 'radio',
                                        'boxLabel'   => '#All',
                                        'name'       => 'type',
                                        'inputValue' => 'all',
                                    ],
                                    [
                                        'xtype'      => 'radio',
                                        'boxLabel'   => '#Installed',
                                        'name'       => 'type',
                                        'inputValue' => 'installed',
                                        'checked'    => true
                                    ],
                                    [
                                        'xtype'      => 'radio',
                                        'boxLabel'   => '#None installed',
                                        'name'       => 'type',
                                        'inputValue' => 'nonInstalled',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], [
                'route' => $this->creator->route()
            ])
        ];

        // контекстное меню записи (Gm.view.grid.Grid.popupMenu GmJS)
        $this->grid->popupMenu = [
            'items' => [
                [
                    'text'    => '#Edit record',
                    'iconCls' => 'g-icon-svg g-icon-m_edit g-icon-m_color_default',
                    'handlerArgs' => [
                        'route'   => $this->creator->route('/form/view/{id}'),
                        'pattern' => 'grid.popupMenu.activeRecord'
                    ],
                    'handler' => 'loadWidget'
                ],
                '-',
                [
                    'text'    => '#Widget settings',
                    'iconCls' => 'g-icon-m_wrench g-icon-m_color_default',
                    'handlerArgs' => [
                        'route'   => '{settingsUrl}',
                        'pattern' => 'grid.popupMenu.activeRecord'
                    ],
                    'handler' => 'loadWidget'
                ],
                [
                    'text'    => '#Widget info',
                    'iconCls' => 'g-icon-m_info-circle g-icon-m_color_default',
                    'handlerArgs' => [
                        'route'   => '{infoUrl}',
                        'pattern' => 'grid.popupMenu.activeRecord'
                    ],
                    'handler' => 'loadWidget'
                ]
            ]
        ];

        // 2-й клик по строке сетки
        $this->grid->rowDblClickConfig = [
            'allow' => true,
            'route' => $this->creator->route('/form/view/{id}')
        ];

        // количество строк в сетке
        $this->grid->store->pageSize = 100;
        // локальная фильтрация и сортировка
        $this->grid->store->remoteFilter = false;
        $this->grid->store->remoteSort = false;
        // сортировка сетке
        $this->grid->sorters = [['property' => 'name', 'direction' => 'ASC']];
        // поле аудита записи
        $this->grid->logField = 'name';
        // плагины сетки
        $this->grid->plugins = 'gridfilters';
        // класс CSS применяемый к элементу body сетки
        $this->grid->bodyCls = 'g-grid_background';
        // убрать плагины пагинации сетки
        $this->pagingtoolbar['plugins'] = [];
        // выбирать только одну запись
        $this->selModel = ['mode' => 'SINGLE'];

        // панель навигации (Gm.view.navigator.Info GmJS)
        $this->navigator->info['tpl'] = HtmlNav::tags([
            HtmlNav::image('{icon}', ['width' => '128px'], false),
            HtmlNav::header('{name}'),
            ['div', '{description}', ['style' => 'text-align:center']],
            ['fieldset',
                [
                    HtmlNav::fieldLabel($this->creator->t('Widget id'), '{widgetId}'),
                    HtmlNav::fieldLabel($this->creator->t('Path'), '{path}'),
                    HtmlNav::fieldLabel($this->creator->t('Use'), '{widgetUse}'),
                    HtmlNav::fieldLabel($this->creator->t('Status'), 
                    HtmlGrid::tplSwitch(
                        [
                            [$this->creator->t('not installed'), '0'],
                            [$this->creator->t('installed'), '1'],
                            [$this->creator->t('broken'), '2']
                        ],
                        'status'
                    )),
                    HtmlNav::tplIf('lock==0',
                        HtmlNav::fieldLabel(
                            $this->creator->t('Enabled'),
                            HtmlNav::tplIf('enabled==1',
                                ExtGrid::renderIcon('g-icon_size_15 g-icon-m_check g-icon-m_color_base', 'svg'), 
                                ExtGrid::renderIcon('g-icon_size_15 g-icon-m_xmark g-icon-m_color_error', 'svg')
                            )
                        ),
                        ''
                    )
                ]
            ],
            ['fieldset',
                [
                    HtmlNav::legend($this->creator->t('Version')),
                    HtmlNav::fieldLabel($this->creator->t('Date'), '{versionDate}'),
                    HtmlNav::fieldLabel($this->creator->t('Version'), '{version}'),
                    HtmlNav::fieldLabel($this->creator->t('Author'), '{versionAuthor}'),
                ]
            ],
            ['fieldset',
                [
                    HtmlNav::tplIf('status',
                        HtmlNav::widgetButton(
                            $this->creator->t('Edit record'),
                            ['route' => $this->creator->route('/form/view/{id}'), 'long' => true]
                        ),
                        ''
                    ),
                    HtmlNav::tplIf('settingsUrl',
                        HtmlNav::widgetButton(
                            $this->creator->t('Widget settings'),
                            ['route' => '{settingsUrl}', 'long' => true]
                        ),
                        ''
                    ),
                    HtmlNav::tplIf('infoUrl',
                        HtmlNav::widgetButton(
                            $this->creator->t('Widget info'),
                            ['route' => '{infoUrl}', 'long' => true]
                        ),
                        ''
                    )
                ]
            ]
        ]);

        $this
            ->setNamespaceJS('Gm.be.mp.wmanager')
            ->addRequire('Gm.view.grid.column.Switch')
            ->addRequire('Gm.be.mp.wmanager.Button')
            ->addCss('/grid.css');
    }
}
