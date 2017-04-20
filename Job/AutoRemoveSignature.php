<?php

namespace LiamW\IgnoreSignatures\Job;

use XF\Job\AbstractJob;

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

		$ignoredSignatureCounts = $db->fetchPairs($db->limit("SELECT ignored_user_id, COUNT(*) FROM xf_liamw_ignored_signatures WHERE ignored_user_id > ? GROUP BY ignored_user_id ORDER BY ignored_user_id ASC", $this->data['batch']), $this->data['start']);

		if (!$ignoredSignatureCounts)
		{
			return $this->complete();
		}

		$signatureRemoveThreshold = \XF::options()->liamw_ignoresignatures_auto_remove;
		$warnThreshold = \XF::options()->liamw_ignoresignatures_warning_threshold;

		$done = 0;

		$conversationStarter = $this->app->em()
			->findOne('XF:User', ['username' => $this->app->options()->liamw_ignoresignatures_warning_sender]);

		foreach ($ignoredSignatureCounts AS $ignoredUserId => $count)
		{
			if (microtime(true) - $startTime >= $maxRunTime)
			{
				break;
			}

			$this->data['start'] = $ignoredUserId;

			/** @var \LiamW\IgnoreSignatures\XF\Entity\User $ignoredUser */
			$ignoredUser = \XF::em()->findOne('XF:User', ['user_id' => $ignoredUserId]);

			/** @var \XF\Entity\UserProfile $ignoredUserProfile */
			$ignoredUserProfile = $ignoredUser->getRelationOrDefault('Profile');

			if ($warnThreshold && $conversationStarter && !$ignoredUserProfile->signature_warning_sent && $count >= $warnThreshold)
			{
				/** @var \XF\Service\Conversation\Creator $conversationCreator */
				$conversationCreator = $this->app->service('XF:Conversation\Creator', $conversationStarter);
				$conversationCreator->setContent($this->app->language($ignoredUser->language_id)
					->phrase('liamw_ignoresignatures_warning_conversation_title', ['username' => $ignoredUser->username, 'userIgnoreCount' => $count, 'removeThreshold' => $signatureRemoveThreshold, 'warningThreshold' => $warnThreshold]), $this->app->language($ignoredUser->language_id)
					->phrase('liamw_ignoresignatures_warning_conversation_message', ['username' => $ignoredUser->username, 'userIgnoreCount' => $count, 'removeThreshold' => $signatureRemoveThreshold, 'warningThreshold' => $warnThreshold]));
				$conversationCreator->setLogIp(false);
				$conversationCreator->setAutoSpamCheck(false);
				$conversationCreator->setRecipientsTrusted($ignoredUser);
				$conversationCreator->save();

				$ignoredUserProfile->signature_warning_sent = 1;
			}
			else if ($signatureRemoveThreshold && $count >= $signatureRemoveThreshold)
			{
				$ignoredUserProfile->signature = '';
			}

			$ignoredUser->save();

			$done++;
		}

		$this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $startTime, $maxRunTime, 1000);

		return $this->resume();
	}

	public function getStatusMessage()
	{
		return \XF::phrase('liamw_ignoresignatures_processing_warnings_and_removals');
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