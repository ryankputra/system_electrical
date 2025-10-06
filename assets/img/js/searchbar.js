document.addEventListener('DOMContentLoaded', function () {
    // Cache elements (safely)
    const searchBar = document.getElementById('search-bar');
    const clearButton = document.getElementById('clear-button');
    const searchForm = document.getElementById('search-form');

    // If search bar is not present on this page, exit silently
    if (!searchBar) {
        // Provide no-op functions so inline calls won't throw
        window.displayClear = function () {};
        window.clearKeyword = function () {};
        return;
    }

    // Initialize keyword and UI
    let keyword = searchBar.value || '';
    if (clearButton && keyword) clearButton.style.display = 'block';

    /**
     * Function: displayClear
     *
     * Toggle the visibility of the clear button
     */
    function displayClear() {
        if (!clearButton) return;
        clearButton.style.display = searchBar.value ? 'block' : 'none';
    }

    /**
     * Function: clearKeyword
     *
     * Clears the keyword and refreshes the search
     */
    function clearKeyword() {
        // Clear input field
        searchBar.value = '';

        // Immediately reflect UI
        displayClear();

        // Only submit if keyword existed initially
        if (keyword && searchForm) {
            try {
                searchForm.submit();
            } catch (e) {
                // ignore submit errors
            }
        }
    }

    // Expose to global scope because views call these inline
    window.displayClear = displayClear;
    window.clearKeyword = clearKeyword;
});