<?php
    $appId = (int)($activeApp['id'] ?? 0);
    $checklist = $appController->getPortfolioChecklist($appId);
    
    $studentName = $_SESSION['f_nama'] ?? $_SESSION['nama'] ; 
    $currentDate = date('d-M-Y');

    function getStatusIcon($isComplete) {
        if ($isComplete) {
            return '<span class="status-indicator complete shadow-sm"></span>';
        }
        return '<span class="status-indicator incomplete shadow-sm"></span>';
    }

    function getRowAction($isComplete, $url) {
        if (!$isComplete) {
            return ' onclick="window.location.href=\'' . $url . '\'" style="cursor: pointer;" title="Klik untuk melengkapkan bahagian ini"';
        }
        return ''; 
    }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="text-dark fw-bold mb-1">Submit Portfolio</h4>
    </div>
</div>

<!-- Portfolio Checklist Card -->
<div class="card border border-dark-subtle rounded-3 mb-4 shadow-none">
    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title text-dark fw-bold mb-0">Portfolio Checklist</h5>
        
        <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-2 text-secondary fw-medium" style="font-size: 0.85rem;">
                <span class="status-indicator complete shadow-none" style="width:14px;height:14px;"></span> Complete
            </span>
            <span class="d-flex align-items-center gap-2 text-secondary fw-medium" style="font-size: 0.85rem;">
                <span class="status-indicator incomplete shadow-none" style="width:14px;height:14px;"></span> Not Complete
            </span>
        </div>
    </div>
    
    <div class="card-body p-4 bg-light bg-opacity-50">
        <div class="table-wrapper shadow-sm">
            <table class="table table-premium text-center w-100">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th width="70%" class="text-start">Section</th>
                        <th width="20%">Complete / Not Complete</th>
                    </tr>
                </thead>
                <tbody>
                    <tr<?= getRowAction($checklist['work'], '../informal/index.php') ?>>
                        <td class="fw-medium">1</td>
                        <td class="text-start fw-medium">Work Experience</td>
                        <td><?= getStatusIcon($checklist['work']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['other'], '../informal/index.php') ?>>
                        <td class="fw-medium">2</td>
                        <td class="text-start fw-medium">Other Learning Activities</td>
                        <td><?= getStatusIcon($checklist['other']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['training'], '../non-formal/index.php') ?>>
                        <td class="fw-medium">3</td>
                        <td class="text-start fw-medium">Training/Course</td>
                        <td><?= getStatusIcon($checklist['training']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['language'], '../language/index.php') ?>>
                        <td class="fw-medium">4</td>
                        <td class="text-start fw-medium">Language Competency</td>
                        <td><?= getStatusIcon($checklist['language']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['reflection'], '../reflection/index.php') ?>>
                        <td class="fw-medium">5</td>
                        <td class="text-start fw-medium">Self-Assessment/Reflection</td>
                        <td><?= getStatusIcon($checklist['reflection']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['referees'], '../referees/index.php') ?>>
                        <td class="fw-medium">6</td>
                        <td class="text-start fw-medium">Referees</td>
                        <td><?= getStatusIcon($checklist['referees']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['documents'], '../upload-documents/index.php') ?>>
                        <td class="fw-medium">7</td>
                        <td class="text-start fw-medium">Upload Other Documents</td>
                        <td><?= getStatusIcon($checklist['documents']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Self-Declaration Card -->
<div class="card border border-dark-subtle rounded-3 mb-4 shadow-none">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="card-title text-dark fw-bold mb-0">Self-Declaration</h5>
    </div>
    <div class="card-body p-4">
        <div class="declaration-box p-4 rounded-3 border border-light">
            <p class="text-dark mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                I hereby declare that all the information/documents provided to support this application are authentic, true and accurate. I fully understand the terms and conditions of the application and agree that my application will be rejected if I have falsified any information in any way.
            </p>
            
            <div class="d-flex flex-column flex-md-row gap-5 mt-4 pt-3 border-top border-light-subtle">
                <div>
                    <span class="text-muted d-block small text-uppercase fw-bold mb-1 tracking-wider">Name</span>
                    <strong class="text-dark text-uppercase fs-6"><?= htmlspecialchars($studentName) ?></strong>
                </div>
                <div>
                    <span class="text-muted d-block small text-uppercase fw-bold mb-1 tracking-wider">Date</span>
                    <strong class="text-dark fs-6"><?= $currentDate ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Form -->
<form id="formSubmitPortfolio">
    <input type="hidden" name="action" value="submit_portfolio">
    <div class="d-flex justify-content-end pt-3 mt-2">
        <button type="submit" id="btnSubmitPortfolio" class="btn btn-primary btn-lg px-5 shadow fw-bold rounded-pill" <?= $checklist['all_complete'] ? '' : 'disabled' ?>>
            <i class="ri-send-plane-fill me-2"></i> Submit Portfolio
        </button>
    </div>
</form>