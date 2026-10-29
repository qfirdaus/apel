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
</style>
<script>
function initApelUserList(){
  const csrf = <?= json_encode((string)($GLOBALS['csrf'] ?? ''), JSON_UNESCAPED_SLASHES) ?>;
  const endpoint = <?= json_encode(base_url('extensions/project/user-list/action.php'), JSON_UNESCAPED_SLASHES) ?>;
  const tabButton = document.getElementById('tab-apel-access-tab');
  const modalElement = document.getElementById('apelUserModal');
  if (!tabButton || !modalElement) return;
  const modal = window.bootstrap?.Modal ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
  let tableStarted = false;
  function startTable(){
    if (tableStarted || !window.jQuery || !jQuery.fn.DataTable) return;
    const table=jQuery('#userDTApel').DataTable({pageLength:10,lengthChange:true,lengthMenu:[10,25,50,100,200],ordering:true,order:[[1,'asc']],autoWidth:false,scrollX:false,retrieve:true,dom:'<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>t<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',language:{lengthMenu:<?= json_encode((string)__('userList_dt_length_menu')) ?>,search:'',info:<?= json_encode((string)__('userList_dt_info')) ?>,infoEmpty:<?= json_encode((string)__('userList_dt_info_empty')) ?>,emptyTable:<?= json_encode((string)__('userList_no_records')) ?>,paginate:{previous:<?= json_encode((string)__('userList_dt_paginate_prev')) ?>,next:<?= json_encode((string)__('userList_dt_paginate_next')) ?>},zeroRecords:<?= json_encode((string)__('userList_dt_zero_records')) ?>},columnDefs:[{targets:0,orderable:false,searchable:false,width:56},{targets:5,orderable:false,searchable:false,width:110}],rowCallback:function(row,data,displayIndex){const info=this.api().page.info();jQuery('td:eq(0)',row).text(info.start+displayIndex+1);},drawCallback:function(){document.querySelectorAll('#userDTApel [data-bs-toggle="tooltip"]').forEach(el=>bootstrap.Tooltip.getOrCreateInstance(el));}});
    if(window.DataTableStandard?.decorate)window.DataTableStandard.decorate('#userDTApel',{searchPlaceholder:<?= json_encode((string)__('userList_search_placeholder')) ?>});
    const topRight=jQuery('#userDTApel_wrapper .dt-top-right').addClass('align-items-center gap-2 flex-nowrap');
    if(!document.getElementById('apelAddUserBtn'))topRight.append(jQuery('<button type="button" class="btn btn-success" id="apelAddUserBtn"><i class="ri-user-add-line me-1"></i><?= h((string)__('apelUser_add_button')) ?></button>'));
    table.columns.adjust().draw(false);
    tableStarted = true;
  }
  tabButton.addEventListener('shown.bs.tab', function(){sessionStorage.setItem('userListActiveTab','#tab-apel-access');startTable();});
  if (sessionStorage.getItem('userListActiveTab') === '#tab-apel-access') setTimeout(function(){bootstrap.Tab.getOrCreateInstance(tabButton).show();startTable();},0);

  const byId = id => document.getElementById(id);
  const showError = message => { const el=byId('apelUserError'); el.textContent=message; el.classList.remove('d-none'); };
  const clearForm = () => { byId('apelUserError').classList.add('d-none'); byId('apelUserId').value='0'; byId('apelIdentifier').value=''; byId('apelIdentifier').readOnly=false; byId('apelName').value=''; byId('apelEmail').value=''; byId('apelUnit').value=''; byId('apelGroup').value=''; byId('apelAccess').value='1'; };
  document.addEventListener('click',function(event){if(event.target.closest('#apelAddUserBtn')){clearForm();modal?.show();}});
  document.addEventListener('click', async function(event){
    const viewAs=event.target.closest('.apel-view-as-user');
    if(viewAs){
      const reasonResult=window.Swal?await Swal.fire({icon:'warning',title:'Lihat sebagai pengguna APEL?',html:`<div class="text-start"><strong>${escapeText(viewAs.dataset.userName||viewAs.dataset.loginId||'Pengguna APEL')}</strong><p class="text-muted mb-0 mt-2">Nyatakan sebab sokongan atau semakan.</p></div>`,input:'textarea',inputPlaceholder:'Sebab diperlukan',inputAttributes:{maxlength:'500'},showCancelButton:true,confirmButtonText:'Mulakan View As',cancelButtonText:'Batal',preConfirm:value=>{const clean=String(value||'').trim();if(!clean){Swal.showValidationMessage('Sebab wajib diisi.');return false;}return clean;}}:{isConfirmed:false};
      if(!reasonResult.isConfirmed)return;
      try{
        const form=new FormData();form.set('csrf_token',csrf);form.set('target_login_id',viewAs.dataset.loginId||'');form.set('reason',String(reasonResult.value||'').trim());form.set('mode','view_only');
        const response=await fetch(<?= json_encode(base_url('ajax/impersonation-start.php'), JSON_UNESCAPED_SLASHES) ?>,{method:'POST',body:form,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const data=await response.json().catch(()=>({}));if(!response.ok||data.success!==true)throw new Error(data.message||'View As gagal dimulakan.');window.location.href=data.redirect||<?= json_encode(base_url('pages/dashboard.php'), JSON_UNESCAPED_SLASHES) ?>;
      }catch(error){if(window.Swal)await Swal.fire({icon:'error',title:'Tindakan gagal',text:error.message||'View As gagal dimulakan.'});}
      return;
    }
    const edit=event.target.closest('.apel-edit-user');
    if(edit){clearForm();const u=JSON.parse(edit.dataset.user||'{}');byId('apelUserId').value=u.f_userID||0;byId('apelIdentifier').value=u.f_loginID||u.f_stafID||'';byId('apelIdentifier').readOnly=true;byId('apelName').value=u.f_nama||'';byId('apelEmail').value=u.f_email||'';byId('apelUnit').value=u.f_namajabatan||'';byId('apelGroup').value=u.f_groupID||'';byId('apelAccess').value=String(u.f_flag??1);modal?.show();return;}
    const del=event.target.closest('.apel-delete-user');
    if(!del)return;
    const confirmResult=window.Swal?await Swal.fire({icon:'warning',title:'Padam pengguna APEL?',text:`${del.dataset.userName||'Rekod ini'} akan dipadam daripada senarai APEL.`,showCancelButton:true,confirmButtonText:'Padam',cancelButtonText:'Batal',confirmButtonColor:'#dc3545'}):{isConfirmed:confirm('Padam pengguna ini?')};
    if(!confirmResult.isConfirmed)return;
    await send({action:'delete',userID:Number(del.dataset.userId||0)});
  });
  byId('apelSaveUser')?.addEventListener('click', async function(){
    const payload={action:Number(byId('apelUserId').value)>0?'update':'create',userID:Number(byId('apelUserId').value||0),identifier:byId('apelIdentifier').value.trim(),name:byId('apelName').value.trim(),email:byId('apelEmail').value.trim(),unit:byId('apelUnit').value.trim(),groupID:Number(byId('apelGroup').value||0),flag:Number(byId('apelAccess').value)};
    if(!payload.identifier||!payload.name||!payload.groupID){showError('ID APEL, nama dan kumpulan wajib diisi.');return;}
    await send(payload);
  });
  async function send(payload){
    try{const response=await fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({...payload,csrf_token:csrf})});const data=await response.json().catch(()=>({}));if(!response.ok||data.error)throw new Error(data.message||'Tindakan gagal.');modal?.hide();if(window.Swal)await Swal.fire({icon:'success',title:'Berjaya',text:data.message||'Perubahan disimpan.',timer:1400,showConfirmButton:false});location.reload();}catch(error){if(modalElement.classList.contains('show'))showError(error.message||'Tindakan gagal.');else if(window.Swal)await Swal.fire({icon:'error',title:'Tindakan gagal',text:error.message||'Tindakan gagal.'});else alert(error.message||'Tindakan gagal.');}
  }
  function escapeText(value){const node=document.createElement('div');node.textContent=String(value||'');return node.innerHTML;}
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initApelUserList,{once:true});else initApelUserList();
</script>
