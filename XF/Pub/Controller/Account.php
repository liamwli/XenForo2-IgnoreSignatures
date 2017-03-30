<?php

namespace LiamW\IgnoreSignatures\XF\Pub\Controller;

class Account extends XFCP_Account
{
	public function actionIgnoredSignatures()
	{
		$visitor = \XF::visitor();
		if ($ignoredSignatures = $visitor->Profile->ignored_signatures)
		{
			$ignoringUsers = $this->finder('XF:User')
				->where('user_id', array_keys($ignoredSignatures))
				->order('username')
				->fetch();
		}
		else
		{
			$ignoringUsers = [];
		}

		$viewParams = [
			'ignoring' => $ignoringUsers
		];
		$view = $this->view('LiamW\IgnoredSignatures:Account\IgnoredSignatures', 'liamw_ignoresignatures_account_ignored_signatures', $viewParams);
		return $this->addAccountWrapperParams($view, 'ignored_signatures');
	}
}