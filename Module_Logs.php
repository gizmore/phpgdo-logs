<?php
declare(strict_types=1);
namespace GDO\Logs;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Date\GDT_Duration;
use GDO\Mail\GDT_Email;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;

/**
 * Retains and exposes the filesystem logs written by GDO\Core\Logger.
 *
 * @version 7.0.3
 * @since 7.0.3
 * @author mira
 */
final class Module_Logs extends GDO_Module
{

	public int $priority = 90;

	public function onIncludeScripts(): void
	{
		$this->addCSS('css/logs.css');
		$this->addJS('js/logs.js');
	}

	public function getDependencies(): array
	{
		return ['CLI', 'Cronjob', 'ZIP'];
	}

	public function getFriendencies(): array
	{
		return ['Mail'];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/logs');
	}

	public function getConfig(): array
	{
		return [
			GDT_Duration::make('log_keep_for_time')->initial('7d')->min(0)->notNull(),
			GDT_Checkbox::make('log_by_mail')->initial('0'),
			GDT_Email::make('log_mail_to')->initial(GDO_ADMIN_EMAIL),
			GDT_Checkbox::make('log_delete_after_mail')->initial('0'),
		];
	}

	public function cfgLogKeepForTime(): int
	{
		return (int)$this->getConfigValue('log_keep_for_time');
	}

	public function cfgLogByMail(): bool
	{
		return $this->getConfigValue('log_by_mail');
	}

	public function cfgLogDeleteAfterMail(): bool
	{
		return $this->getConfigValue('log_delete_after_mail');
	}

	public function cfgLogMailTo(): ?string
	{
		return $this->getConfigVar('log_mail_to');
	}

	public function logsDir(): string
	{
		return GDO_PATH . 'protected/logs';
	}

	public function zippedDir(): string
	{
		return GDO_PATH . 'protected/zipped';
	}

	public function onInitSidebar(): void
	{
		if (GDO_User::current()->isUser())
		{
			GDT_Page::instance()->rightBar()->addField(
				GDT_Link::make('logs_overview')->href(href('Logs', 'Overview')));
		}
	}

}
