<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * Пакет русской локализации.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    '{name}'        => 'Менеджер виджетов',
    '{description}' => 'Управление виджетами сайта',
    '{permissions}' => [
        'any'       => ['Полный доступ', 'Просмотр и внесение изменений в видежты'],
        'view'      => ['Просмотр', 'Просмотр виджетов'],
        'read'      => ['Чтение', 'Чтение виджетов'],
        'install'   => ['Установка', 'Установка виджетов'],
        'uninstall' => ['Удаление', 'Удаление и демонтаж виджетов']
    ],

    // Grid: панель инструментов
    'Edit record' => 'Редактировать',
    'Update' => 'Обновить',
    'Update configurations of installed widgets' => 'Обновление конфигурации установленных виджетов',
    'Widget enabled' => 'Доступ к виджету',
    'You need to select a widget' => 'Вам нужно выбрать виджета',
    'Download' => 'Скачать',
    'Downloads widget package file' => 'Скачивает файла пакета виджета',
    'Upload' => 'Загрузить',
    'Uploads widget package file' => 'Загружает файл пакета виджета',
    // Grid: панель инструментов / Установить (install)
    'Install' => 'Установить',
    'Widget install' => 'Установка виджета',
    // Grid: панель инструментов / Удалить (uninstall)
    'Uninstall' => 'Удалить',
    'Completely delete an installed widget' => 'Полностью удаление установленного виджета',
    'Are you sure you want to completely delete the installed widget?' => 'Вы уверены, что хотите полностью удалить установленный виджет (все файлы виджета будут удалены)?',
    // Grid: панель инструментов / Удалить (delete)
    'Delete' => 'Удалить',
    'Delete an uninstalled widget from the repository' => 'Удаление не установленного виджета из репозитория',
    'Are you sure you want to delete the uninstalled widget from the repository?' => 'Вы уверены, что хотите удалить не установленный виджет из репозитория?',
    // Grid: панель инструментов / Демонтаж (unmount)
    'Unmount' => 'Демонтаж',
    'Delete an installed widget without removing it from the repository' => 'Удаление установленного виджета без удаления его из репозитория',
    'Are you sure you want to remove the installed widget without removing it from the repository?' 
        => 'Вы уверены, что хотите удалить установленный виджет без удаления его из репозитория?',
    // Grid: фильтр
    'All' => 'Все',
    'Installed' => 'Установленные',
    'None installed' => 'Не установленные',
    // Grid: поля
    'Name' => 'Название',
    'Widget id' => 'Идентификатор',
    'Record id' => 'Идентификатор записи',
    'Path' => 'Путь',
    'Enabled' => 'Доступен',
    'Author' => 'Автор',
    'Version' => 'Версия',
    'from' => 'от',
    'Description' => 'Описание',
    'Resource' => 'Ресурсы',
    'Use' => 'Назначение',
    'Date' => 'Дата',
    'Widget settings' => 'Настройка виджета',
    'Widget info' => 'Информация о виджете',
    'Status' => 'Статус',
    // Grid: значения
    FRONTEND => 'Сайт',
    BACKEND => 'Панель управления',
    'Yes' => 'да',
    'No' => 'нет',
    'installed' => 'установлен',
    'not installed' => 'не установлен',
    'unknow' => 'неизвестно',
    // Grid: всплывающие сообщения / заголовок
    'Enabled' => 'Доступен',
    'Disabled' => 'Отключен',
    'Unmounting' => 'Демонтаж',
    'Uninstalling' => 'Удаление',
    'Deleting' => 'Удаление',
    'Downloading' => 'Скачивание',
    // Grid: всплывающие сообщения / текст
    'Widget {0} - enabled' => 'Виджет "<b>{0}</b>" - <b>доступен</b>.',
    'Widget {0} - disabled' => 'Виджет "<b>{0}</b>" - <b>отключен</b>.',
    'Widgets configuration files are updated' => 'Файлы конфигурации виджетов обновлены!',
    'Updating widgets' => 'Обновление виджетов',
    'Unmounting of widget "{0}" completed successfully' => 'Демонтаж виджета "{0}" успешно завершен.',
    'Uninstalling of widget "{0}" completed successfully' => 'Удаление виджета "{0}" успешно завершено.',
    'Deleting of widget completed successfully' => 'Удаление виджета выполнено успешно.',
    'The widget package will now be loaded' => 'Сейчас будет выполнена загрузка пакета виджета.',
    // Grid: сообщения (ошибки)
    'There is no widget with the specified id "{0}"' => 'Виджет с указанным идентификатором "{0}" отсутствует',
    'Widget installation configuration file is missing' => 'Отсутствует файл конфигурации установки виджета (.install.php).',
    'It is not possible to remove the widget from the repository because it\'s installed' 
        => 'Невозможно выполнить удаление виджета из репозитория, т.к. он установлен.',
    // Grid: аудит записей
    'widget {0} with id {1} is enabled' => 'предоставление доступа к виджету "<b>{0}</b>" c идентификатором "<b>{1}</b>"',
    'widget {0} with id {1} is disabled' => 'отключение доступа к виджету "<b>{0}</b>" c идентификатором "<b>{1}</b>"',

    // Form
    '{form.title}' => 'Редактирование виджета "{title}"',
    '{form.subtitle}' => 'Редактирование базовых настроек виджета',
    // Form: поля
    'Identifier' => 'Идентификатор',
    'Record identifier' => 'Идентификатор записи',
    'Default' => 'По умолчанию',
    'enabled' => 'доступен',

    // Upload
    '{upload.title}' => 'Загрузка файла пакета виджета',
    // Upload: панель инструментов
    'Upload' => 'Загрузить',
    // Upload: поля
    'File name' => 'Имя файла',
    '(more details)' => '(подробнее)',
    'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}' 
        => 'Загрузка файла(ов) будет выполнена согласно <em>"параметрам загрузки ресурсов на сервер"</em>. Только расширение файла ".gpk". {0}',
    // Upload: всплывающие сообщения / заголовок
    'Uploading a file' => 'Загрузка файла',
    // Upload: сообщения
    'File uploading error' => 'Ошибка загрузки файла пакета виджета.',
    'Error creating temporary directory to download widget package file' 
        => 'Ошибка создания временного каталога для загрузки файла пакета виджета.',
    'File uploaded successfully' => 'Файл пакета виджета успешно загружен.',
    'The widget package file does not contain one of the attributes: id, type' 
        => 'Файл пакета виджета не содержит один из атрибутов: "id" или "type".',
    'Widget attribute "{0}" is incorrectly specified' => 'Неправильно указан атрибут "{0}" виджета.',
    'You already have the widget "{0}" installed. Please remove it and try again' 
        => 'У Вас уже установлен виджет "{0}". Удалите его и повторите действие заново.',
    'You already have a widget with files installed: {0}' 
        => 'У Вас уже установлен виджет со слудующими файлами, удалиет их и <br>повторите действие заново: <br><br>{0}<br>...',

    // Widget: вкладка
    '{info.title}' => 'Информация о виджете "{0}"',

    // WidgetSettings: всплывающие сообщения / заголовок
    'Widget setting' => 'Настройка виджета',
    // WidgetSettings: сообщения (ошибки)
    'Unable to create widget object "{0}"' => 'Невозможно создать объект виджета "{0}".',
    'Unable to get widget settings' => 'Невозможно получить настройки виджета.',

    // ShortcodeSettings: сообщения (ошибки)
    'Unable to show widget shortcode settings' => 'Невозможно показать настройки шорткода виджета.',
];
