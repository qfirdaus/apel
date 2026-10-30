<?php declare(strict_types=1); ?>
<style>
#tab-apel-access .apel-user-card{margin-bottom:0}
#userDTApel{width:100%}
#userDTApel th,#userDTApel td{vertical-align:middle;white-space:nowrap}
#userDTApel th.col-bil,#userDTApel td.col-bil{width:5%;text-align:center}
#userDTApel th.col-nama,#userDTApel td.col-nama{width:30%;text-align:left}
#userDTApel th.col-jabatan,#userDTApel td.col-jabatan{width:30%;text-align:left}
#userDTApel th.col-group,#userDTApel td.col-group{width:15%;text-align:left}
#userDTApel th.col-akses,#userDTApel td.col-akses{width:10%;text-align:left}
#userDTApel th.col-actions,#userDTApel td.col-actions{width:10%;text-align:center}
#userDTApel .group-chip{
  background:rgba(71,85,105,.12);
  border-color:rgba(71,85,105,.2);
  color:#334155;
}
.apel-user-tooltip{--bs-tooltip-max-width:none!important;max-width:none!important}
.apel-user-tooltip .tooltip-inner{display:block;width:max-content;max-width:none!important;white-space:nowrap!important;text-align:left}
[data-bs-theme="dark"] #userDTApel .group-chip{
  background:rgba(148,163,184,.18);
  border-color:rgba(148,163,184,.28);
  color:#e2e8f0;
}
#apelUserModal{z-index:11020!important}
#apelSyncLogModal{z-index:11020!important}
#apelSyncLogModal .modal-content{border:0;border-radius:10px;overflow:hidden}
#apelSyncLogModal .apel-sync-log-dialog{margin-top:1rem;margin-bottom:1rem;align-items:flex-start}
#apelSyncLogModal .modal-header{background:linear-gradient(135deg,#2563eb 0%,#0891b2 100%);color:#fff;border-bottom:0}
#apelSyncLogModal .modal-title{color:#fff;font-weight:700}
#apelSyncLogModal .modal-header .btn-close{filter:invert(1);opacity:.9}
#apelSyncLogTable th,#apelSyncLogTable td{text-align:center;vertical-align:middle;white-space:nowrap}
#apelSyncLogTable th:first-child,#apelSyncLogTable td:first-child{text-align:left}
#apelUserModal .modal-dialog{max-width:780px;position:relative;z-index:1}
#apelUserModal .modal-content{border:0;border-radius:8px;box-shadow:none!important;outline:0!important;overflow:hidden}
#apelUserModal .modal-header{background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:#fff;border-bottom:0;padding:1rem 1.35rem}
#apelUserModal.is-edit-mode .modal-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
#apelUserModal .modal-title{color:#fff;font-weight:600;font-size:1.05rem;letter-spacing:.2px}
#apelUserModal .modal-header .btn-close{filter:invert(1);opacity:.9}
#apelUserModal .modal-body{padding:1rem 1.35rem}
#apelUserModal .modal-footer{padding:.95rem 1.35rem;background:#f8f9fa;border-top:1px solid rgba(0,0,0,.08)}
#apelUserModal .modal-footer .btn{padding:.5rem 1.05rem;font-weight:600;border-radius:8px}
#apelUserModal .au-modal-tabs{display:flex;gap:.5rem;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid #e9ecef}
#apelUserModal .au-modal-tabs .nav-link{border:1px solid #dbe4f0;background:#f8fafc;color:#475569;border-radius:8px;padding:.55rem .95rem;font-size:.875rem;font-weight:600}
#apelUserModal .au-modal-tabs .nav-link.active{color:#fff;border-color:#28a745;background:linear-gradient(135deg,#28a745 0%,#20c997 100%);box-shadow:0 8px 20px rgba(32,201,151,.18)}
#apelUserModal.is-edit-mode .au-modal-tabs .nav-link.active{border-color:#667eea;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);box-shadow:0 8px 20px rgba(102,126,234,.18)}
#apelUserModal .au-tab-pane{padding-top:.15rem}
#apelUserModal .form-section{margin-bottom:1rem;padding-bottom:.9rem;border-bottom:2px solid #e9ecef}
#apelUserModal .form-section:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0}
#apelUserModal .form-section-title{font-size:.8rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.9px;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #28a745;display:flex;align-items:center}
#apelUserModal.is-edit-mode .form-section-title{border-bottom-color:#667eea}
#apelUserModal .form-section-title i{color:#28a745}
#apelUserModal.is-edit-mode .form-section-title i{color:#667eea}
#apelUserModal .form-label{font-weight:600;color:#495057;margin-bottom:.55rem;font-size:.9rem;display:flex;align-items:center}
#apelUserModal .form-label i{margin-right:.4rem;font-size:1rem}
#apelUserModal .form-control,#apelUserModal .form-select{min-height:38px;height:38px;padding:.375rem .75rem;font-size:.875rem;line-height:1.5;border-width:1px;border-radius:8px}
#apelUserModal #apelUserError{margin-top:0;border-radius:8px;border-left:4px solid #dc3545}
[data-bs-theme="dark"] #apelUserModal .modal-footer{background:#111827}
[data-bs-theme="dark"] #apelUserModal .form-label,[data-bs-theme="dark"] #apelUserModal .form-section-title{color:#e5e7eb}
[data-bs-theme="dark"] #apelUserModal .au-modal-tabs{border-bottom-color:#334155}
[data-bs-theme="dark"] #apelUserModal .au-modal-tabs .nav-link{background:#1e293b;border-color:#334155;color:#cbd5e1}
</style>
<script>
function initApelUserList(){
  const csrf = <?= json_encode((string)($GLOBALS['csrf'] ?? ''), JSON_UNESCAPED_SLASHES) ?>;
  const endpoint = <?= json_encode(base_url('extensions/project/user-list/action.php'), JSON_UNESCAPED_SLASHES) ?>;
  const labels = <?= json_encode([
    'addTitle' => (string)__('apelUser_modal_add_title'),
    'editTitle' => (string)__('apelUser_modal_edit_title'),
    'required' => (string)__('apelUser_required_error'),
    'deleteTitle' => (string)__('apelUser_delete_title'),
    'deleteMessage' => (string)__('apelUser_delete_message'),
    'deleteWarning' => (string)__('apelUser_delete_warning'),
    'deleteYes' => (string)__('userList_delete_confirm_yes'),
    'cancel' => (string)__('userList_modal_btn_cancel'),
    'viewTitle' => (string)__('impersonation_start_title'),
    'viewText' => (string)__('impersonation_start_text'),
    'modeLabel' => (string)__('impersonation_mode_label'),
    'viewOnly' => (string)__('impersonation_mode_view_only'),
    'supportAction' => (string)__('impersonation_mode_support_action'),
    'modeHelp' => (string)__('impersonation_mode_help'),
    'reasonLabel' => (string)__('impersonation_reason_label'),
    'reasonPlaceholder' => (string)__('impersonation_reason_placeholder'),
    'reasonRequired' => (string)__('impersonation_reason_required'),
    'viewStart' => (string)__('impersonation_start_button'),
    'syncConfirmTitle' => (string)__('apelUser_sync_confirm_title'),
    'syncConfirmText' => (string)__('apelUser_sync_confirm_text'),
    'syncConfirmButton' => (string)__('apelUser_sync_confirm_button'),
    'syncRunning' => (string)__('apelUser_sync_running'),
    'syncSuccess' => (string)__('apelUser_sync_success'),
    'cronLogEmpty' => (string)__('apelUser_cron_log_empty'),
    'triggerCron' => (string)__('apelUser_trigger_cron'),
    'triggerManual' => (string)__('apelUser_trigger_manual'),
    'close' => (string)__('userList_modal_btn_close'),
    'syncStatusSuccess' => (string)__('apelUser_sync_status_success'),
    'syncStatusFailed' => (string)__('apelUser_sync_status_failed'),
    'syncSummary' => (string)__('apelUser_sync_summary'),
    'syncErrorTitle' => (string)__('apelUser_sync_error_title'),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const tabButton = document.getElementById('tab-apel-access-tab');
  const modalElement = document.getElementById('apelUserModal');
  if (!tabButton || !modalElement) return;
  const modal = window.bootstrap?.Modal ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
  let tableStarted = false;
  function startTable(){
    if (tableStarted || !window.jQuery || !jQuery.fn.DataTable) return;
    const table=jQuery('#userDTApel').DataTable({pageLength:10,lengthChange:true,lengthMenu:[10,25,50,100,200],ordering:true,order:[[1,'asc']],autoWidth:false,scrollX:false,retrieve:true,dom:'<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>t<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',language:{lengthMenu:<?= json_encode((string)__('userList_dt_length_menu')) ?>,search:'',info:<?= json_encode((string)__('userList_dt_info')) ?>,infoEmpty:<?= json_encode((string)__('userList_dt_info_empty')) ?>,emptyTable:<?= json_encode((string)__('userList_no_records')) ?>,paginate:{previous:<?= json_encode((string)__('userList_dt_paginate_prev')) ?>,next:<?= json_encode((string)__('userList_dt_paginate_next')) ?>},zeroRecords:<?= json_encode((string)__('userList_dt_zero_records')) ?>},columnDefs:[{targets:0,orderable:false,searchable:false,width:56},{targets:5,orderable:false,searchable:false,width:110}],rowCallback:function(row,data,displayIndex){const info=this.api().page.info();jQuery('td:eq(0)',row).text(info.start+displayIndex+1);},drawCallback:function(){document.querySelectorAll('#userDTApel [data-bs-toggle="tooltip"]').forEach(el=>{bootstrap.Tooltip.getInstance(el)?.dispose();new bootstrap.Tooltip(el,{customClass:'apel-user-tooltip',container:'body',boundary:'viewport'});});}});
    if(window.DataTableStandard?.decorate)window.DataTableStandard.decorate('#userDTApel',{searchPlaceholder:<?= json_encode((string)__('userList_search_placeholder')) ?>});
    const topRight=jQuery('#userDTApel_wrapper .dt-top-right').addClass('align-items-center gap-2 flex-nowrap');
    if(!document.getElementById('apelManualSyncBtn'))topRight.append(jQuery('<button type="button" class="btn btn-primary" id="apelManualSyncBtn"><i class="ri-refresh-line me-1"></i><?= h((string)__('apelUser_manual_sync_button')) ?></button>'));
    if(!document.getElementById('apelSyncLogBtn'))topRight.append(jQuery('<button type="button" class="btn btn-outline-primary" id="apelSyncLogBtn"><i class="ri-history-line me-1"></i><?= h((string)__('apelUser_cron_log_button')) ?></button>'));
    if(!document.getElementById('apelAddUserBtn'))topRight.append(jQuery('<button type="button" class="btn btn-success" id="apelAddUserBtn"><i class="ri-user-add-line me-1"></i><?= h((string)__('apelUser_add_button')) ?></button>'));
    table.columns.adjust().draw(false);
    tableStarted = true;
  }
  tabButton.addEventListener('shown.bs.tab', function(){sessionStorage.setItem('userListActiveTab','#tab-apel-access');startTable();});
  if (sessionStorage.getItem('userListActiveTab') === '#tab-apel-access') setTimeout(function(){bootstrap.Tab.getOrCreateInstance(tabButton).show();startTable();},0);

  const byId = id => document.getElementById(id);
  const showError = message => { const el=byId('apelUserError'); el.textContent=message; el.classList.remove('d-none'); };
  const selectInfoTab = () => bootstrap.Tab.getOrCreateInstance(byId('apel-info-tab')).show();
  const setModalMode = editing => {modalElement.classList.toggle('is-edit-mode',editing);byId('apelUserModalTitleText').textContent=editing?labels.editTitle:labels.addTitle;byId('apelUserModalIcon').className=(editing?'ri-user-settings-line':'ri-user-add-line')+' me-2';selectInfoTab();};
  const clearForm = () => { byId('apelUserError').classList.add('d-none'); byId('apelUserId').value='0'; byId('apelIdentifier').value=''; byId('apelIdentifier').readOnly=false; byId('apelName').value=''; byId('apelEmail').value=''; byId('apelUnit').value=''; byId('apelGroup').value=''; byId('apelAccess').value='1'; };
  document.addEventListener('click',function(event){if(event.target.closest('#apelAddUserBtn')){clearForm();setModalMode(false);modal?.show();}});
  document.addEventListener('click',async function(event){
    if(event.target.closest('#apelManualSyncBtn')){
      const result=window.Swal?await Swal.fire({icon:'question',title:labels.syncConfirmTitle,text:labels.syncConfirmText,showCancelButton:true,confirmButtonText:labels.syncConfirmButton,cancelButtonText:labels.cancel,confirmButtonColor:'#2563eb'}):{isConfirmed:confirm(labels.syncConfirmTitle)};
      if(!result.isConfirmed)return;
      if(window.Swal)Swal.fire({title:labels.syncRunning,allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
      try{
        const data=await postAction({action:'manual_sync'});
        const run=data.run||{};
        const summary=labels.syncSummary.replace('{created}',run.created||0).replace('{updated}',run.updated||0).replace('{deactivated}',run.deactivated||0);
        if(window.Swal)await Swal.fire({icon:'success',title:labels.syncSuccess,text:summary});
        location.reload();
      }catch(error){if(window.Swal)await Swal.fire({icon:'error',title:labels.syncErrorTitle,text:error.message||labels.syncErrorTitle});else alert(error.message||labels.syncErrorTitle);}
      return;
    }
    if(event.target.closest('#apelSyncLogBtn')){
      await openSyncLog();
    }
  });
  document.addEventListener('click', async function(event){
    const viewAs=event.target.closest('.apel-view-as-user');
    if(viewAs){
      const reasonResult=window.Swal?await Swal.fire({icon:'warning',title:labels.viewTitle,width:680,html:`<div class="text-start small"><div class="mb-2">${escapeText(labels.viewText)}</div><div><strong>${escapeText(viewAs.dataset.userName||viewAs.dataset.loginId||'APEL')}</strong> <span class="text-muted">(${escapeText(viewAs.dataset.loginId||'')})</span></div><div class="mt-3"><label class="form-label fw-semibold mb-1">${escapeText(labels.modeLabel)}</label><select class="form-select" id="apelImpersonationMode"><option value="view_only" selected>${escapeText(labels.viewOnly)}</option><option value="support_action">${escapeText(labels.supportAction)}</option></select><div class="form-text">${escapeText(labels.modeHelp)}</div></div></div>`,input:'textarea',inputLabel:labels.reasonLabel,inputPlaceholder:labels.reasonPlaceholder,inputAttributes:{maxlength:'500'},showCancelButton:true,confirmButtonText:labels.viewStart,cancelButtonText:labels.cancel,confirmButtonColor:'#f59e0b',preConfirm:value=>{const clean=String(value||'').trim();if(!clean){Swal.showValidationMessage(labels.reasonRequired);return false;}return clean;}}):{isConfirmed:false};
      if(!reasonResult.isConfirmed)return;
      try{
        const form=new FormData();form.set('csrf_token',csrf);form.set('target_login_id',viewAs.dataset.loginId||'');form.set('reason',String(reasonResult.value||'').trim());form.set('mode',String(document.getElementById('apelImpersonationMode')?.value||'view_only'));
        const response=await fetch(<?= json_encode(base_url('ajax/impersonation-start.php'), JSON_UNESCAPED_SLASHES) ?>,{method:'POST',body:form,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const data=await response.json().catch(()=>({}));if(!response.ok||data.success!==true)throw new Error(data.message||'View As gagal dimulakan.');window.location.href=data.redirect||<?= json_encode(base_url('pages/dashboard.php'), JSON_UNESCAPED_SLASHES) ?>;
      }catch(error){if(window.Swal)await Swal.fire({icon:'error',title:'Tindakan gagal',text:error.message||'View As gagal dimulakan.'});}
      return;
    }
    const edit=event.target.closest('.apel-edit-user');
    if(edit){clearForm();setModalMode(true);const u=JSON.parse(edit.dataset.user||'{}');byId('apelUserId').value=u.f_userID||0;byId('apelIdentifier').value=u.f_loginID||u.f_stafID||'';byId('apelIdentifier').readOnly=true;byId('apelName').value=u.f_nama||'';byId('apelEmail').value=u.f_email||'';byId('apelUnit').value=u.f_namajabatan||'';byId('apelGroup').value=u.f_groupID||'';byId('apelAccess').value=String(u.f_flag??1);modal?.show();return;}
    const del=event.target.closest('.apel-delete-user');
    if(!del)return;
    const confirmResult=window.Swal?await Swal.fire({icon:'warning',title:labels.deleteTitle,html:`<p>${escapeText(labels.deleteMessage)}</p><p><strong>${escapeText(del.dataset.userName||'APEL')}</strong></p><p class="text-danger"><small>${escapeText(labels.deleteWarning)}</small></p>`,showCancelButton:true,confirmButtonText:labels.deleteYes,cancelButtonText:labels.cancel,confirmButtonColor:'#dc3545',cancelButtonColor:'#6c757d',reverseButtons:true}):{isConfirmed:confirm(labels.deleteTitle)};
    if(!confirmResult.isConfirmed)return;
    await send({action:'delete',userID:Number(del.dataset.userId||0)});
  });
  byId('apelSaveUser')?.addEventListener('click', async function(){
    const payload={action:Number(byId('apelUserId').value)>0?'update':'create',userID:Number(byId('apelUserId').value||0),identifier:byId('apelIdentifier').value.trim(),name:byId('apelName').value.trim(),email:byId('apelEmail').value.trim(),unit:byId('apelUnit').value.trim(),groupID:Number(byId('apelGroup').value||0),flag:Number(byId('apelAccess').value)};
    if(!payload.identifier||!payload.name||!payload.groupID){showError(labels.required);return;}
    await send(payload);
  });
  async function send(payload){
    try{const response=await fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({...payload,csrf_token:csrf})});const data=await response.json().catch(()=>({}));if(!response.ok||data.error)throw new Error(data.message||'Tindakan gagal.');modal?.hide();if(window.Swal)await Swal.fire({icon:'success',title:'Berjaya',text:data.message||'Perubahan disimpan.',timer:1400,showConfirmButton:false});location.reload();}catch(error){if(modalElement.classList.contains('show'))showError(error.message||'Tindakan gagal.');else if(window.Swal)await Swal.fire({icon:'error',title:'Tindakan gagal',text:error.message||'Tindakan gagal.'});else alert(error.message||'Tindakan gagal.');}
  }
  async function postAction(payload){
    const response=await fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({...payload,csrf_token:csrf})});
    const data=await response.json().catch(()=>({}));
    if(!response.ok||data.error)throw new Error(data.message||'Request failed.');
    return data;
  }
  async function openSyncLog(){
    const logModalElement=byId('apelSyncLogModal');
    const errorElement=byId('apelSyncLogError');
    errorElement.classList.add('d-none');
    try{
      const data=await postAction({action:'sync_logs'});
      const showValue=value=>Number(value)>0?value:'';
      const rows=(data.runs||[]).map(run=>{
        const errors=Number(run.errors||0);
        return [run.run_at||'-',run.trigger==='manual'?labels.triggerManual:labels.triggerCron,errors===0?labels.syncStatusSuccess:labels.syncStatusFailed,showValue(run.source),showValue(run.created),showValue(run.updated),showValue(run.deactivated),Number(run.duration_ms)>0?`${run.duration_ms} ms`:'' ];
      });
      if(window.jQuery&&jQuery.fn.DataTable){
        if(jQuery.fn.DataTable.isDataTable('#apelSyncLogTable'))jQuery('#apelSyncLogTable').DataTable().destroy();
        jQuery('#apelSyncLogTable tbody').empty();
        const options={data:rows,pageLength:10,lengthMenu:[10,25,50,100],ordering:false,autoWidth:false,scrollX:true,language:{emptyTable:labels.cronLogEmpty,search:'',lengthMenu:<?= json_encode((string)__('userList_dt_length_menu')) ?>,info:<?= json_encode((string)__('userList_dt_info')) ?>,infoEmpty:<?= json_encode((string)__('userList_dt_info_empty')) ?>,paginate:{previous:<?= json_encode((string)__('userList_dt_paginate_prev')) ?>,next:<?= json_encode((string)__('userList_dt_paginate_next')) ?>}},columnDefs:[{targets:0,render:function(value,type){if(type!=='display')return value;const parsed=new Date(value);return Number.isNaN(parsed.getTime())?escapeText(value):escapeText(parsed.toLocaleString());}},{targets:2,render:function(value,type){if(type!=='display')return value;const ok=value===labels.syncStatusSuccess;return `<span class="badge ${ok?'bg-success':'bg-danger'}">${escapeText(value)}</span>`;}}]};
        const table=jQuery('#apelSyncLogTable').DataTable(window.DataTableStandard?.options?window.DataTableStandard.options(options):options);
        if(window.DataTableStandard?.decorate)window.DataTableStandard.decorate('#apelSyncLogTable',{searchPlaceholder:<?= json_encode((string)__('userList_search_placeholder')) ?>});
        bootstrap.Modal.getOrCreateInstance(logModalElement).show();
        logModalElement.addEventListener('shown.bs.modal',()=>table.columns.adjust(),{once:true});
      }
    }catch(error){errorElement.textContent=error.message||'Unable to load sync log.';errorElement.classList.remove('d-none');bootstrap.Modal.getOrCreateInstance(logModalElement).show();}
  }
  function escapeText(value){const node=document.createElement('div');node.textContent=String(value||'');return node.innerHTML;}
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initApelUserList,{once:true});else initApelUserList();
</script>
