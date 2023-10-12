(function( $ ) {
	'use strict';

/**
 * RSSValidation class handles validation of the RSS URL.
 */
class RSSValidation {
    /**
     * Initializes the RSSValidation class.
     *
     * @param {string} submitButtonId - ID of the submit button.
     * @param {string} rssInputId - ID of the RSS input field.
     * @param {string} validRssUrl - The allowed URL for validation.
     */
    constructor(submitButtonId, rssInputId, validRssUrl) {
        this.submitButton = document.getElementById(submitButtonId);
        this.rssInput = document.getElementById(rssInputId);
        this.validRssUrl = validRssUrl;

        // Attach event handlers
        this.bindEvents();
    }

    /**
     * Binds necessary event handlers.
     */
    bindEvents() {
        this.submitButton.addEventListener("click", (e) => this.validateRssUrl(e));
    }

    /**
     * Validates the RSS URL.
     *
     * @param {Event} e - The event object.
     */
    validateRssUrl(e) {
        const rssValue = this.rssInput.value.trim();
        if (rssValue !== this.validRssUrl) {
            // Prevent form submission
            e.preventDefault();
            
            // Notify user about the allowed URL
            alert(`For this experiment, you can only select ${this.validRssUrl}`);
        }
    }
}

// This ensures the script executes when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Initialize the RSSValidation class with appropriate IDs and valid RSS URL
    const rssValidator = new RSSValidation("submit-button", "rss-url", "https://www.dailymail.co.uk/articles.rss");
});


})( jQuery );
