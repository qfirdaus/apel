<div class="modal fade modal-add-accent" id="modalAddDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formAddDoc" enctype="multipart/form-data">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-upload-line me-1"></i> <span>Tambah Dokumen Tambahan</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="upload_additional_doc">
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Tajuk Dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="document_title" class="form-control" placeholder="Contoh: Sijil Kecekapan Tambahan 2023" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Catatan / Penjelasan Tambahan</label>
                            <textarea name="document_remarks" class="form-control" rows="3" placeholder="Sebab muat naik atau penerangan ringkas dokumen..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Fail Dokumen (PDF, JPG, PNG) <span class="text-danger">*</span></label>
                            <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('work_btn_close', 'Tutup')) ?></button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSaveDoc"><?= h(__('lang_btn_save', 'Muat Naik')) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>