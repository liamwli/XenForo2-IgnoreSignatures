<?php

namespace LiamW\IgnoreSignatures\Service\User;

use XF\Service\AbstractService;

class IgnoreSignatures extends AbstractService
{
	/**
	 * @var \XF\Entity\User
	 */
	protected $ignoredBy;

	/**
	 * @var \XF\Entity\User
	 */
	protected $ignoredUser;

	public function __construct(\XF\App $app, \XF\Entity\User $ignoredUser, \XF\Entity\User $ignoredBy = null)
	{
		parent::__construct($app);

		$this->ignoredUser = $ignoredUser;
		$this->ignoredBy = $ignoredBy ?: \XF::visitor();
	}

	public function ignore()
	{
		$userIgnored = $this->em()->create('LiamW\IgnoreSignatures:IgnoredSignatures');
		$userIgnored->user_id = $this->ignoredBy->user_id;
		$userIgnored->ignored_user_id = $this->ignoredUser->user_id;
		$userIgnored->save(false); // TODO: catch duplicate error

		return $userIgnored;
	}

	public function unignore()
	{
		$userIgnored = $this->em()->findOne('LiamW\IgnoreSignatures:IgnoredSignatures', [
			'user_id' => $this->ignoredBy->user_id,
			'ignored_user_id' => $this->ignoredUser->user_id
		]);

		if ($userIgnored)
		{
			$userIgnored->delete(false);
		}

		return $userIgnored;
	}
}