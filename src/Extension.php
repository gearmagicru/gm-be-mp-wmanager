<?php
/**
 * Расширение модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager;

/**
 * Расширение "Менеджер виджетов сайта".
 * 
 * Расширение принадлежит модулю "Маркетплейс".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager
 * @since 1.0
 */
class Extension extends \Gm\Panel\Extension\Extension
{
    /**
     * {@inheritdoc}
     */
    public string $id = 'gm.be.mp.wmanager';

    /**
     * {@inheritdoc}
     */
    public string $defaultController = 'grid';

    /**
     * {@inheritdoc}
     */
    public function controllerMap(): array
    {
        return [
            'winfo'     => 'WidgetInfo',
            'wsettings' => 'WidgetSettings',
            'hsettings' => 'ShortcodeSettings'
        ];
    }
}