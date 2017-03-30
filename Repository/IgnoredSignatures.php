<?php

namespace LiamW\IgnoreSignatures\Repository;

use XF\Mvc\Entity\Repository;

class IgnoredSignatures extends Repository
{
	public function getIgnoredSignatureCache($userId)
	{
		return $this->db()->fetchPairs('
			SELECT user.user_id, user.username
			FROM xf_liamw_ignored_signatures AS ignored_signature
			INNER JOIN xf_user AS user ON (ignored_signature.ignored_user_id = user.user_id)
			WHERE ignored_signature.user_id = ?
				AND user.is_staff = 0
				AND user.user_id <> ignored_signature.user_id
			ORDER BY user.username
		', $userId);
	}

	public function rebuildIgnoredSignatureCache($userId)
	{
		$cache = $this->getIgnoredSignatureCache($userId);

		$profile = $this->em->find('XF:UserProfile', $userId);
		if ($profile)
		{
			$profile->fastUpdate('ignored_signatures', $cache);
		}
	}

	public function rebuildIgnoredCacheByIgnoredUser($ignoredUserId)
	{
		$ignorers = $this->db()->fetchAllColumn("
			SELECT user_id
			FROM xf_liamw_ignored_signatures
			WHERE ignored_user_id = ?
		", $ignoredUserId);

		$this->db()->beginTransaction();

		foreach ($ignorers AS $ignorer)
		{
			$this->rebuildIgnoredSignatureCache($ignorer);
		}

		$this->db()->commit();
	}
}