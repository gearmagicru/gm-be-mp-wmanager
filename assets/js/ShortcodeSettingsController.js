/*!
 * Контроллер представления формы настроек шорткода виджета.
 * Расширение "Менеджер расширений".
 * Модуль "Маркетплейс".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.mp.wmanager.ShortcodeSettingsController', {
    extend: 'Gm.view.form.PanelController',
    alias: 'controller.gm-mp-wmanager-shortcodesettings',

    /**
     * Возвращает атрибуты шорткода в виде строки.
     * @param {Ext.utils.MixedCollection} fields Поля формы.
     * @return {String}
     */
    getShortcodeAttrbiutes: (fields) => {
        let attr = [];

        fields.each((field, index) => {
            let val = field.getValue();

            if (!field.isDirty() || val === null) return;

            if (Ext.isBoolean(val))
                val = val ? 'true' : 'false';
            else
            if (Ext.isString(val)) {
                if (val.length === 0) {
                    if (Ext.isDefined(field.emptyText)) {
                        if (field.emptyText.length > 0)
                            val = field.emptyText;
                        else
                            return;
                    } else
                        return;
                }
                val = val.escapeDQuotes();
            }
            attr.push(field.name + '="' + val + '"');
        });
        return attr.join(' ');
    },

    /**
     * Возвращает шорткод.
     * @param {String} template Шаблон шорткода.
     * @param {String} attributes Атрибуты шорткода.
     * @return {String} 
     */
    makeShortcode: (template, attributes) => {
        return (new Ext.Template(template)).apply([attributes]);
    },

    /**
     * Событие при клике на кнопке формы "Добавить".
     * @param {Ext.Component} cmp 
     */
    insertShortcode: function (cmp) {
        let form  = cmp.up('form'),
            window = cmp.up('window');

        if (form.isValid()) {
            let attrs = this.getShortcodeAttrbiutes(form.getForm().getFields()),
                shortcode = this.makeShortcode(window.shortcodeTpl, attrs);

            Gm.app.tmp.editor.insertContent(shortcode);
            window.close();
        }
    }
});