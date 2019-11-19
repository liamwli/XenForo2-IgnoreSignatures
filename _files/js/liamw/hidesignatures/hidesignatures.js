var LiamW = window.LiamW || {};
LiamW.HideSignatures = LiamW.HideSignatures || {};

!function ($, window, document, _undefined) {
	"use strict";

	LiamW.HideSignatures.InserterClickExtension = {
		__backup: {
			'_applyChange': '__applyChange'
		},

		options: $.extend(true, XF.InserterClick.prototype.options, {
			all: false
		}),

		_applyChange: function ($html, targets, applyFn) {
			if (!this.options.all)
			{
				return this.__applyChange($html, targets, applyFn);
			}

			if (!targets || !targets.length)
			{
				return;
			}

			var selectors = targets.split(','),
				selector,
				selectorOld, selectorNew,
				$old, $new;

			for (var i = 0; i < selectors.length; i++)
			{
				selector = selectors[i].split(' with ');
				selectorOld = $.trim(selector[0]);
				selectorNew = selector[1] ? $.trim(selector[1]) : selectorOld;

				if (selectorOld.length && selectorNew.length)
				{
					var $olds = $(selectorOld);

					for (var i2 = 0; i2 < $olds.length; i2++)
					{
						$old = $($olds[i2]);

						if ($html.is(selectorNew))
						{
							$new = $html;
						} else
						{
							$new = $html.find(selectorNew).first();
						}

						applyFn(selectorOld, $old, $new);
					}
				}
			}
		}
	};

	XF.Inserter = XF.extend(XF.Inserter, LiamW.HideSignatures.InserterClickExtension);
}(jQuery, window, document);