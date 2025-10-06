(function () {
	// Debug marker to ensure this file version is loaded in the browser
	try { console.debug && console.debug('popoverlogic.js (updated) loaded'); } catch(e) {}
	// Protect top-level names by scoping inside an IIFE so this file can be included multiple times
	try {
		// Load popover configs (fall back to empty array)
		const popoverConfigs = window.popoverConfigs || [];

		// Store initialized popovers here (local to this module)
		const popovers = {};

		// Initialize Bootstrap popovers for each configured filter
		popoverConfigs.forEach((config) => {
			try {
				const trigger = document.getElementById(`${config.id}-trigger`);
				const content = document.getElementById(`${config.id}-filter`);

				if (!trigger || !content) return; // skip if elements not present on page

				const popover = new bootstrap.Popover(trigger, {
					html: true,
					placement: "bottom",
					title: config.title,
					content: content,
				});

				// Save reference by ID
				popovers[config.id] = popover;
			} catch (e) {
				// Ignore individual popover init errors
				console && console.warn && console.warn('popover init failed', e);
			}
		});

		// Expose a safe hidePop function on window so other code can call it without creating globals here
		window.hidePop = function (exceptId) {
			for (const [id, pop] of Object.entries(popovers)) {
				if (id !== exceptId && pop && typeof pop.hide === 'function') pop.hide();
			}
		};
	} catch (err) {
		console && console.error && console.error('popoverlogic error', err);
	}
})();