<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Marketplace\WidgetManager\Model;

use Gm;
use Gm\Db\Sql;
use Gm\Panel\Data\Model\Combo\ComboModel;

/**
 * Модель данных элементов выпадающего списка установленных виджетов 
 * (реализуемых представленим с использованием компонента Gm.form.Combo ExtJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Model
 * @since 1.0
 */
class WidgetCombo extends ComboModel
{
    /**
     * {@inheritdoc}
     */
    protected array $allowedKeys = [
        'id'       => 'id',
        'widgetId' => 'widget_id'
    ];

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'tableName'  => '{{widget_locale}}',
            'primaryKey' => 'widget_id',
            'searchBy'   => 'name',
            'order'      => ['name' => 'ASC'],
            'fields'     => [
                ['name', 'direct' => 'widl.name'],
                ['description']
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function selectAll(string $tableName = null): array
    {
        /** @var \Gm\Db\Sql\Select $select */
        $select = $this->builder()->select();
        $select
            ->columns(['id', 'widget_id', 'name', 'description'])
            ->quantifier(new Sql\Expression('SQL_CALC_FOUND_ROWS'))
            ->from(['wid' => '{{widget}}'])
            ->join(
                ['widl' => '{{widget_locale}}'],
                'widl.widget_id = wid.id AND widl.language_id = ' . (int) Gm::$app->language->code,
                ['loName' => 'name', 'loDescription' => 'description'],
                $select::JOIN_LEFT
            );

        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->buildQuery($select);
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetchRow(array $row, array &$rows): void
    {
        if ($row['loName']) {
            $row['name'] = $row['loName'];
        }
        if ($row['loDescription']) {
            $row['description'] = $row['loDescription'];
        }
        $rows[] = [$row[$this->key], $row['name'], $row['description']];
    }
}
