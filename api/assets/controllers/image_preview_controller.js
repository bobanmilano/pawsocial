import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview'];

    connect() {
        // Optional: log to check connection
        // console.log('Image preview controller connected');
    }

    readURL(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.previewTarget.src = e.target.result;
                this.previewTarget.classList.remove('d-none');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
}
