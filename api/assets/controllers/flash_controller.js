import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        duration: { type: Number, default: 5000 }
    }

    connect() {
        setTimeout(() => {
            this.dismiss();
        }, this.durationValue);
    }

    dismiss() {
        // Add a fade-out animation class or just remove it
        this.element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
        this.element.style.opacity = '0';
        this.element.style.transform = 'translateY(-20px)';

        setTimeout(() => {
            this.element.remove();
        }, 500);
    }
}
