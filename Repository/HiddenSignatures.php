<?php

namespace LiamW\HideSignatures\Repository;

use XF\Mvc\Entity\Repository;

class HiddenSignatures extends Repository
{
	public function getHiddenSignatureCache($userId)
	{
		return $this->db()->fetchPairs('
			SELECT user.user_id, user.username
			FROM xf_liamw_hidesignatures_user_hidden_signature AS hidden_signature
			INNER JOIN xf_user AS user ON (hidden_signature.hidden_user_id = user.user_id)
			WHERE hidden_signature.user_id = ?
				AND user.is_staff = 0
				AND user.user_id <> hidden_signature.user_id
			ORDER BY user.username
		', $userId);
	}

	public function rebuildHiddenSignatureCache($userId)
	{
		$cache = $this->getHiddenSignatureCache($userId);

		$profile = $this->em->find('XF:UserProfile', $userId);
		if ($profile)
		{
			$profile->fastUpdate('liamw_hidesignatures_hidden_signatures', $cache);
		}
	}

	public function rebuildHiddenSignatureCacheByHiddenUser($hiddenUserId)
	{
		$hiders = $this->db()->fetchAllColumn("
			SELECT user_id
			FROM xf_liamw_hidesignatures_user_hidden_signature
			WHERE hidden_user_id = ?
		", $hiddenUserId);

		$this->db()->beginTransaction();

		foreach ($hiders AS $hider)
		{
			$this->rebuildHiddenSignatureCache($hider);
		}

		$this->db()->commit();
	}
}