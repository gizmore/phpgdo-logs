<?php
use GDO\Core\GDT;

/** @var string[] $files */
/** @var ?string $file */
/** @var ?string $search */
/** @var ?string $content */
?>
<div class="gdo-logs">
	<form class="gdo-logs-search" method="get" action="<?= href('Logs', 'Overview') ?>">
		<label for="logs-search"><?= t('logs_search') ?></label>
		<input id="logs-search" name="search" type="search" value="<?= html($search ?: '') ?>" maxlength="256" />
		<button type="submit"><?= t('btn_search') ?></button>
	</form>
	<div class="gdo-logs-columns">
		<aside class="gdo-logs-files">
			<label for="logs-file-filter"><?= t('logs_filter_files') ?></label>
			<input id="logs-file-filter" type="search" autocomplete="off" />
			<?php if (!$files): ?>
				<p><?= t('logs_no_files') ?></p>
			<?php else: ?>
				<ul>
					<?php foreach ($files as $entry): ?>
						<li><a href="<?= href('Logs', 'Overview') ?>&file=<?= rawurlencode($entry) ?>"><?= html($entry) ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</aside>
		<section class="gdo-logs-viewer">
			<?php if ($search): ?>
				<h3><?= t('logs_search_result', [html($search)]) ?></h3>
				<pre class="gdo-logs-content"><?= html($content ?: t('logs_no_matches')) ?></pre>
			<?php elseif ($file && $content !== null): ?>
				<h3><?= html($file) ?></h3>
				<pre class="gdo-logs-content"><?= html($content) ?></pre>
			<?php else: ?>
				<p><?= t('logs_select_file') ?></p>
			<?php endif; ?>
		</section>
	</div>
</div>
