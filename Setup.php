<?php

namespace LiamW\IgnoreSignatures;

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
		$this->getSchemaManager()->createTable('xf_liamw_ignored_signatures', function (Create $create)
		{
			$create->comment("Added by Ignored Signatures.");
			$create->addColumn('user_id', 'int')->unsigned();
			$create->addColumn('ignored_user_id', 'varchar', 75);
			$create->addPrimaryKey(['user_id', 'ignored_user_id']);
		});
	}

	public function installStep2()
	{
		$this->getSchemaManager()->alterTable('xf_user_profile', function (Alter $alter)
		{
			$alter->addColumn('ignored_signatures', 'text')->after('ignored')
				->comment("Added by Ignored Signatures. Comma separated integers from xf_liamw_ignored_signatures.");
			$alter->addColumn('signature_warning_sent', 'bool')->after('ignored_signatures')
				->setDefault(0)->comment("Added by Ignored Signatures");
		});

		$this->db()->update('xf_user_profile', ['ignored_signatures' => 'a:0:{}']);
	}

	public function uninstallStep1()
	{
		$sm = $this->getSchemaManager();

		$sm->dropTable('xf_liamw_ignored_signatures');
		$sm->alterTable('xf_user_profile', function (Alter $alter)
		{
			$alter->dropColumns('ignored_signatures');
		});
	}

	protected function getSchemaManager()
	{
		return $this->db()->getSchemaManager();
	}
}