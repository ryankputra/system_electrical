/**
 * Function: resetFilter
 *
 * Resets all checked filters within a filter form and submits the form
 * only if at least one checkbox was previously selected.
 *
 * @param {string} formId - The ID of the form element to reset.
 */
function resetFilter(formId) {
	// Get HTML element form
	const form = document.getElementById(formId);

	// Get all currently checked checkboxes
	const checkedInputs = form.querySelectorAll(".form-check-input:checked");

	// Uncheck all
	form
		.querySelectorAll(".form-check-input")
		.forEach((input) => (input.checked = false));

	// Only submit if something was previously checked
	if (checkedInputs.length > 0) {
		form.submit();
	}
}