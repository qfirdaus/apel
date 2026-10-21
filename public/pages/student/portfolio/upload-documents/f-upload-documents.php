<?php
    $controller = new UploadDocumentController();
    $studentId = (string)($_SESSION['f_stafID'] ?? '');
    $documentsList = $controller->getDocumentsList($studentId, $applicationId);
?>
<div class="row g-4">
    <?php if (empty($documentsList)): ?>
        <div class="col-12">
            <div class="alert alert-info border-0">
                <i class="ri-information-line me-2"></i> <?= h(__('upload_alert_info')) ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($documentsList as $doc): ?>
            <?php
                $isUploaded = !empty($doc['file_path']);
                $statusColor = $isUploaded ? 'success' : 'warning';
                $statusText = $isUploaded ? 'Uploaded' : 'Pending Upload';
                $icon = $isUploaded ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
            ?>
            
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border border-light-subtle shadow-sm rounded-3 hover-elevate">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title text-dark fw-bold mb-0 lh-base"><?= h($doc['document_name']) ?></h6>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> rounded-pill px-2 py-1">
                                <i class="<?= $icon ?> me-1"></i> <?= $statusText ?>
                            </span>
                        </div>

                        <?php if ($isUploaded): ?>
                            <div class="p-2 bg-light rounded mb-3 d-flex align-items-center" title="<?= h($doc['file_name']) ?>">
                                <i class="ri-file-pdf-line text-danger fs-4 me-2"></i>
                                <div class="text-truncate small fw-medium text-secondary">
                                    <?= h($doc['file_name']) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted mb-3 flex-grow-1">
                                <?= h(__('upload_required_docs_info')) ?> <?= strtolower(h($doc['document_name'])) ?>.
                            </p>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-auto pt-2 border-top border-light">
                            <?php if ($isUploaded): ?>
                                
                                <a href="<?= base_url('pages/student/portfolio/upload-documents/' . $doc['file_path']) ?>" target="_blank" class="btn btn-soft-info btn-sm flex-fill fw-medium">
                                    <i class="ri-search-eye-line me-1"></i> <?= __('upload_doc_btn_view') ?>
                                </a>
                                
                                <button type="button" class="btn btn-primary btn-sm flex-fill fw-medium btn-upload-trigger" data-doctype="<?= $doc['doc_type_id'] ?>" data-docname="<?= h($doc['document_name']) ?>">
                                    <i class="ri-refresh-line me-1"></i> <?= __('upload_doc_btn_replace') ?>
                                </button>
                            <?php else: ?>
                                
                                <button type="button" class="btn btn-primary btn-sm w-100 fw-medium btn-upload-trigger" data-doctype="<?= $doc['doc_type_id'] ?>" data-docname="<?= h($doc['document_name']) ?>">
                                    <i class="ri-upload-cloud-2-line me-1"></i> <?= __('upload_doc_btn_upload') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Hidden Form - AJAX File Upload -->
<form id="formUploadDocument" style="display:none;" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload_document">
    <input type="hidden" name="application_id" value="<?= $applicationId ?>">
    <input type="hidden" name="document_type_id" id="hidden_doc_type_id">
    <input type="hidden" name="document_name" id="hidden_doc_name">
    
    <input type="file" name="document_file" id="hidden_file_input" accept=".pdf,.jpg,.jpeg,.png">
</form>

<style>

</style>