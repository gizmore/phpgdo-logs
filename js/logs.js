"use strict";

document.addEventListener('DOMContentLoaded', () => {
	const filter = document.getElementById('logs-file-filter');
	const files = document.querySelectorAll('.gdo-logs-files li');
	if (!filter || !files.length) {
		return;
	}
	filter.addEventListener('input', () => {
		const query = filter.value.trim().toLocaleLowerCase();
		files.forEach((entry) => {
			entry.hidden = query !== '' && !entry.textContent.toLocaleLowerCase().includes(query);
		});
	});
});
