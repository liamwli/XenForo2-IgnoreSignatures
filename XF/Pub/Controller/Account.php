<?php

namespace LiamW\HideSignatures\XF\Pub\Controller;

use LiamW\HideSignatures\AddOn as HideSignatures;

class Account extends XFCP_Account
{
	public function actionHiddenSignatures()
	{
		$visitor = HideSignatures::visitor();
		if ($hiddenSignatures = $visitor->Profile->liamw_hidesignatures_hidden_signatures)
		{
			$hiddenSignatureUsers = $this->finder('XF:User')->where('user_id', array_keys($hiddenSignatures))->order('username')->fetch();
		}
		else
		{
			$hiddenSignatureUsers = [];
		}

		$viewParams = [
			'hidden' => $hiddenSignatureUsers
		];
		$view = $this->view('LiamW\HideSignatures:Account\HiddenSignatures', 'liamw_hidesignatures_account_hidden_signatures', $viewParams);
		return $this->addAccountWrapperParams($view, 'hidden_signatures');
	}
}