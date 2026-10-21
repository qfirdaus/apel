<!-- MODAL 1: PILIHAN PEMBAYARAN -->
<div class="modal fade" id="modalPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="modal-title fw-bold fs-4"><?= h(__('reg_pay_method_title')) ?></h5>
                <button type="button" class="btn-close bg-light rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4"><?= str_replace('{amount}', number_format($portfolioFee ?? 300, 2), __('reg_pay_method_text')) ?></p>
                
                <div class="d-grid gap-3">
                    <!-- Option 1: FPX (DITAMBAH data-bs-dismiss="modal") -->
                    <button type="button" class="btn btn-outline-primary p-4 text-start d-flex align-items-center rounded-4 btn-pay-fpx border-2 transition-all hover-elevate" data-bs-dismiss="modal">
                        <div class="bg-primary-subtle p-3 rounded-circle me-4">
                            <i class="ri-bank-card-line fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold"><?= h(__('reg_pay_fpx_title')) ?></h5>
                            <span class="text-muted small"><?= h(__('reg_pay_fpx_desc')) ?></span>
                        </div>
                        <i class="ri-arrow-right-line fs-3 ms-auto text-primary"></i>
                    </button>
                    
                    <!-- Option 2: Resit Manual (DITAMBAH data-bs-dismiss="modal") -->
                    <button type="button" class="btn btn-outline-secondary p-4 text-start d-flex align-items-center rounded-4 btn-pay-manual border-2 transition-all hover-elevate" data-bs-dismiss="modal">
                        <div class="bg-secondary-subtle p-3 rounded-circle me-4">
                            <i class="ri-file-upload-line fs-2 text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold"><?= h(__('reg_pay_upload_title')) ?></h5>
                            <span class="text-muted small"><?= h(__('reg_pay_upload_desc')) ?></span>
                        </div>
                        <i class="ri-arrow-right-line fs-3 ms-auto text-secondary"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: UPLOAD RESIT -->
<div class="modal fade" id="modalUploadReceipt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="modal-title fw-bold fs-4"><?= h(__('reg_pay_upload_title')) ?></h5>
                <button type="button" class="btn-close bg-light rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formUploadReceipt">
                    <input type="hidden" name="action" value="register_portfolio_manual">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark"><?= h(__('reg_receipt_label')) ?></label>
                        <input type="file" class="form-control form-control-lg bg-light border-light-subtle" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    
                    <div class="alert alert-info border-0 bg-info-subtle d-flex align-items-start rounded-3 p-3 mb-4">
                        <i class="ri-information-fill text-info fs-4 me-2 mt-n1"></i>
                        <span class="small text-dark"><?= h(__('reg_receipt_alert')) ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="ri-send-plane-fill me-2"></i> <?= h(__('reg_btn_send_receipt')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-pay-fpx *, .btn-pay-manual * { pointer-events: none; }
    .hover-elevate:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.10)!important; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .btn-outline-primary:hover h5, .btn-outline-primary:hover span, .btn-outline-primary:hover .ri-bank-card-line, .btn-outline-primary:hover .ri-arrow-right-line { color: #fff !important; }
    .btn-outline-secondary:hover h5, .btn-outline-secondary:hover span, .btn-outline-secondary:hover .ri-file-upload-line, .btn-outline-secondary:hover .ri-arrow-right-line { color: #fff !important; }
    .btn-outline-primary:hover .bg-primary-subtle { background-color: rgba(255,255,255,0.2) !important; }
    .btn-outline-secondary:hover .bg-secondary-subtle { background-color: rgba(255,255,255,0.2) !important; }
</style>