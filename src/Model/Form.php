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
use Gm\Panel\Data\Model\FormModel;

/**
 * Модель данных изменения виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Model
 * @since 1.0
 */
class Form extends FormModel
{
    /**
     * {@inheritdoc}
     */
    public array $localizerParams = [
        'tableName'  => '{{widget_locale}}',
        'foreignKey' => 'widget_id',
        'modelName'  => 'Gm\WidgetManager\Model\WidgetLocale',
    ];


    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'useAudit'   => true,
            'tableName'  => '{{widget}}',
            'primaryKey' => 'id',
            'fields'     => [
                ['id'],
                ['name'],
                ['description'],
                [
                    'widget_id',
                    'alias' => 'widgetId'
                ],
                [
                    'enabled', 
                    'title' => 'Enabled'
                ],
                /**
                 * поля добавленные динамически:
                 * - title, имя расширения (для заголовка окна)
                 */
            ],
            // правила форматирования полей
            'formatterRules' => [
                [['enabled'], 'logic']
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
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result, $message) {
                // если всё успешно
                if ($result) {
                    /** @var \Gm\WidgetManager\WidgetRegistry $installed */
                    $installed = Gm::$app->widgets->getRegistry();
                    $widget = $installed->get($this->widgetId);
                    if ($widget) {
                        $lock = (bool) ($widget['lock'] ?? false);
                        // если виджет не системный
                        if (!$lock) {
                            // обвновление конфигурации установленных виджетов
                            $installed->set($this->widgetId, [
                                'enabled'     => (bool) $this->enabled,
                                'name'        => $this->name,
                                'description' => $this->description
                            ], true);
                        }
                    }
                }
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\FormController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            })
            ->on(self::EVENT_AFTER_DELETE, function ($result, $message) {
                // обвновление конфигурации установленных виджетов
                Gm::$app->widgets->update();
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\FormController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            });
    }

    /**
     * {@inheritdoc}
     */
    public function processing(): void
    {
        parent::processing();

        // для формирования загаловка по атрибутам
        $locale = $this->getLocalizer()->getModel();
        if ($locale) {
            $this->title = $locale->name ?: '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            if (!Gm::$app->widgets->getRegistry()->has($this->widgetId)) {
                $this->setError($this->module->t('There is no widget with the specified id "{0}"', [$this->widgetId]));
                return false;
            }
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle():string
    {
        return isset($this->title) ? $this->title : parent::getActionTitle();
    }
}
