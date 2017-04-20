<?php

namespace LiamW\IgnoreSignatures\XF\Entity;

class User extends XFCP_User
{
	public function isIgnoringSignature($userId)
	{
		if (!$this->user_id)
		{
			return false;
		}

		if ($userId instanceof \XF\Entity\User)
		{
			$userId = $userId->user_id;
		}

		if (!$userId || !$this->Profile)
		{
			return false;
		}

		$ignoredSignatures = $this->Profile->ignored_signatures;

		return $ignoredSignatures && isset($ignoredSignatures[$userId]);
	}

	public function canIgnoreSignature(\XF\Entity\User $user, &$error = '')
	{
		if (!$user->user_id || !$this->user_id)
		{
			return false;
		}

		if ($user->is_staff)
		{
			$error = \XF::phraseDeferred('liamw_ignoresignatures_staff_signatures_cannot_be_ignored');

			return false;
		}

		if ($user->user_id == $this->user_id)
		{
			$error = \XF::phraseDeferred('liamw_ignoresignatures_you_may_not_ignore_your_own_signature');

			return false;
		}

		if ($this->user_state != 'valid')
		{
			return false;
		}

		if (!in_array($user->user_state, ['valid', 'email_confirm', 'email_confirm_edit']))
		{
			return false;
		}

		if (!$this->hasPermission('general', 'ignoreSignatures'))
		{
			return false;
		}

		return true;
	}
}