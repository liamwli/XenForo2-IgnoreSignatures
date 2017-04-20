<?php

namespace LiamW\IgnoreSignatures\Cron;

class AutoRemoveSignature
{
	public static function run()
	{
		\XF::app()->jobManager()
			->enqueueUnique('ignoresignature_autoremove', 'LiamW\IgnoreSignatures:AutoRemoveSignature', [], false);
	}
}