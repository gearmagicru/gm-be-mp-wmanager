<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Controller;

use Gm\Panel\Controller\ComboTriggerController;

/**
 * Контроллера выпадающего списка.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 */
class Trigger extends ComboTriggerController
{
    /**
     * {@inheritdoc}
     */
    protected array $triggerNames = [
        'widgets' => 'WidgetCombo'
    ];
}
