import { BrowserMultiFormatReader, BarcodeFormat, DecodeHintType } from '@zxing/library';

/**
 * Decodes the PDF417 barcode on the back of US state IDs so the guest's date
 * of birth / expiry / document number can be read reliably (Vision OCR can't
 * read the barcode). Exposes window.GuestIdBarcode.decode(dataUrl) -> Promise.
 */
(function () {
    let reader = null;

    function getReader() {
        if (reader) {
            return reader;
        }

        try {
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.PDF_417]);
            reader = new BrowserMultiFormatReader(hints);
        } catch (e) {
            reader = null;
        }

        return reader;
    }

    window.GuestIdBarcode = {
        decode: function (dataUrl) {
            return new Promise(function (resolve) {
                const r = getReader();
                if (!r || !dataUrl) {
                    resolve(null);
                    return;
                }

                r.decodeFromImageUrl(dataUrl)
                    .then(function (result) {
                        resolve(result ? result.getText() : null);
                    })
                    .catch(function () {
                        resolve(null);
                    });
            });
        },
    };
})();
