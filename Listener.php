<?php

namespace LiamW\HideSignatures;

use LiamW\HideSignatures\AddOn as HideSignatures;
use XF\Mvc\Entity\Entity;

class Listener
{
	public static function userEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
	{
		$structure->relations['HiddenSignatures'] = [
			'entity' => 'LiamW\HideSignatures:UserHiddenSignature',
			'type' => Entity::TO_MANY,
			'conditions' => 'user_id',
			'key' => 'hidden_user_id'
		];
	}

	public static function userProfileEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
	{
		$structure->columns['liamw_hidesignatures_hidden_signatures'] = [
			'type' => Entity::JSON_ARRAY,
			'default' => [],
			'changeLog' => false
		];
		$structure->columns['liamw_hidesignatures_signature_warning_date'] = [
			'type' => Entity::UINT,
			'nullable' => true,
			'changeLog' => false
		];
	}

	public static function processSignatureMacro(\XF\Template\Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
	{
		$visitor = HideSignatures::visitor();

		if ($visitor->isHidingSignature($arguments['user']))
		{
			$template = 'liamw_hidesignatures_signature_macro';
		}
	}
}