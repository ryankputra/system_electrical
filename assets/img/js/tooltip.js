/**
 * Initializes Bootstrap tooltips on all elements with the data-bs-toggle="tooltip" attribute.
 */
document.addEventListener("DOMContentLoaded", function () {
	var tooltipTriggerList = [].slice.call(
		document.querySelectorAll('[data-bs-toggle="tooltip"]')
	);
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl);
	});
});