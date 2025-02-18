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
use Gm\Panel\Controller\FormController;
use Gm\Backend\Marketplace\WidgetManager\Widget\UploadWindow;

/**
 * Контроллер загрузки файла пакета виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Controller
 * @since 1.0
 */
class Upload extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'UploadForm';

    /**
     * {@inheritdoc}
     */
    public function createWidget(): UploadWindow
    {
        return new UploadWindow();
    }

    /**
     * Действие "perfom" выполняет загрузку файла или подтверждает запрос.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var \Gm\Panel\Http\Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Backend\Marketplace\WidgetManager\Model\UploadForm $form */
        $form = $this->getModel($this->defaultModel);
        if ($form === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->module->id . ':onFormAction', [$this->module, $form, 'upload']);
        }

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

        // загрузка файла
        if (!$form->upload()) {
            $response
                ->meta->error(
                    $form->hasErrors() ? $form->getError() : $this->module->t('File uploading error')
                );
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->module->id . ':onAfterFormAction', [$this->module, $form, 'upload']);
        }
        return $response;
    }
}
