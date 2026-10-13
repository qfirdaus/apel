<div class="modal fade" id="modalOther" tabindex="-1" aria-hidden="true" aria-labelledby="modalOtherTitle">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formOther" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOtherTitle">Add Record (Other Learning Activities)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" value="<?= h($_SESSION['student_id'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="otherActivityName" class="form-label">Other activities</label>
                        <input type="text" name="activity_name" id="otherActivityName" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="otherStartDate" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="otherStartDate" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="otherEndDate" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="otherEndDate" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="otherOrganizer" class="form-label">Organizer</label>
                        <input type="text" name="organizer" id="otherOrganizer" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="skillLevel" class="form-label">Skill Level</label>
                        <select name="skill_level" id="skillLevel" class="form-select">
                            <option value="" disabled selected>- PLEASE CHOOSE -</option>
                            <option value="Asas">Asas (Basic)</option>
                            <option value="Pertengahan">Pertengahan (Intermediate)</option>
                            <option value="Mahir">Mahir (Advanced)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="otherWhatLearned" class="form-label">What I Have Learned/Acquired</label>
                        <textarea name="what_learned" id="otherWhatLearned" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="otherEvidence" class="form-label fw-bold">Evidence Of Learning</label>
                        <p class="text-danger small mb-1">** Only PDF, JPEG and JPG files are allowed. You can select multiple files.</p>
                        <input type="file" name="evidence[]" id="otherEvidence" class="form-control" accept=".pdf,.jpeg,.jpg" multiple>
                        <ul class="list-group list-group-flush small mt-2" id="otherEvidenceList" aria-live="polite"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
