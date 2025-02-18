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
 * Модель данных профиля записи установленного виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Model
 * @since 1.0
 */
class GridRow extends FormModel
{
    /**
     * Идентификатор выбранного виджета.
     * 
     * @see GridRow::afterValidate()
     * 
     * @var string
     */
    protected ?string $widgetId;

    /**
     * Имя выбранного виджета.
     * 
     * @see GridRow::afterValidate()
     * 
     * @var string
     */
    public ?string $widgetName;

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'tableName'  => '{{widget}}',
            'primaryKey' => 'id',
            'fields'     => [
                ['id'],
                ['name'], 
                ['enabled', 'label' => 'Enabled']
            ],
            'useAudit' => true
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
                if ($message['success']) {
                    if (isset($columns['enabled'])) {
                        $enabled = (int) $columns['enabled'];
                        $message['message'] = $this->module->t('Widget {0} - ' . ($enabled > 0 ? 'enabled' : 'disabled'), [$this->widgetName]);
                        $message['title']   = $this->module->t($enabled > 0 ? 'Enabled' : 'Disabled');
                    }
                }
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
            });
    }

    /**
     * {@inheritDoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            /** @var \Gm\Http\Request $request */
            $request  = Gm::$app->request;
            // имя виджета
            $this->widgetName = $request->post('name');
            if (empty($this->widgetName)) {
                $this->setError(Gm::t('app', 'Parameter passed incorrectly "{0}"', ['Name']));
                return false;
            }
            // идентификатор виджета
            $this->widgetId = $request->post('widgetId');
            if (empty($this->widgetId)) {
                $this->setError(Gm::t('app', 'Parameter passed incorrectly "{0}"', ['Widget Id']));
                return false;
            }
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
    public function beforeUpdate(array &$columns): void
    {
        /** @var \Gm\WidgetManager\WidgetRegistry $installed */
        $installed = Gm::$app->widgets->getRegistry();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;
        // доступность виджета
        $enabled = $request->getPost('enabled', 0, 'int');
        $installed->set($this->widgetId, ['enabled' => (bool) $enabled], true);
    }
}
