<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * Пакет английской (британской) локализации.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    '{name}'        => 'Widget Mamager',
    '{description}' => 'Website Widget Manager',
    '{permissions}' => [
        'any'       => ['Full access', 'View and make changes to widgets'],
        'view'      => ['View', 'View widgets'],
        'read'      => ['Read', 'Read widgets'],
        'install'   => ['Install', 'Install widgets'],
        'uninstall' => ['Uninstall', 'Remove and uninstall widgets']
    ],

    // Grid: панель инструментов
    'Edit record' => 'Edit record',
    'Update' => 'Update',
    'Update configurations of installed widgets' => 'Update configurations of installed widgets',
    'Widget enabled' => 'Widget enabled',
    'You need to select a widget' => 'You need to select a widget',
    'Download' => 'Download',
    'Downloads widget package file' => 'Downloads widget package file',
    'Upload' => 'Upload',
    'Uploads widget package file' => 'Uploads widget package file',
    // Grid: панель инструментов / Установить (install)
    'Install' => 'Install',
    'Widget install' => 'Widget install',
    // Grid: панель инструментов / Удалить (uninstall)
    'Uninstall' => 'Uninstall',
    'Completely delete an installed widget' => 'Completely delete an installed widget',
    'Are you sure you want to completely delete the installed widget?' => 'Are you sure you want to completely delete the installed widget?',
    // Grid: панель инструментов / Удалить (delete)
    'Delete' => 'Delete',
    'Delete an uninstalled widget from the repository' => 'Delete an uninstalled widget from the repository',
    'Are you sure you want to delete the uninstalled widget from the repository?' => 'Are you sure you want to delete the uninstalled widget from the repository?',
    // Grid: панель инструментов / Демонтаж (unmount)
    'Unmount' => 'Unmount',
    'Delete an installed widget without removing it from the repository' => 'Delete an installed widget without removing it from the repository',
    'Are you sure you want to remove the installed widget without removing it from the repository?' 
        => 'Are you sure you want to remove the installed widget without removing it from the repository?',
    // Grid: фильтр
    'All' => 'All',
    'Installed' => 'Installed',
    'None installed' => 'None installed',
    // Grid: поля
    'Name' => 'Name',
    'Widget id' => 'Widget id',
    'Record id' => 'Record id',
    'Path' => 'Path',
    'Enabled' => 'Enabled',
    'Author' => 'Author',
    'Version' => 'Version',
    'from' => 'from',
    'Description' => 'Description',
    'Resource' => 'Resource',
    'Use' => 'Use',
    'Date' => 'Date',
    'Widget settings' => 'Widget settings',
    'Widget info' => 'Widget info',
    'Status' => 'Status',
    // Grid: значения
    FRONTEND => 'Site',
    BACKEND => 'Panel control',
    'Yes' => 'yes',
    'No' => 'no',
    'installed' => 'installed',
    'not installed' => 'not installed',
    'unknow' => 'unknow',
    // Grid: всплывающие сообщения / заголовок
    'Enabled' => 'Enabled',
    'Disabled' => 'Disabled',
    'Unmounting' => 'Unmounting',
    'Uninstalling' => 'Uninstalling',
    'Deleting' => 'Deleting',
    'Downloading' => 'Downloading',
    // Grid: всплывающие сообщения / текст
    'Widget {0} - enabled' => 'Widget "<b>{0}</b>" - <b>enabled</b>.',
    'Widget {0} - disabled' => 'Widget "<b>{0}</b>" - <b>disabled</b>.',
    'Widgets configuration files are updated' => 'Widgets configuration files are updated!',
    'Updating widgets' => 'Updating widgets',
    'Unmounting of widget "{0}" completed successfully' => 'Unmounting of widget "{0}" completed successfully.',
    'Uninstalling of widget "{0}" completed successfully' => 'Uninstalling of widget "{0}" completed successfully.',
    'Deleting of widget completed successfully' => 'Deleting of widget completed successfully.',
    'The widget package will now be loaded' => 'The widget package will now be loaded.',
    // Grid: сообщения (ошибки)
    'There is no widget with the specified id "{0}"' => 'There is no widget with the specified id "{0}"',
    'Widget installation configuration file is missing' => 'Widget installation configuration file is missing (.install.php).',
    'It is not possible to remove the widget from the repository because it\'s installed' 
        => 'It is not possible to remove the widget from the repository because it\'s installed.',
    // Grid: аудит записей
    'widget {0} with id {1} is enabled' => 'widget "<b>{0}</b>" with id "<b>{1}</b>" is enabled',
    'widget {0} with id {1} is disabled' => 'widget "<b>{0}</b>" with id "<b>{1}</b>" is disabled',

    // Form
    '{form.title}' => 'Widget editing "{title}"',
    '{form.subtitle}' => 'Editing basic widget settings',
    // Form: поля
    'Identifier' => 'Identifier',
    'Record identifier' => 'Record identifier',
    'Default' => 'Default',
    'enabled' => 'enabled',

    // Upload
    '{upload.title}' => 'Loading widget package file',
    // Upload: панель инструментов
    'Upload' => 'Upload',
    // Upload: поля
    'File name' => 'File name',
    '(more details)' => '(more details)',
    'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}' 
        => 'The file(s) will be downloaded according to the parameters for downloading resources to the server. File extension only ".gpk". {0}',
    // Upload: всплывающие сообщения / заголовок
    'Uploading a file' => 'Uploading a file',
    // Upload: сообщения
    'File uploading error' => 'Error loading widget package file.',
    'Error creating temporary directory to download widget package file' 
        => 'Error creating temporary directory to download widget package file.',
    'File uploaded successfully' => 'File uploaded successfully.',
    'The widget package file does not contain one of the attributes: id, type' 
        => 'The widget package file does not contain one of the attributes: id, type.',
    'Widget attribute "{0}" is incorrectly specified' => 'Widget attribute "{0}" is incorrectly specified.',
    'You already have the widget "{0}" installed. Please remove it and try again' 
        => 'You already have the widget "{0}" installed. Please remove it and try again.',
    'You already have a widget with files installed: {0}' 
        => 'You already have a widget with files installed: <br><br>{0}<br>...',

    // Widget: вкладка
    '{info.title}' => 'Widget Information "{0}"',

    // WidgetSettings: всплывающие сообщения / заголовок
    'Widget setting' => 'Widget setting',
    // WidgetSettings: сообщения (ошибки)
    'Unable to create widget object "{0}"' => 'Unable to create widget object "{0}".',
    'Unable to get widget settings' => 'Unable to get widget settings.'
];
