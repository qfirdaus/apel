<!-- Modal Senarai Fail Lampiran -->
<div class="modal fade modal-add-accent" id="modalViewFiles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-5"><i class="ri-attachment-line me-1"></i> <?= h(__('work_files_title')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <div class="list-group list-group-flush" id="fileListContainer">
                    <!-- Senarai fail akan dijana di sini oleh JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>