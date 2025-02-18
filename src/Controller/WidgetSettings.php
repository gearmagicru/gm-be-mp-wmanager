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
use Gm\Panel\Helper\ExtForm;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Widget\SettingsWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер настройки виджета.
 * 
 * Действия контроллера:
 * - view, вывод интерфейса настроек виджета;
 * - data, вывод настроек виджета по указанному идентификатору;
 * - update, изменение настроек виджета по указанному идентификатору.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class WidgetSettings extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Marketplace\WidgetManager\Extension
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
            // просмтор настроек
            case 'data':
                return Gm::t(BACKEND, "{{$this->actionName} settings action}");

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} settings action}")
                );
        }
    }

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
    public function createWidget(): SettingsWindow
    {
        return new SettingsWindow();
    }

    /**
     * Действие "view" выводит интерфейс настроек виджета.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        // если виджет не найден
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$id]));
            return $response;
        }

        // для доступа к пространству имён объекта
        Gm::$loader->addPsr4($widgetParams['namespace']  . NS, Gm::$app->modulePath . $widgetParams['path'] . DS . 'src');

        $settingsClass = $widgetParams['namespace'] . NS . 'Settings' . NS . 'Settings';
        if (!class_exists($settingsClass)) {
            $response
                ->meta->error($this->module->t('Unable to create widget object "{0}"', [$settingsClass]));
            return $response;
        }

        // т.к. виджет самостоятельно не может подключать свою локализацию (в данном случаи делает это модуль), 
        // то добавляем шаблон локализации виджета модулю
        $category = Gm::$app->translator->getCategory($this->module->id);
        $category->patterns['widget'] = [
            'basePath' => Gm::$app->modulePath . $widgetParams['path'] . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $this->module->addTranslatePattern('widget');

        /** @var object|Gm\Panel\Widget\SettingsWindow $widget Виджет настроек */
        $widget = Gm::createObject($settingsClass);
        if ($widget instanceof Gm\Panel\Widget\SettingsWindow) {
            // панель формы (Gm.view.form.Panel GmJS)
            $widget->form->router->route = $this->module->route('/wsettings');
            $widget->form->router->id    = $id;
            $widget->form->buttons = ExtForm::buttons([
                'help' => [
                    'component' => 'widget:' . $widgetParams['id'],
                    'subject'   => 'settings'
                ], 
                'reset', 'save', 'cancel'
            ]);
            $widget->titleTpl = $this->module->t('{settings.title}');
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "data" выводит настройки виджета по указанному идентификатору.
     *
     * @return Response
     */
    public function dataAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams Параметры виджета */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        // если виджет не найден
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$id]));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $model */
        $model = Gm::$app->widgets->getModel(
            'Settings', $widgetParams['id'], ['basePath' => Gm::$app->modulePath . $widgetParams['path'], 'module' => $this->module]
        );
        // если модель данных не определена
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', ['Settings']));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $form */
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(
                    $model->hasErrors() ? $model->getError() : Gm::t(BACKEND, 'The item you selected does not exist or has been deleted')
                );
            return $response;
        }

        return $response->setContent($form->getAttributes());
    }

    /**
     * Действие "update" изменяет настройки виджета по указанному идентификатору.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams Параметры виджета */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        // если виджет не найден
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$id]));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $model */
        $model = Gm::$app->widgets->getModel(
            'Settings', $widgetParams['id'], ['basePath' => Gm::$app->modulePath . $widgetParams['path'], 'module' => $this->module]
        );
        // если модель данных не определена
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', ['Settings']));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $form */
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(
                    $model->hasErrors() ? $model->getError() : $this->t('Unable to get widget settings')
                );
            return $response;
        }

        // т.к. виджет самостоятельно не может подключать свою локализацию (в данном случаи делает это модуль), 
        // то добавляем шаблон локализации виджета модулю
        $category = Gm::$app->translator->getCategory($this->module->id);
        $category->patterns['widget'] = [
            'basePath' => Gm::$app->modulePath . $widgetParams['path'] . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $this->module->addTranslatePattern('widget');

        // загрузка атрибутов в модель из запроса
        if (!$form->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // валидация атрибутов модели
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // сохранение атрибутов модели
        if (!$form->save()) {
            $response
                ->meta->error(
                    $form->hasErrors() ? $form->getError() : Gm::t(BACKEND, 'Could not save data')
                );
            return $response;
        } else {
            // всплывающие сообщение
            $response
                ->meta
                    ->cmdPopupMsg($this->t('Widget settings successfully changed'), $this->t('Widget settings'), 'accept');
        }
        return $response;
    }
}
