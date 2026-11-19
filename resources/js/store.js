import './loading';
const headerToggles = [...document.querySelectorAll('[data-menu-toggle], [data-search-toggle]')];

function setHeaderPanel(toggle, open) {
    const panel = document.getElementById(toggle.getAttribute('aria-controls'));
    toggle.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
    if (open) panel.querySelector('input')?.focus();
}

headerToggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        headerToggles.forEach((item) => setHeaderPanel(item, item === toggle && open));
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const toggle = headerToggles.find((item) => item.getAttribute('aria-expanded') === 'true');
    if (toggle) {
        setHeaderPanel(toggle, false);
        toggle.focus();
    }
});

document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const main = gallery.querySelector('[data-gallery-main]');
    const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const photo = thumb.querySelector('img');
            main.src = photo.src;
            main.alt = photo.alt;
            thumbs.forEach((item) => item.setAttribute('aria-pressed', String(item === thumb)));
        });
    });
});

document.querySelectorAll('[data-quantity]').forEach((control) => {
    const input = control.querySelector('input');
    control.querySelectorAll('[data-step]').forEach((button) => {
        button.addEventListener('click', () => {
            if (input.disabled) return;
            const value = input.valueAsNumber || 1;
            const min = Number(input.min || 1);
            const max = input.max ? Number(input.max) : Number.POSITIVE_INFINITY;
            input.value = Math.min(max, Math.max(min, value + Number(button.dataset.step)));
        });
    });
});

const product = document.querySelector('[data-product]');
const variant = product?.querySelector('[data-variant-select]');

if (variant) {
    const price = product.querySelector('[data-variant-price]');
    const fulfillment = product.querySelector('[data-variant-fulfillment]');
    const sku = product.querySelector('[data-variant-sku]');
    const quantity = product.querySelector('[name="quantity"]');
    const submit = product.querySelector('[data-add-to-cart]');

    const updateVariant = () => {
        const option = variant.selectedOptions[0];
        const stock = Number(option.dataset.stock);
        price.textContent = option.dataset.price;
        fulfillment.textContent = option.dataset.fulfillment;
        sku.textContent = option.dataset.sku;

        if (quantity) {
            quantity.disabled = stock < 1;
            quantity.max = stock;
            if (stock > 0) quantity.value = Math.min(Number(quantity.value || 1), stock);
            submit.disabled = stock < 1;
            product.querySelectorAll('[data-step]').forEach((button) => { button.disabled = stock < 1; });
        }
    };

    variant.addEventListener('change', updateVariant);
    updateVariant();
}

const checkout = document.querySelector('[data-checkout]');

if (checkout) {
    const rates = checkout.querySelectorAll('[data-rate]');
    const address = checkout.querySelector('[data-address]');
    const province = checkout.querySelector('[data-province]');
    const pickupNote = checkout.querySelector('[data-pickup-note]');
    const shippingFee = checkout.querySelector('[data-shipping-fee]');
    const total = checkout.querySelector('[data-order-total]');
    const subtotal = Number(checkout.dataset.subtotal);
    const formatAmount = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const rupiah = (amount) => `Rp${formatAmount.format(amount)}`;

    const updateRates = () => {
        const rate = checkout.querySelector('[data-rate]:checked');
        const pickup = rate?.dataset.pickup === 'true';
        const fee = Number(rate?.dataset.fee || 0);
        const region = pickup ? '' : rate?.dataset.region;

        address.hidden = pickup;
        address.querySelectorAll('[data-delivery-required]').forEach((field) => {
            field.disabled = pickup;
            field.required = Boolean(rate) && !pickup;
        });
        if (region) province.value = region;
        province.readOnly = Boolean(region);
        if (pickupNote) pickupNote.hidden = !pickup;
        shippingFee.textContent = rate ? rupiah(fee) : 'Pilih metode';
        total.textContent = rupiah(subtotal + fee);
    };

    rates.forEach((rate) => rate.addEventListener('change', updateRates));
    updateRates();
}
