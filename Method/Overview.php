<?php
declare(strict_types=1);
namespace GDO\Logs\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Core\GDT_Template;
use GDO\Core\Method;
use GDO\Logs\Module_Logs;
use GDO\User\GDO_User;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Web-only view of the current user's logs, or all logs for staff.
 *
 * @version 7.0.3
 * @since 7.0.3
 * @author mira
 */
final class Overview extends Method
{

	public function isCLI(): bool { return false; }

	public function isShownInSitemap(): bool { return true; }

	public function gdoParameters(): array
	{
		return [
			GDT_String::make('file')->max(512),
			GDT_String::make('search')->max(256),
		];
	}

	public function execute(): GDT
	{
		$user = GDO_User::current();
		if (!$user->isUser())
		{
			return $this->error('err_login');
		}
		$files = $this->logFiles($user);
		$file = $this->gdoParameterVar('file', false);
		$search = $this->gdoParameterVar('search', false);
		$content = $search ? $this->grepLogs($search, $files) : ($file ? $this->readLog($file, $files) : null);
		return GDT_Template::make()->template('Logs', 'overview.php', [
			'files' => $files,
			'file' => $file,
			'search' => $search,
			'content' => $content,
		]);
	}

	private function logFiles(GDO_User $user): array
	{
		$base = Module_Logs::instance()->logsDir();
		if (!$user->isStaff())
		{
			$base .= '/memberlog/' . $user->getUserName();
		}
		if (!is_dir($base))
		{
			return [];
		}
		$files = [];
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ($iterator as $entry)
		{
			/** @var SplFileInfo $entry */
			if ($entry->isFile())
			{
				$files[] = substr($entry->getPathname(), strlen(Module_Logs::instance()->logsDir()) + 1);
			}
		}
		rsort($files);
		return $files;
	}

	private function readLog(string $file, array $files): ?string
	{
		if (!in_array($file, $files, true))
		{
			return null;
		}
		$path = Module_Logs::instance()->logsDir() . '/' . $file;
		$content = file_get_contents($path, false, null, 0, 1024 * 1024);
		return $content === false ? null : $content;
	}

	private function grepLogs(string $search, array $files): string
	{
		if (!$files)
		{
			return '';
		}
		$logsDir = Module_Logs::instance()->logsDir();
		$paths = array_map(static fn(string $file): string => escapeshellarg("{$logsDir}/{$file}"), $files);
		$command = sprintf('grep -n -i -I -H -- %s %s', escapeshellarg($search), implode(' ', $paths));
		exec($command, $output, $returnCode);
		return str_replace($logsDir . '/', '', implode(PHP_EOL, $output));
	}

}
