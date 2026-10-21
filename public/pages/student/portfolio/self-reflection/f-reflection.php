<?php
    $controller = new SelfReflectionController();
    $studentId = (string)($_SESSION['f_stafID'] ?? '');
    
    $record = $controller->getReflectionRecord($studentId, $applicationId);
?>
<div class="alert alert-secondary mb-4 text-dark">
    <strong><?= h(__('reflection_intro')) ?></strong>
</div>

<form id="formSelfReflection" method="post">
    <input type="hidden" name="action" value="save_reflection">
    <input type="hidden" name="application_id" value="<?= $applicationId ?>">
    
    <!-- Soalan 1 -->
    <div class="mb-4">
        <div class="question-title">1. <?= h(__('reflection_q1')) ?></div>
        <textarea id="editor-q1" name="q1_answer" class="tinymce-editor form-control"><?= $record['q1_answer'] ?? '' ?></textarea>
    </div>

    <!-- Soalan 2 -->
    <div class="mb-4">
        <div class="question-title">2. <?= h(__('reflection_q2')) ?><br>
        <span class="text-muted fw-normal" style="font-size: 0.9em;"><?= h(__('reflection_q2_hint')) ?></span>
        </div>
        <textarea id="editor-q2" name="q2_answer" class="tinymce-editor form-control"><?= $record['q2_answer'] ?? '' ?></textarea>
    </div>

    <!-- Soalan 3 -->
    <div class="mb-4">
        <div class="question-title">3. <?= h(__('reflection_q3')) ?></div>
        <textarea id="editor-q3" name="q3_answer" class="tinymce-editor form-control"><?= $record['q3_answer'] ?? '' ?></textarea>
    </div>

    <!-- Soalan 4 -->
    <div class="mb-4">
        <div class="question-title">4. <?= h(__('reflection_q4')) ?><br>
        <span class="text-muted fw-normal" style="font-size: 0.9em;"><?= h(__('reflection_q4_hint')) ?></span>
        </div>
        <textarea id="editor-q4" name="q4_answer" class="tinymce-editor form-control"><?= $record['q4_answer'] ?? '' ?></textarea>
    </div>

    <div class="d-flex justify-content-end mt-4 border-top pt-3">
        <button type="submit" class="btn btn-primary px-5 py-2">
            <i class="ri-save-3-line me-1"></i> <?= h(__('reflection_btn_save')) ?>
        </button>
    </div>
</form>