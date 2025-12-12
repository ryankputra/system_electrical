(function(){
	// local copy to avoid redeclaring a global identifier if this file is loaded multiple times
	const inputConfigs = window.inputConfigs || [];

	/**
	 * Function: showClearButtonsOnLoad
	 *
	 * Show clear button if the input has value on load
	 */
	function showClearButtonsOnLoad() {
		inputConfigs.forEach(({ id, button }) => {
			const input = document.getElementById(id);
			const clearBtn = document.getElementById(button);
			if (input && clearBtn && input.value) {
				clearBtn.style.display = "block";
			}
		});
	}

	// Call on page load
	try { showClearButtonsOnLoad(); } catch (e) { console.warn('[forminput] showClearButtonsOnLoad error', e); }

	/**
	 * Function: toggleClear
	 *
	 * Toggle clear button visibility on input change
	 */
	function toggleClear(id, button) {
		const input = document.getElementById(id);
		const clearBtn = document.getElementById(button);
		if (input && clearBtn) {
			clearBtn.style.display = input.value ? "block" : "none";
		}
	}

	/**
	 * Function: clearInput
	 *
	 * Clear input and hide button
	 */
	function clearInput(id, button) {
		const input = document.getElementById(id);
		const clearBtn = document.getElementById(button);
		if (input && clearBtn) {
			input.value = "";
			clearBtn.style.display = "none";
		}
	}

	// export to global so views can call these functions
	window.toggleClear = window.toggleClear || toggleClear;
	window.clearInput = window.clearInput || clearInput;
	window.showClearButtonsOnLoad = window.showClearButtonsOnLoad || showClearButtonsOnLoad;
})();