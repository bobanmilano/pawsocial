import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const settings = {
            render: {
                option: function (data, escape) {
                    const flagCode = data.value.toLowerCase();
                    // Handle special cases if needed (e.g. 'en' for language vs 'gb' for flag)
                    let iconCode = flagCode;
                    if (iconCode === 'en') iconCode = 'gb';
                    if (iconCode === 'uk') iconCode = 'ua'; // Ukrainian language uk -> flag ua
                    if (iconCode === 'el') iconCode = 'gr'; // Greek language el -> flag gr
                    if (iconCode === 'ga') iconCode = 'ie'; // Irish language ga -> flag ie
                    if (iconCode === 'cs') iconCode = 'cz'; // Czech cs -> flag cz
                    if (iconCode === 'da') iconCode = 'dk'; // Danish da -> flag dk
                    if (iconCode === 'et') iconCode = 'ee'; // Estonian et -> flag ee
                    if (iconCode === 'sv') iconCode = 'se'; // Swedish sv -> flag se
                    if (iconCode === 'sl') iconCode = 'si'; // Slovenian sl -> flag si
                    if (iconCode === 'sr') iconCode = 'rs'; // Serbian sr -> flag rs

                    return `<div><span class="fi fi-${iconCode} me-2"></span>${escape(data.text)}</div>`;
                },
                item: function (data, escape) {
                    const flagCode = data.value.toLowerCase();
                    let iconCode = flagCode;
                    if (iconCode === 'en') iconCode = 'gb';
                    if (iconCode === 'uk') iconCode = 'ua';
                    if (iconCode === 'el') iconCode = 'gr';
                    if (iconCode === 'ga') iconCode = 'ie';
                    if (iconCode === 'cs') iconCode = 'cz';
                    if (iconCode === 'da') iconCode = 'dk';
                    if (iconCode === 'et') iconCode = 'ee';
                    if (iconCode === 'sv') iconCode = 'se';
                    if (iconCode === 'sl') iconCode = 'si';
                    if (iconCode === 'sr') iconCode = 'rs';

                    return `<div><span class="fi fi-${iconCode} me-2"></span>${escape(data.text)}</div>`;
                }
            }
        };

        this.tomSelect = new TomSelect(this.element, settings);
    }

    disconnect() {
        if (this.tomSelect) {
            this.tomSelect.destroy();
        }
    }
}
