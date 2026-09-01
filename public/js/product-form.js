let variantIndex = 0;

const categorySelect = document.getElementById('category_id');
const subcategorySelect = document.getElementById('subcategory_id');
const childSelect = document.getElementById('child_category_id');

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
}

function loadChildren(parentId, targetSelect, selectedId = null) {
    resetSelect(targetSelect, '-- None --');
    if (!parentId) return Promise.resolve();

    return fetch(`/admin/categories/${parentId}/children`)
        .then(res => res.json())
        .then(items => {
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (selectedId && item.id == selectedId) option.selected = true;
                targetSelect.appendChild(option);
            });
        });
}

categorySelect.addEventListener('change', function () {
    loadChildren(this.value, subcategorySelect);
    resetSelect(childSelect, '-- None --');
});

subcategorySelect.addEventListener('change', function () {
    loadChildren(this.value, childSelect);
});

function addVariantRow(prefill = null) {
    const template = document.getElementById('variantRowTemplate');
    const clone = template.content.cloneNode(true);

    const radio = clone.querySelector('input[name="variant_default"]');
    radio.value = variantIndex;

    const weightSelect = clone.querySelector('select[name="variant_weight_id[]"]');
    const eggSelect = clone.querySelector('select[name="variant_egg_type[]"]');

    if (prefill) {
        // Options already render ho chuke hain template me — sirf value set karo
        weightSelect.value = String(prefill.weight_id);
        eggSelect.value = prefill.egg_type;

        clone.querySelector('input[name="variant_price[]"]').value = prefill.price;
        clone.querySelector('input[name="variant_discount_price[]"]').value = prefill.discount_price ?? '';
        clone.querySelector('input[name="variant_stock[]"]').value = prefill.stock;
        if (prefill.is_default) radio.checked = true;
    }

    document.getElementById('variantRows').appendChild(clone);
    variantIndex++;
}

document.addEventListener('DOMContentLoaded', async function () {
    // Edit mode: restore category cascade
    if (window.existingCategorySelection) {
        const sel = window.existingCategorySelection;
        if (sel.category_id) {
            await loadChildren(sel.category_id, subcategorySelect, sel.subcategory_id);
        }
        if (sel.subcategory_id) {
            await loadChildren(sel.subcategory_id, childSelect, sel.child_category_id);
        }
    }

    // Edit mode: restore existing variants, else start with one empty row
    if (window.existingVariants && window.existingVariants.length) {
        window.existingVariants.forEach(v => addVariantRow(v));
    } else {
        addVariantRow();
    }
});

function deleteExistingImage(imageId, btn) {
    if (!confirm('Delete this image?')) return;

    fetch(`/admin/products/${imageId}/delete-image`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.closest('.col-4').remove();
            }
        });
}