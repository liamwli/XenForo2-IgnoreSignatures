<?php

namespace LiamW\HideSignatures;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1()
	{
		$this->createTable('xf_liamw_hidesignatures_user_hidden_signature', function (Create $create)
		{
			$create->addColumn('user_id', 'int');
			$create->addColumn('hidden_user_id', 'int');
			$create->addPrimaryKey(['user_id', 'hidden_user_id']);
		});

		$this->alterTable('xf_user_profile', function (Alter $alter)
		{
			$alter->addColumn('liamw_hidesignatures_hidden_signatures', 'text')->after('ignored')->setDefault('[]');
			$alter->addColumn('liamw_hidesignatures_signature_warning_date', 'int')->after('liamw_hidesignatures_hidden_signatures')->nullable();
		});
	}

	public function uninstallStep1()
	{
		$this->schemaManager()->dropTable('xf_liamw_hidesignatures_user_hidden_signature');
		$this->schemaManager()->alterTable('xf_user_profile', function (Alter $alter)
		{
			$alter->dropColumns([
				'liamw_hidesignatures_hidden_signatures',
				'liamw_hidesignatures_signature_warning_date'
			]);
		});
	}
}