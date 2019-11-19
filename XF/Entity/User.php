<?php

namespace LiamW\HideSignatures\XF\Entity;

class User extends XFCP_User
{
	public function isHidingSignature($userId)
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

		$ignoredSignatures = $this->Profile->liamw_hidesignatures_hidden_signatures;

		return $ignoredSignatures && isset($ignoredSignatures[$userId]);
	}

	public function canHideSignature(\XF\Entity\User $user, &$error = null)
	{
		if (!$user->user_id || !$this->user_id)
		{
			return false;
		}

		if ($user->is_staff)
		{
			$error = \XF::phraseDeferred('liamw_hidesignatures_you_cannot_hide_signature_of_staff_members');

			return false;
		}

		if ($user->user_id == $this->user_id)
		{
			$error = \XF::phraseDeferred('liamw_hidesignatures_you_cannot_hide_your_own_signature');

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

		if (!$this->hasPermission('general', 'lwHideSignatures'))
		{
			return false;
		}

		return true;
	}
}