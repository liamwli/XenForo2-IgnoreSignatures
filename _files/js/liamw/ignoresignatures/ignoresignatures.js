!function ($, window, document, _undefined)
{
    "use strict";

    XF.InserterClick = XF.extend(XF.InserterClick, {
        __backup: {
            '_applyChange': '__applyChange'
        },

        options: $.extend({}, XF.InserterClick.prototype.options, {
            all: false
        }),

        _applyChange: function ($html, targets, applyFn)
        {
            if (!this.options.all) {
                return this.__applyChange($html, targets, applyFn);
            }

            if (!targets || !targets.length) {
                return;
            }

            var selectors = targets.split(','),
                selector,
                selectorOld, selectorNew,
                $old, $new;

            for (var i = 0; i < selectors.length; i++) {
                selector = selectors[i].split(' with ');
                selectorOld = $.trim(selector[0]);
                selectorNew = selector[1] ? $.trim(selector[1]) : selectorOld;

                if (selectorOld.length && selectorNew.length) {
                    if ($html.is(selectorNew)) {
                        $new = $html;
                    }
                    else {
                        $new = $html.find(selectorNew).first();
                    }

                    $(selectorOld).each(function (k, v)
                    {
                        applyFn(selectorOld, v, $new);
                    });
                }
            }
        }
    });
}(jQuery, window, document);