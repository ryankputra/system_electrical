(function () {
	// Debug marker to ensure this file version is loaded in the browser
	try { console.debug && console.debug('notificationlogic.js (updated) loaded'); } catch(e) {}
	try {
		// Read duration from window; default to 3000ms
		const notificationDuration = (typeof window.notificationDuration !== 'undefined') ? Number(window.notificationDuration) : 3000;

		// Display notification (if present) and schedule fade-out
		const notif = document.getElementById("notification");
		if (notif) {
			// Use requestAnimationFrame to avoid layout thrashing when available
			if (typeof window.requestAnimationFrame === 'function') {
				window.requestAnimationFrame(() => {
					setTimeout(() => {
						notif.classList.add("cust-fade-out");
					}, notificationDuration);
				});
			} else {
				setTimeout(() => {
					notif.classList.add("cust-fade-out");
				}, notificationDuration);
			}
		}
	} catch (err) {
		console && console.error && console.error('notificationlogic error', err);
	}
})();