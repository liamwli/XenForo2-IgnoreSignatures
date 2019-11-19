<?php

namespace LiamW\HideSignatures\XF\Pub\Controller;

use LiamW\HideSignatures\AddOn as HideSignatures;
use LiamW\HideSignatures\Service\User\HideSignature;
use XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Service\AbstractService;

class Member extends XFCP_Member
{
	/**
	 * @param User $hideUser
	 *
	 * @return AbstractService|HideSignature
	 */
	protected function setupHideSignatureService(User $hideUser)
	{
		return $this->service('LiamW\HideSignatures:User\HideSignature', $hideUser);
	}

	public function actionHideSignature(ParameterBag $params)
	{
		$this->assertRegistrationRequired();

		$user = $this->assertViewableUser($params->user_id, [], true);

		$visitor = HideSignatures::visitor();

		$isHidden = $visitor->isHidingSignature($user);

		if (!$isHidden && !$visitor->canHideSignature($user, $error))
		{
			return $this->noPermission($error);
		}

		$ignoreSignatureService = $this->setupHideSignatureService($user);

		$signatureHidden = $isHidden ? $ignoreSignatureService->show() : $ignoreSignatureService->hide();
		if ($signatureHidden->hasErrors())
		{
			return $this->error($signatureHidden->getErrors());
		}

		$reply = $this->redirect($this->getDynamicRedirect(), '');
		$this->app->templater()->addDefaultParam('xf', \XF::app()->getGlobalTemplateData($reply));
		$reply->setJsonParams([
			'html' => $this->app->templater()->renderTemplate('public:liamw_hidesignatures_signature_macro', [
				'user' => $user,
				'isHidden' => !$isHidden
			])
		]);

		return $reply;
	}
}