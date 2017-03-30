<?php

namespace LiamW\IgnoreSignatures\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class IgnoredSignatures extends Entity
{
	protected function _preSave()
	{
		if ($this->isInsert())
		{
			if ($this->user_id == $this->ignored_user_id)
			{
				$this->error(\XF::phrase('liamw_ignoresignatures_you_may_not_ignore_your_own_signature'));
			}

			$exists = $this->em()->findOne('LiamW\IgnoreSignatures:IgnoredSignatures', [
				'user_id' => $this->user_id,
				'ignored_user_id' => $this->ignored_user_id
			]);
			if ($exists)
			{
				$this->error(\XF::phrase('liamw_ignoresignatures_you_already_ignore_this_persons_signature'));
			}

			$ignoredFinder = $this->finder('XF:UserIgnored');
			$total = $ignoredFinder
				->where('user_id', $this->user_id)
				->total();
			$ignoredLimit = 1000;
			if ($total >= $ignoredLimit )
			{
				$this->error(\XF::phrase('liamw_ignoresignatures_for_performance_reasons_you_may_only_ignore_x_signatures', ['count' => $ignoredLimit]));
			}
		}
	}

	protected function _postSave()
	{
		$this->rebuildIgnoredCache();
	}

	protected function _postDelete()
	{
		$this->rebuildIgnoredCache();
	}

	protected function rebuildIgnoredCache()
	{
		$this->getIgnoredSignatureRepo()->rebuildIgnoredSignatureCache($this->user_id);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_liamw_ignored_signatures';
		$structure->primaryKey = ['user_id', 'ignored_user_id'];
		$structure->shortName = 'LiamW/IgnoredSignatures:IgnoredSignatures';

		$structure->columns = [
			'user_id' => ['type' => self::UINT, 'required' => true],
			'ignored_user_id' => ['type' => self::UINT, 'required' => true],
		];

		return $structure;
	}

	/**
	 * @return \LiamW\IgnoreSignatures\Repository\IgnoredSignatures
	 */
	protected function getIgnoredSignatureRepo()
	{
		return $this->repository('LiamW\IgnoreSignatures:IgnoredSignatures');
	}
}