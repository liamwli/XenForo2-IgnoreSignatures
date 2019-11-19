<?php

namespace LiamW\HideSignatures\Cron;

class AutoRemoveSignature
{
	public static function run()
	{
		\XF::app()->jobManager()->enqueueUnique('lwHideSignatures-autoRemove', 'LiamW\HideSignatures:AutoRemoveSignature', [], false);
	}
}