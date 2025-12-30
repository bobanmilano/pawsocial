import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        title: String,
        text: String,
        url: String
    }

    async share(event) {
        event.preventDefault();

        // Check if Web Share API is supported
        if (navigator.share) {
            try {
                await navigator.share({
                    title: this.titleValue,
                    text: this.textValue,
                    url: this.urlValue
                });
            } catch (err) {
                // User cancelled or error, ignore
                console.log('Share cancelled');
            }
        } else {
            // Fallback: Copy to clipboard
            try {
                await navigator.clipboard.writeText(this.urlValue);
                alert('Link copied to clipboard! 📋');
            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Could not copy link. Manually copy this URL: ' + this.urlValue);
            }
        }
    }
}
