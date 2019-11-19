<?php

namespace LiamW\HideSignatures\Entity;

use LiamW\HideSignatures\Repository\HiddenSignatures;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 *
 * @property int user_id
 * @property int hidden_user_id
 */
class UserHiddenSignature extends Entity
{
	protected function _preSave()
	{
		if ($this->isInsert())
		{
			if ($this->user_id == $this->hidden_user_id)
			{
				$this->error(\XF::phrase('liamw_hidesignatures_you_may_not_hide_your_own_signature'));
			}

			$exists = $this->em()->findOne('LiamW\HideSignatures:UserHiddenSignature', [
				'user_id' => $this->user_id,
				'hidden_user_id' => $this->hidden_user_id
			]);
			if ($exists)
			{
				$this->error(\XF::phrase('liamw_hidesignatures_you_already_hiding_this_users_signature'));
			}

			$hiddenSignatureFinder = $this->finder('LiamW\HideSignatures:UserHiddenSignature');
			$total = $hiddenSignatureFinder->where('user_id', $this->user_id)->total();
			$hiddenLimit = 1000;
			if ($total >= $hiddenLimit)
			{
				$this->error(\XF::phrase('liamw_hidesignatures_for_performance_reasons_you_only_able_to_hide_x_signatures', ['hiddenLimit' => $hiddenLimit]));
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
		$this->getHiddenSignaturesRepo()->rebuildHiddenSignatureCache($this->user_id);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_liamw_hidesignatures_user_hidden_signature';
		$structure->primaryKey = ['user_id', 'hidden_user_id'];
		$structure->shortName = 'LiamW/HideSignatures:UserHiddenSignature';

		$structure->columns = [
			'user_id' => ['type' => self::UINT, 'required' => true],
			'hidden_user_id' => ['type' => self::UINT, 'required' => true],
		];

		return $structure;
	}

	/**
	 * @return HiddenSignatures
	 */
	protected function getHiddenSignaturesRepo()
	{
		return $this->repository('LiamW\HideSignatures:HiddenSignatures');
	}
}