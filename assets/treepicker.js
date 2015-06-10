/**
 * widget_tree_picker extension for Contao Open Source CMS
 *
 * Copyright (C) 2014 Codefog
 *
 * @package widget_tree_picker
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

var TreePicker = {

    /**
     * Toggle the input field
     *
     * @param {object} el    The DOM element
     * @param {string} id    The ID of the target element
     * @param {string} field The field name
     * @param {string} name  The Ajax field name
     * @param {int}    level The indentation level
     *
     * @returns {boolean}
     */
    toggle: function (el, id, field, name, level) {
        el.blur();
        Backend.getScrollOffset();

        var item = $(id),
            image = $(el).getFirst('img');

        if (item) {
            if (item.getStyle('display') == 'none') {
                item.setStyle('display', 'inline');
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                $(el).store('tip:title', Contao.lang.collapse);
                new Request.Contao({field:el}).post({'action':'toggleTreepicker', 'id':id, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
            } else {
                item.setStyle('display', 'none');
                image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
                $(el).store('tip:title', Contao.lang.expand);
                new Request.Contao({field:el}).post({'action':'toggleTreepicker', 'id':id, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
            }
            return false;
        }

        new Request.Contao({
            field: el,
            evalScripts: true,
            onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
            onSuccess: function(txt) {
                var li = new Element('li', {
                    'id': id,
                    'class': 'parent',
                    'styles': {
                        'display': 'inline'
                    }
                });

                var ul = new Element('ul', {
                    'class': 'level_' + level,
                    'html': txt
                }).inject(li, 'bottom');

                li.inject($(el).getParent('li'), 'after');

                // Update the referer ID
                li.getElements('a').each(function(el) {
                    el.href = el.href.replace(/&ref=[a-f0-9]+/, '&ref=' + Contao.referer_id);
                });

                $(el).store('tip:title', Contao.lang.collapse);
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                AjaxRequest.hideBox();

                // HOOK
                window.fireEvent('ajax_change');
            }
        }).post({'action':'loadTreepicker', 'id':id, 'level':level, 'field':field, 'name':name, 'state':1, 'REQUEST_TOKEN':Contao.request_token});

        return false;
    },


    /**
     * Open a tree selector in a modal window
     *
     * @param {object} options An optional options object
     */
    openModal: function(options) {
        var opt = options || {},
            max = (window.getSize().y-180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal({
            'width': opt.width,
            'btn_ok': Contao.lang.close,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() { document.body.setStyle('overflow', 'auto'); }
        });
        M.addButton(Contao.lang.close, 'btn', function() {
            this.hide();
        });
        M.addButton(Contao.lang.apply, 'btn primary', function() {
            var val = [],
                frm = null,
                frms = window.frames;
            for (i=0; i<frms.length; i++) {
                if (frms[i].name == 'simple-modal-iframe') {
                    frm = frms[i];
                    break;
                }
            }
            if (frm === null) {
                alert('Could not find the SimpleModal frame');
                return;
            }
            if (frm.document.location.href.indexOf('contao/main.php') != -1) {
                alert(Contao.lang.picker);
                return; // see #5704
            }
            var inp = frm.document.getElementById('tl_select').getElementsByTagName('input');
            for (var i=0; i<inp.length; i++) {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
            }
            if (opt.tag) {
                $(opt.tag).value = val.join(',');
                opt.self.set('href', opt.self.get('href').replace(/&value=[^&]*/, '&value='+val.join(',')));
            } else {
                $('ctrl_'+opt.id).value = val.join("\t");
                new Request.Contao({
                    field: $('ctrl_'+opt.id),
                    evalScripts: false,
                    onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                    onSuccess: function(txt, json) {
                        $('ctrl_'+opt.id).getParent('div').set('html', json.content);
                        json.javascript && Browser.exec(json.javascript);
                        AjaxRequest.hideBox();
                        window.fireEvent('ajax_change');
                    }
                }).post({'action':'reloadTreepicker', 'name':opt.id, 'value':$('ctrl_'+opt.id).value, 'REQUEST_TOKEN':Contao.request_token});
            }
            this.hide();
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
    },
};