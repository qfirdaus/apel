// ==========================================
// 1. FUNGSI TERJEMAHAN GLOBAL (DWI-BAHASA)
// ==========================================
window.TEKS_BAHASA = window.TEKS_BAHASA || {};
window.t = function (key, params) {
    var text = Object.prototype.hasOwnProperty.call(window.TEKS_BAHASA, key) ? window.TEKS_BAHASA[key] : key;
    if (params) {
        Object.keys(params).forEach(function (p) {
            text = text.split('{' + p + '}').join(params[p]);
        });
    }
    return text;
};

// ==========================================
// 2. FUNGSI INISIALISASI DATEPICKER GLOBAL
// ==========================================
function initDatePicker(parent = document) {
    if (!jQuery.fn.daterangepicker) return;

    jQuery(parent).find('.datepicker').each(function () {
        // Prevent duplicate init
        if (jQuery(this).data('daterangepicker')) {
            return;
        }

        var parentModal = jQuery(this).closest('.modal');
        var cancelTxt = (typeof window.t === 'function') ? window.t('js_datepicker_clear') : 'Clear';

        jQuery(this).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            showDropdowns: true,
            autoUpdateInput: false, // Membenarkan input dikosongkan
            parentEl: parentModal.length ? parentModal : 'body',
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: cancelTxt
            }
        });

        // Apabila tarikh dipilih
        jQuery(this).on('apply.daterangepicker', function(ev, picker) {
            jQuery(this).val(picker.startDate.format('DD/MM/YYYY'));
        });

        // Apabila butang 'Clear' ditekan
        jQuery(this).on('cancel.daterangepicker', function(ev, picker) {
            jQuery(this).val('');
        });
    });
}

// ==========================================
// 3. FUNGSI-FUNGSI GLOBAL LAIN
// ==========================================
jQuery(document).ready(function ($) {
    
    // Inisialisasi Select2 secara Automatik
    if ($.fn.select2) {
        $('.select2').each(function () {
            var $this = $(this);
            var $parentModal = $this.closest('.modal'); 
            
            // Simbol OR (||) dibetulkan
            var placeholderText = $this.data('placeholder') || $this.find('option:first').text() || window.t('other_skill_choose');
            
            // $this.select2 dipisahkan ke baris baharu
            $this.select2({
                placeholder: placeholderText,
                allowClear: false,
                width: '100%',
                dropdownParent: $parentModal.length ? $parentModal : null
            });
        });    
    }

    // Inisialisasi DatePicker semasa page load (untuk form di luar modal)
    initDatePicker(document);
});

// Automatik Inisialisasi DatePicker apabila mana-mana Bootstrap Modal dibuka
document.addEventListener('shown.bs.modal', function (event) {
    const modal = event.target;
    initDatePicker(modal);
});

// Force Uppercase Input
document.addEventListener('input', function (e) {
    if (e.target.matches('input.uppercase')) {
        e.target.value = e.target.value.toUpperCase();
    }
});


// ==========================================
// 4. FEEDBACK SUBMIT & LOADER ASAL
// ==========================================
function shouldUseLocalSubmitFeedback() {
    const currentPath = String(window.location.pathname || '').replace(/\\/g, '/');
    return /\/pages\/(?:iStar|rekod-utama|iCareS)\//.test(currentPath);
}

function findRelatedSkeleton(form) {
    let sibling = form.previousElementSibling;
    while (sibling) {
        if (sibling.classList && sibling.classList.contains('skeleton-loader')) {
        return sibling;
        }
        sibling = sibling.previousElementSibling;
    }
    return form.parentElement ? form.parentElement.querySelector('.skeleton-loader') : null;
}

function resolveSubmitButton(form, submitEvent) {
    if (submitEvent && submitEvent.submitter instanceof HTMLElement) {
        return submitEvent.submitter;
    }
    return form.querySelector('button[type="submit"], input[type="submit"]');
}

function setLocalSubmitFeedback(form, submitEvent) {
    if (!shouldUseLocalSubmitFeedback()) return;
    if (form.dataset.localSubmitPending === '1') return;

    const method = String(form.getAttribute('method') || 'get').toLowerCase();
    const action = String(form.getAttribute('action') || '').trim();
    if (method !== 'post' || action === '' || action === '#') return;

    form.dataset.localSubmitPending = '1';
    form.setAttribute('aria-busy', 'true');

    const skeleton = findRelatedSkeleton(form);
    if (skeleton) {
        skeleton.style.display = 'block';
    }

    form.style.opacity = '0.55';
    form.style.pointerEvents = 'none';

    const submitButton = resolveSubmitButton(form, submitEvent);
    if (!submitButton) return;

    if (submitButton.tagName === 'BUTTON') {
        submitButton.dataset.originalHtml = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +'Sedang diproses...';
    } else {
        submitButton.dataset.originalValue = submitButton.value;
        submitButton.value = 'Sedang diproses...';
    }

    submitButton.disabled = true;
}

function showLoading(messageKey = 'processing') {
  const message = (typeof msg_load !== 'undefined' && msg_load[messageKey]) ? msg_load[messageKey] : 'Sedang diproses...'; 
  
  hideLoading();
  if (window.AppLoader && typeof window.AppLoader.show === 'function') {
    window.__userListLoaderToken = window.AppLoader.show(message);
    return;
  }
  if (window.IQSLoader && typeof window.IQSLoader.show === 'function') {
    window.__userListLoaderToken = window.IQSLoader.show(message);
  }
}

function hideLoading() {
  if (!window.__userListLoaderToken) {
    return;
  }
  if (window.AppLoader && typeof window.AppLoader.hide === 'function') {
    window.AppLoader.hide(window.__userListLoaderToken);
  } else if (window.IQSLoader && typeof window.IQSLoader.hide === 'function') {
    window.IQSLoader.hide(window.__userListLoaderToken);
  }
  window.__userListLoaderToken = null;
}

function resolveLoadMessage(messageKey) {
    if (typeof msg_load !== 'undefined' && msg_load && msg_load[messageKey]) {
        return msg_load[messageKey];
    }
    if (typeof msg_load !== 'undefined' && msg_load && msg_load.processing) {
        return msg_load.processing;
    }
    return 'Sedang diproses...';
}

function renderInlineLoader(message) {
    return `
        <div class="konvo-inline-loader" role="status" aria-live="polite">
            <div class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></div>
            <span>${message}</span>
        </div>
    `;
}

function setSectionLoading(container, messageKey = 'loading') {
    if (!container) return;
    container.innerHTML = renderInlineLoader(resolveLoadMessage(messageKey));
}

function setButtonBusy(button, isBusy, messageKey = 'processing') {
    const btn = button && button.jquery ? button : jQuery(button);
    if (!btn.length) return;

    if (isBusy) {
        if (!btn.data('original-html')) {
            btn.data('original-html', btn.html());
        }
        btn.prop('disabled', true);
        btn.addClass('is-busy');
        btn.html(`
            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
            <span>${resolveLoadMessage(messageKey)}</span>
        `);
        return;
    }

    const originalHtml = btn.data('original-html');
    if (originalHtml) {
        btn.html(originalHtml);
        btn.removeData('original-html');
    }
    btn.prop('disabled', false);
    btn.removeClass('is-busy');
}

function showToast(message, type = 'success') {
    let bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
    const toast = `
        <div class="toast align-items-center text-white ${bgClass} border-0 show mb-2">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    jQuery('.toast-lite').append(toast);
    setTimeout(() => {
        jQuery('.toast-lite .toast').first().remove();
    }, 2500);
}