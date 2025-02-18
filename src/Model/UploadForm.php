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
use Gm\Uploader\UploadedFile;
use Gm\FilePackager\FilePackager;
use Gm\Panel\Data\Model\FormModel;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель загрузки файла пакета виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Marketplace\WidgetManager\Model
 * @since 1.0
 */
class UploadForm extends FormModel
{
    /**
     * @var string Событие, возникшее после загрузки файла.
     */
    public const EVENT_AFTER_UPLOAD = 'afterUpload';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_UPLOAD, function ($result, $message) {
                /** @var \Gm\Panel\Http\Response\JsongMetadata $meta */
                $meta = $this->response()->meta;
                // всплывающие сообщение
                $meta->cmdPopupMsg($message['message'], $message['title'], $message['type']);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            /** @var false|UploadedFile $file */
            $file = $this->getUploadedFile();
            // проверка загрузки файла
            if ($file === false) {
                $this->setError('No file selected for upload');
                return false;
            }

            // если была ошибки загрузки
            if (!$file->hasUpload()) {
                $this->setError(Gm::t('app', $file->getErrorMessage()));
                return false;
            }

            // если файл не соответствует указанным параметрам
            if ($file->validate()) {
                // если временный каталог для файла не возможно создать
                if (!$this->makePackageFolder($file->getBaseName())) {
                    $this->setError(
                        $this->module->t('Error creating temporary directory to download widget package file')
                    );
                    return false;    
                }
            } else {
                $this->setError(Gm::t('app', $file->getErrorMessage()));
                return false;
            }
        }
        return $isValid;
    }

    /**
     * @see UploadForm::makePackageFolder()
     * 
     * @var string
     */
    private string $packagePath = '';

    /**
     * Создаёт временный каталог виджета.
     * 
     * @param string $packageFolder Имя папки виджета.
     * 
     * @return bool
     */
    protected function makePackageFolder(string $packageFolder): bool
    {
        Fs::$throwException = false;
        $uploadPath = Gm::alias('@runtime') . DS . 'packages';
        $this->packagePath = $uploadPath . DS . $packageFolder;

        if (Fs::exists($this->packagePath))
            if (!Fs::deleteFiles($this->packagePath)) return false;
        else
            if (!Fs::makeDirectory($this->packagePath, 0755, true)) return false;

        Gm::$app->uploader->setPath($uploadPath);
        return true;
    }

    /**
     * @see UploadForm::getUploadedFile()
     * 
     * @var UploadedFile|false
     */
    private UploadedFile|false $uploadedFile;

    /**
     * Возвращает загруженный файл.
     * 
     * @return UploadedFile|false Возвращает значение `false` если была ошибка загрузки.
     */
    public function getUploadedFile(): UploadedFile|false 
    {
        if (isset($this->uploadedFile)) return $this->uploadedFile;

        /** @var \Gm\Uploader\UploadedFile $uploadedFile */
        $uploadedFile = Gm::$app->uploader->getFile('packageFile') ?: false;
        if ($uploadedFile) {
            // проверить расширение загруженного файла.
            $uploadedFile->checkFileExtension = true;
            // доступные расширения файла
            $uploadedFile->allowedExtensions = ['gpk'];
            // формирование уникального имени файла с помощью хеш-функции
            $uploadedFile->uniqueFilename = false;
            // имя файла в нижнем регистре
            $uploadedFile->lowercaseFilename  = false;
            // транслитерация имени файла с исходного языка на латиницу
            $uploadedFile->transliterateFilename = false;
        }
        return $this->uploadedFile = $uploadedFile;
    }

    /**
     * Выполняет загрузку файла.
     * 
     * @param bool $useValidation Использовать проверку атрибутов (по умолчанию `false`).
     * @param array $attributes Имена атрибутов с их значениями, если не указаны - будут 
     * задействованы атрибуты записи (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка загрузки файла.
     */
    public function upload(bool $useValidation = false, array $attributes = null)
    {
        if ($useValidation && !$this->validate($attributes)) {
            return false;
        }
        if ($this->uploadProcess($attributes)) {
            return $this->unpackProcess();
        }
        return false;
    }

    /**
     * Извлечение файлов из пакета виджета.
     * 
     * @return bool
     */
    protected function unpackProcess(): bool
    {
        /** @var string $filename Имя загруженного файла */
        $filename = $this->getUploadedFile()->uploadedFilename;
        /** @var FilePackager $packager */
        $packager = new FilePackager(['filename' => $filename]);

        /** @var \Gm\FilePackager\Package $package */
        $package = $packager->getPackage([
            'path'   => $this->packagePath,
            'format' => 'json'
        ]);

        if (!$packager->unpack($package)) {
            $this->setError($packager->getError());
            return false;
        }

        if (!$package->load(true)) {
            $this->setError($package->getError());
            return false;
        }

        if (empty($package->id) || empty($package->type)) {
            $this->setError(
                $this->module->t('The widget package file does not contain one of the attributes: id, type')
            );
            return false;
        }

        if ($package->type !== 'widget') {
            $this->setError(
                $this->module->t('Widget attribute "{0}" is incorrectly specified', ['type'])
            );
            return false;
        }

        /** @var array|null $params Параметры установленного виджета */
        $params = Gm::$app->widgets->getRegistry()->get($package->id);
        if ($params) {
            /** @var array|null $names */
            $names = Gm::$app->widgets->selectName($params['rowId']);
            $this->setError(
                $this->module->t(
                    'You already have the widget "{0}" installed. Please remove it and try again', 
                    [$names ? $names['name'] : $params['name']]
                )
            );
            return false;
        }

        /** @var array|false $exists */
        $exists = $package->fileExists();
        if ($exists !== false) {
            $this->setError(
                $this->module->t(
                    'You already have a widget with files installed: {0}', 
                    [implode('<br>', array_slice($exists, 0, 5))]
                )
            );
            return false;
        }

        if (!$package->extract()) {
            $this->setError($package->getError());
            return false;
        }
        return true;
    }

    /**
     * Процесс подготовки загрузки файла.
     * 
     * @param null|array $attributes Имена атрибутов с их значениями (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка загрузки файла.
     */
    protected function uploadProcess(array $attributes = null): bool
    {
        /** @var UploadedFile $file */
        $file = $this->getUploadedFile();

        $this->result = $file->move();
        // если файл не загружен
        if (!$this->result) {
            $this->setError(Gm::t('app', $file->getErrorMessage()));
        }

        $this->afterUpload($this->result);
        return $this->result;
    }

    /**
     * Cобытие вызывается после загрузки файла.
     * 
     * @see UploadForm::upload()
     * 
     * @param bool $result Если значение `true`, файл успешно загружен.
     * 
     * @return void
     */
    public function afterUpload(bool $result = false)
    {
        /** @var bool|int $result */
        $this->trigger(
            self::EVENT_AFTER_UPLOAD,
            [
                'result'  => $result,
                'message' => $this->lastEventMessage = $this->uploadMessage($result)
            ]
        );
    }

    /**
     * Возвращает сообщение полученное при загрузке файла.
     *
     * @param bool $result Если значение `true`, файл успешно загружен.
     * 
     * @return array Сообщение имеет вид:
     * ```php
     *     [
     *         'success' => true,
     *         'message' => 'File uploaded successfully',
     *         'title'   => 'Uploading a file',
     *         'type'    => 'accept'
     *     ]
     * ```
     */
    public function uploadMessage(bool $result): array
    {
        $messages = $this->getActionMessages();
        return [
            'success'  => $result, // успех загрузки
            'message'  => $messages[$result ? 'msgSuccessUpload' : 'msgUnsuccessUpload'], // сообщение
            'title'    => $messages['titleUpload'], // заголовок сообщения
            'type'     => $result ? 'accept' : 'error' // тип сообщения
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionMessages(): array
    {
        return [
            'titleUpload'        => $this->module->t('Uploading a file'),
            'msgUnsuccessUpload' => $this->getError(),
            'msgSuccessUpload'   => $this->module->t('File uploaded successfully')
        ];
    }
}
