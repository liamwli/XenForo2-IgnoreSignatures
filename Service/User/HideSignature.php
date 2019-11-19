<?php

namespace LiamW\HideSignatures\Service\User;

use LiamW\HideSignatures\Entity\UserHiddenSignature;
use XF;
use XF\App;
use XF\Entity\User;
use XF\Service\AbstractService;

class HideSignature extends AbstractService
{
	/**
	 * @var User
	 */
	protected $hiddenBy;

	/**
	 * @var User
	 */
	protected $hiddenUser;

	public function __construct(App $app, User $hiddenUser, User $hiddenBy = null)
	{
		parent::__construct($app);

		$this->hiddenUser = $hiddenUser;
		$this->hiddenBy = $hiddenBy ?? XF::visitor();
	}

	public function hide()
	{
		/** @var UserHiddenSignature $userHidden */
		$userHidden = $this->em()->create('LiamW\HideSignatures:UserHiddenSignature');
		$userHidden->user_id = $this->hiddenBy->user_id;
		$userHidden->hidden_user_id = $this->hiddenUser->user_id;
		$userHidden->save(false);

		return $userHidden;
	}

	public function show()
	{
		$userHidden = $this->em()->findOne('LiamW\HideSignatures:UserHiddenSignature', [
			'user_id' => $this->hiddenBy->user_id,
			'hidden_user_id' => $this->hiddenUser->user_id
		]);

		if ($userHidden)
		{
			$userHidden->delete();
		}

		return $userHidden;
	}
}