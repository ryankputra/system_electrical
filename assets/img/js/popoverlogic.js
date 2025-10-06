// Load popover configs
const popoverConfigs = window.popoverConfigs;

// Store initialized popovers here
const popovers = {};

// Initialize Bootstrap popovers for each configured filter
popoverConfigs.forEach((config) => {
	const trigger = document.getElementById(`${config.id}-trigger`);
	const content = document.getElementById(`${config.id}-filter`);

	const popover = new bootstrap.Popover(trigger, {
		html: true,
		placement: "bottom",
		title: config.title,
		content: content,
	});

	// Save reference by ID
	popovers[config.id] = popover;
});

/**
 * Function:hidePop
 *
 * Hides all popovers except the one passed
 *
 * @param {string} exceptId - The popover ID to exclude from hiding
 */
function hidePop(exceptId) {
	for (const [id, pop] of Object.entries(popovers)) {
		if (id !== exceptId) pop.hide();
	}
}