import { Controller } from '@hotwired/stimulus';
import Cropper from 'cropperjs';
import { Modal } from 'bootstrap';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'modal', 'image', 'previewAvatar', 'previewCover'];
    static values = {
        userId: Number,
        type: String
    }

    connect() {
        console.log('Profile image edit controller connected');
        this.bsModal = null;
    }

    openEdit(event) {
        console.log('Opening edit for type:', event.currentTarget.dataset.type);
        this.typeValue = event.currentTarget.dataset.type;
        this.inputTarget.click();
    }

    onFileChange(event) {
        const file = event.target.files[0];
        console.log('File selected:', file ? file.name : 'none');
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            console.log('File read complete, updating image source');
            this.imageTarget.src = e.target.result;

            if (!this.bsModal) {
                console.log('Initializing Bootstrap Modal');
                this.bsModal = new Modal(this.modalTarget);
            }
            console.log('Showing modal');
            this.bsModal.show();
        };
        reader.readAsDataURL(file);
    }

    onModalShow() {
        if (this.cropper) {
            this.cropper.destroy();
        }

        const isAvatar = this.typeValue === 'avatar';
        const aspectRatio = isAvatar ? 1 : 16 / 9; // approximate cover ratio

        this.cropper = new Cropper(this.imageTarget, {
            aspectRatio: aspectRatio,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: !isAvatar, // For avatar, we keep it fixed mostly
            cropBoxResizable: !isAvatar,
            toggleDragModeOnDblclick: false,
        });
    }

    save() {
        if (!this.cropper) return;

        const isAvatar = this.typeValue === 'avatar';
        const canvasOptions = isAvatar
            ? { width: 400, height: 400 }
            : { width: 1200, height: 675 };

        const canvas = this.cropper.getCroppedCanvas(canvasOptions);
        const base64Image = canvas.toDataURL('image/jpeg', 0.9);

        const saveBtn = this.modalTarget.querySelector('[data-action$="#save"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        fetch(`/api/profile-image/update/${this.typeValue}/${this.userIdValue}`, {
            method: 'POST',
            body: JSON.stringify({ image: base64Image }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cacheBuster = `?t=${new Date().getTime()}`;
                    const imageUrl = data.image_url + cacheBuster;

                    if (isAvatar) {
                        this.previewAvatarTargets.forEach(img => img.src = imageUrl);
                    } else {
                        this.previewCoverTargets.forEach(img => img.src = imageUrl);
                    }
                    this.bsModal.hide();
                    // Optional: show a small toast or flash
                } else {
                    alert('Error: ' + (data.error || 'Upload failed'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('A network error occurred.');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
    }

    disconnect() {
        if (this.cropper) {
            this.cropper.destroy();
        }
    }
}
