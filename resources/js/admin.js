import './loading';
import 'bootstrap';
import 'admin-lte';

document.addEventListener('submit', (event) => {
    const message = event.submitter?.dataset.confirm || event.target.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

const productPhotos = document.querySelector('[data-product-photos]');

if (productPhotos) {
    productPhotos.addEventListener('change', () => {
        const totalBytes = Array.from(productPhotos.files).reduce((total, file) => total + file.size, 0);
        productPhotos.setCustomValidity(totalBytes > 7 * 1024 * 1024 ? 'Ukuran seluruh foto dalam satu unggahan tidak boleh melebihi 7 MB.' : '');
        productPhotos.reportValidity();
    });
}

const faqList = document.querySelector('[data-faq-list]');
const faqTemplate = document.querySelector('#faq-template');

if (faqList && faqTemplate) {
    let nextIndex = Number(faqList.dataset.nextIndex);

    document.querySelector('[data-faq-add]').addEventListener('click', () => {
        if (faqList.children.length >= 30) return;

        const row = faqTemplate.content.cloneNode(true);
        for (const field of row.querySelectorAll('[name]')) {
            field.name = field.name.replace('__INDEX__', nextIndex);
            field.id = field.id.replace('__INDEX__', nextIndex);
        }
        for (const label of row.querySelectorAll('label')) {
            label.htmlFor = label.htmlFor.replace('__INDEX__', nextIndex);
        }

        faqList.append(row);
        faqList.lastElementChild.querySelector('input').focus();
        nextIndex += 1;
    });

    faqList.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-faq-remove]');
        if (!remove) return;

        remove.closest('[data-faq-row]').remove();
        document.querySelector('[data-faq-add]').focus();
    });
}
