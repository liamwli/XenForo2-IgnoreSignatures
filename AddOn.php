<?php

namespace LiamW\HideSignatures;

use LiamW\HideSignatures\XF\Entity\User;

class AddOn
{
	/**
	 * @return User
	 */
	public static function visitor()
	{
		return \XF::visitor();
	}
}