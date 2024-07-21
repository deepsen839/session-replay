/**
 * Aurora Heatmap
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

'use strict';

import { AuroraHeatmapAdmin } from './admin';
import { AuroraHeatmapViewer } from './viewer';
import { AuroraHeatmapReporter } from './reporter';

try {
	await awaitLoad(['aurora_heatmap']);
	const runner = startAuroraHeatmap(window.aurora_heatmap);
} catch (e) {
	console.error(e);
}

// Avoid optimization side effects.
function awaitLoad(modules = [], timeout = 10000) {
	const timestamp = Date.now() + timeout;

	// Wait Modules.
	const loads = modules.map((module) => {
		return new Promise((resolve, reject) => {
			const checker = () => {
				if (module in window) {
					return resolve(window[module]);
				} else if (timeout < Date.now()) {
					return reject(`Aurora Heatmap: Timeout while waiting for ${module}.`);
				} else {
					setTimeout(checker, 100);
				}
			};
			return checker();
		});
	});

	// Wait DOMContentLoaded.
	loads.push(new Promise((resolve, reject) => {
		if ('loading' !== document.readyState) {
			return resolve();
		}
		document.addEventListener('DOMContentLoaded', () => resolve());
	}));

	// Wait all.
	return Promise.all(loads);
}

// Start Aurora Heatmap.
function startAuroraHeatmap(config) {
	if (!config) {
		throw new Error('Aurora Heatmap: missing aurora_heatmap config.');
	}
	switch (config._mode) {
		case 'admin':
			return new AuroraHeatmapAdmin(config);
		case 'viewer':
			return new AuroraHeatmapViewer(config);
		case 'reporter':
			return new AuroraHeatmapReporter(config);
		default:
			throw new Error('Aurora Heatmap: invalid aurora_heatmap._mode.');
	}
}

/* vim: set ts=4 sw=4 sts=4 noet: */
