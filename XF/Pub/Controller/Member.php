<?php

namespace LiamW\IgnoreSignatures\XF\Pub\Controller;

use XF\Entity\User;
use XF\Mvc\ParameterBag;

class Member extends XFCP_Member
{
	/**
	 * @param User $ignoreUser
	 *
	 * @return \LiamW\IgnoreSignatures\Service\User\IgnoreSignatures
	 */
	protected function setupIgnoreSignatureService(\XF\Entity\User $ignoreUser)
	{
		return $this->service('LiamW\IgnoreSignatures:User\IgnoreSignatures', $ignoreUser);
	}

	public function actionIgnoreSignature(ParameterBag $params)
	{
		$user = $this->assertViewableUser($params->user_id, [], true);
		/** @var \LiamW\IgnoreSignatures\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		$wasIgnoring = $visitor->isIgnoringSignature($user);

		if (!$wasIgnoring && !$visitor->canIgnoreSignature($user, $error))
		{
			return $this->noPermission($error);
		}

		$redirect = $this->getDynamicRedirect();

		if ($this->isPost() || $this->responseType() == 'json')
		{
			$ignoreSignatureService = $this->setupIgnoreSignatureService($user);

			if ($wasIgnoring)
			{
				$signatureIgnored = $ignoreSignatureService->unignore();
			}
			else
			{
				$signatureIgnored = $ignoreSignatureService->ignore();
			}

			if ($signatureIgnored->hasErrors())
			{
				return $this->error($signatureIgnored->getErrors());
			}

			$viewParams = [
				'user' => $user
			];

			$reply = $this->view('LiamW\IgnoreSignatures:Member\SignatureIgnore', 'liamw_ignoresignatures_signature_macro', $viewParams);
			//$reply->setJsonParam('switchKey', $wasIgnoring ? 'ignore' : 'unignore');

			return $reply;
		}
		else
		{
			$viewParams = [
				'user' => $user,
				'redirect' => $redirect,
				'isIgnoringSignature' => $wasIgnoring
			];

			return $this->view('LiamW\IgnoreSignatures:Member\IgnoreSignature', 'liamw_ignoresignatures_signature_ignore', $viewParams);
		}
	}
}