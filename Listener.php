<?php

namespace LiamW\IgnoreSignatures;

use XF\Mvc\Entity\Entity;

class Listener
{
	public static function entityStructureUserProfile(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
	{
		$structure->columns['ignored_signatures'] = ['type' => Entity::SERIALIZED_ARRAY, 'default' => [], 'changeLog' => false];
	}

	public static function processSignatureMacro(\XF\Template\Templater $templater, &$type, &$template, array &$arguments, array &$globalVars)
	{
		/** @var \LiamW\IgnoreSignatures\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if ($visitor->isIgnoringSignature($arguments['user']))
		{
			$template = 'liamw_ignoresignatures_signature_macro';
		}
	}
}