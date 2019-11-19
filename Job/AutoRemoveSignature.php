<?php

namespace LiamW\HideSignatures\Job;

use LiamW\HideSignatures\XF\Entity\User;
use XF;
use XF\Entity\UserProfile;
use XF\Job\AbstractJob;
use XF\Service\Conversation\Creator;

class AutoRemoveSignature extends AbstractJob
{
	protected $defaultData = [
		'batch' => 50,
		'start' => 0
	];

	public function run($maxRunTime)
	{
		$startTime = microtime(true);

		$db = $this->app->db();

		$hiddenSignatureCounts = $db->fetchPairs($db->limit("SELECT hidden_user_id, COUNT(*) FROM xf_liamw_hidesignatures_user_hidden_signature WHERE hidden_user_id > ? GROUP BY hidden_user_id ORDER BY hidden_user_id", $this->data['batch']), $this->data['start']);

		if (!$hiddenSignatureCounts)
		{
			return $this->complete();
		}

		$removeThreshold = XF::options()->liamw_hidesignatures_auto_remove;
		$warnThreshold = XF::options()->liamw_hidesignatures_warning_threshold;

		$done = 0;

		$warningConversationStarter = $this->app->em()->findOne('XF:User', ['username' => $this->app->options()->liamw_hidesignatures_warning_sender]);

		foreach ($hiddenSignatureCounts AS $hiddenUserId => $count)
		{
			if (microtime(true) - $startTime >= $maxRunTime)
			{
				break;
			}

			$this->data['start'] = $hiddenUserId;

			/** @var User $hiddenUser */
			$hiddenUser = XF::em()->findOne('XF:User', ['user_id' => $hiddenUserId]);

			/** @var UserProfile $hiddenUserProfile */
			$hiddenUserProfile = $hiddenUser->getRelationOrDefault('Profile');

			if ($warnThreshold && $count >= $warnThreshold && $warningConversationStarter && !$hiddenUserProfile->liamw_hidesignatures_signature_warning_date)
			{
				/** @var Creator $conversationCreator */
				$conversationCreator = $this->app->service('XF:Conversation\Creator', $warningConversationStarter);
				$conversationCreator->setIsAutomated();
				$conversationCreator->setContent($this->app->language($hiddenUser->language_id)->phrase('liamw_hidesignatures_warning_conversation_title', [
					'username' => $hiddenUser->username,
					'userIgnoreCount' => $count,
					'removeThreshold' => $removeThreshold,
					'warningThreshold' => $warnThreshold
				]), $this->app->language($hiddenUser->language_id)->phrase('liamw_hidesignatures_warning_conversation_message', [
					'username' => $hiddenUser->username,
					'userIgnoreCount' => $count,
					'removeThreshold' => $removeThreshold,
					'warningThreshold' => $warnThreshold
				]));
				$conversationCreator->setRecipientsTrusted($hiddenUser);
				$conversationCreator->save();

				$hiddenUserProfile->liamw_hidesignatures_signature_warning_date = XF::$time;
			}
			else if ($removeThreshold && $count >= $removeThreshold)
			{
				$hiddenUserProfile->signature = '';
			}

			$hiddenUser->save();

			$done++;
		}

		$this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $startTime, $maxRunTime, 1000);

		return $this->resume();
	}

	public function getStatusMessage()
	{
		return \XF::phrase('liamw_hidesignatures_processing_hidden_signature_removals');
	}

	public function canCancel()
	{
		return false;
	}

	public function canTriggerByChoice()
	{
		return false;
	}
}