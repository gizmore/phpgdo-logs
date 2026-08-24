<?php
declare(strict_types=1);
namespace GDO\Logs\Method;

use GDO\Cronjob\MethodCronjob;
use GDO\Logs\Module_Logs;
use GDO\Mail\Mail;
use GDO\Util\FileUtil;
use GDO\ZIP\Module_ZIP;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Archives expired log files once per day and optionally mails the archive.
 *
 * @version 7.0.3
 * @since 7.0.3
 * @author mira
 */
final class Cronjob extends MethodCronjob
{

	public function runAt(): string
	{
		return $this->runDailyAt(2);
	}

	public function run(): void
	{
		if ($archive = $this->rotate())
		{
			$this->logNotice(sprintf('Archived logs to %s', basename($archive)));
			$this->mailArchive($archive);
		}
	}

	private function rotate(): ?string
	{
		$module = Module_Logs::instance();
		$logsDir = $module->logsDir();
		if (!is_dir($logsDir))
		{
			return null;
		}

		$cutoff = time() - $module->cfgLogKeepForTime();
		$files = [];
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($logsDir, RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ($iterator as $file)
		{
			/** @var SplFileInfo $file */
			if ($file->isFile() && $file->getMTime() < $cutoff)
			{
				$files[] = substr($file->getPathname(), strlen($logsDir) + 1);
			}
		}
		if (!$files)
		{
			return null;
		}

		FileUtil::createDir($module->zippedDir());
		$archive = sprintf('%s/logs.%s.zip', $module->zippedDir(), date('Ymd_His'));
		$zip = escapeshellarg(Module_ZIP::instance()->cfgZipPath());
		$archiveArg = escapeshellarg($archive);
		$fileArgs = implode(' ', array_map('escapeshellarg', $files));
		$command = sprintf('cd %s && %s -q %s %s', escapeshellarg($logsDir), $zip, $archiveArg, $fileArgs);
		exec($command, $output, $returnCode);
		if ($returnCode !== 0 || !is_file($archive))
		{
			$this->logError('Could not archive expired logs');
			return null;
		}
		foreach ($files as $file)
		{
			unlink("{$logsDir}/{$file}");
		}
		return $archive;
	}

	private function mailArchive(string $archive): void
	{
		$module = Module_Logs::instance();
		if (!$module->cfgLogByMail() || !module_enabled('Mail'))
		{
			return;
		}
		if (!($email = $module->cfgLogMailTo()))
		{
			return;
		}
		$mail = Mail::botMail();
		$mail->setReceiver($email);
		$mail->setSubject(t('mail_subj_logs_archive', [sitename()]));
		$mail->setBody(t('mail_body_logs_archive', [sitename()]));
		$mail->addAttachmentFile(basename($archive), $archive);
		if ($mail->sendAsText() && $module->cfgLogDeleteAfterMail())
		{
			unlink($archive);
		}
	}

}
