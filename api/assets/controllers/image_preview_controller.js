import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview', 'placeholder'];

    connect() {
        // console.log('Image preview controller connected');
    }

    readURL(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = (e) => {
                if (this.hasPreviewTarget) {
                    this.previewTarget.src = e.target.result;
                    this.previewTarget.classList.remove('d-none');
                }

                if (this.hasPlaceholderTarget) {
                    this.placeholderTarget.classList.add('d-none');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
}
