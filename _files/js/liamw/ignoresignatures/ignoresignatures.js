!function ($, window, document, _undefined) {
    "use strict";

    XF.RedirectSwitchClick = XF.extend(XF.SwitchClick, {
        options: $.extend({}, XF.SwitchClick.prototype.options, {
            redirect: true
        })
    });

    XF.Click.register('switch-redirect', 'XF.RedirectSwitchClick');
}
(jQuery, window, document);