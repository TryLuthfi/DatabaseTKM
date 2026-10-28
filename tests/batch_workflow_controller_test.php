<?php
/** Exercise the actual controller boundary/gates without a DB connection or session cookie. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
require APPPATH . 'libraries/Batch_workflow_policy.php';
class CI_Controller { public $session, $input, $MBatch_edit_approval, $MBatch_Approval_MyRep; }
function redirect($path) { $GLOBALS['redirected'] = $path; }
function show_error($message, $status) { throw new RuntimeException($message, $status); }
require APPPATH . 'controllers/Batch_Approval_MyRep.php';
class WorkflowSession {
    public $data = ['id_user'=>10,'nama_level'=>'Super Admin','batch_edit_csrf'=>'test-session-csrf'];
    public $flash=[];
    function userdata($key) { return $this->data[$key] ?? null; }
    function set_flashdata($key, $value) { $this->flash[$key]=$value; }
}
class WorkflowInput {
    public $verb='POST', $data=[];
    function method() { return $this->verb; }
    function post($key) { return $this->data[$key] ?? null; }
}
class WorkflowServiceSpy {
    public $calls=[];
    function correctWorkflow(...$args) { $this->calls[]=$args; return ['ok'=>true,'message'=>'saved']; }
}
class WorkflowDocumentsStub {
    public $pre=true,$post=true,$finance=true,$astri=true,$rejected=0;
    function areDonationRequiredDocumentsFinanceApproved($id,$group) { return $this->finance; }
    function areDonationRequiredDocumentsApproved($id,$group) { return $group==='PRE_ZEYN'?$this->pre:$this->post; }
    function areAllDonationDocumentsAstriApproved($id) { return $this->astri; }
    function getDonationDocumentSummary($id) { return ['PRE_ZEYN'=>['astri_rejected'=>$this->rejected],'POST_ZEYN'=>['astri_rejected'=>0]]; }
}
$c=(new ReflectionClass('Batch_Approval_MyRep'))->newInstanceWithoutConstructor();
$c->session=new WorkflowSession();$c->input=new WorkflowInput();$c->MBatch_edit_approval=new WorkflowServiceSpy();$c->MBatch_Approval_MyRep=new WorkflowDocumentsStub();
$checks=0;
function verifyWorkflow($value,$label) { global $checks;if(!$value){throw new RuntimeException('FAIL '.$label);} $checks++;echo 'PASS '.$label.PHP_EOL; }
function callPrivate($method,...$args) { global $c;$m=new ReflectionMethod($c,$method);$m->setAccessible(true);return $m->invoke($c,...$args); }
function boundaryStatus() { global $c;try{$c->correctWorkflow();return 0;}catch(RuntimeException $e){return $e->getCode();} }
$valid=['cluster_id'=>'15112','id_batch_approval'=>'13239','target_stage'=>'DRAFT','saku_action'=>'KEEP','correction_reason'=>'Correct imported stage','correction_evidence'=>'','baseline_hash'=>'baseline','batch_edit_csrf'=>'test-session-csrf'];
$c->input->data=$valid;
unset($c->session->data['id_user']);$c->correctWorkflow();verifyWorkflow($GLOBALS['redirected']==='Auth' && !$c->MBatch_edit_approval->calls,'anonymous redirected before service');
$c->session->data['id_user']=10;
foreach(['Admin','Finance HO','SITAC HO',''] as $role){$c->session->data['nama_level']=$role;verifyWorkflow(boundaryStatus()===403,'role denied: '.$role);}
$c->session->data['nama_level']='Super Admin';$c->input->verb='GET';verifyWorkflow(boundaryStatus()===405,'GET rejected');$c->input->verb='POST';
$c->input->data['batch_edit_csrf']='other-session';verifyWorkflow(boundaryStatus()===403,'cross-session CSRF rejected');
$c->input->data=$valid;$c->input->data['target_stage']=['RELEASED'];verifyWorkflow(boundaryStatus()===400,'array target rejected before service');
$c->input->data=$valid;$c->input->data['cluster_id']='15112junk';verifyWorkflow(boundaryStatus()===400,'malformed cluster ID rejected');
$c->input->data=$valid;$c->correctWorkflow();verifyWorkflow(count($c->MBatch_edit_approval->calls)===1 && $GLOBALS['redirected']==='Batch_Approval_MyRep/detail/15112','valid authorized POST reaches service then detail');
$call=$c->MBatch_edit_approval->calls[0];verifyWorkflow($call[0]===15112 && $call[1]===13239 && $call[6]==='baseline' && is_callable($call[7]),'controller forwards IDs token and locked validation callback');
$context=['cluster'=>['id_myrep_cluster'=>15112]];
$batch=['staging_status'=>'WAITING_FINANCE_RELEASE','astri_batch_number'=>'QA','astri_batch_approved_at'=>'2026-10-26','nominal_nego_emr'=>100,'released_at'=>'2026-10-28 10:30:00','po_donasi_number'=>'QA-PO','po_donasi_date'=>'2026-10-28','po_donasi_value'=>100,'invoice_donasi_number'=>'QA-INV','invoice_donasi_date'=>'2026-10-28','invoice_donasi_value'=>100];
$batch['id_batch_approval']=13239;
foreach(['DRAFT','WAITING HO','WAITING_BATCH_APPROVAL'] as $target){$bad=$batch;$bad['staging_status']=$target;verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','canonical stage mismatch denied: '.$target);}
verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$batch,'APPROVE')==='','complete finance-approved data passes callback');
$c->MBatch_Approval_MyRep->finance=false;verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$batch,'APPROVE')!=='','Saku approval requires Finance-approved documents');
$low=$batch;$low['staging_status']='DRAFT';unset($low['astri_batch_number'],$low['astri_batch_approved_at']);verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$low,'REQUEST')!=='','request Saku cannot bypass approval gate by choosing Draft');
$low['staging_status']='HOLD';foreach(['REQUEST','APPROVE'] as $action){verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$low,$action)!=='','Saku action requires EMR even on HOLD: '.$action);}
$c->MBatch_Approval_MyRep->finance=true;
foreach(['astri_batch_number'=>'','astri_batch_approved_at'=>'2026-02-30','nominal_nego_emr'=>0] as $field=>$value){$bad=$batch;$bad[$field]=$value;verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','EMR prerequisite enforced: '.$field);}
$c->MBatch_Approval_MyRep->pre=false;$bad=$batch;$bad['staging_status']='PRE_ZEYN_DOC_APPROVED';verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','stage1 approval requires SITAC-approved documents');$c->MBatch_Approval_MyRep->pre=true;
$bad=$batch;$bad['staging_status']='RELEASED';$bad['released_at']='0000-00-00';verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','release rejects zero date');
$c->MBatch_Approval_MyRep->post=false;$bad=$batch;$bad['staging_status']='WAITING_ASTRI_SUBMISSION';verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','Astri transition requires post-payment SITAC documents');$c->MBatch_Approval_MyRep->post=true;
$c->MBatch_Approval_MyRep->astri=false;foreach(['ASTRI_APPROVED','PO_DONASI','INVOICE','COMPLETED'] as $stage){$bad=$batch;$bad['staging_status']=$stage;verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','Astri full approval gate: '.$stage);}$c->MBatch_Approval_MyRep->astri=true;
$c->MBatch_Approval_MyRep->rejected=1;$bad=$batch;$bad['staging_status']='INVOICE';verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','Astri rejection prevents final stages');$c->MBatch_Approval_MyRep->rejected=0;
foreach(['po_donasi_number'=>'','po_donasi_date'=>'invalid','po_donasi_value'=>0,'invoice_donasi_number'=>'','invoice_donasi_date'=>'2026-02-30','invoice_donasi_value'=>0] as $field=>$value){$bad=$batch;$bad['staging_status']='INVOICE';$bad[$field]=$value;verifyWorkflow(callPrivate('validateWorkflowCorrection',$context,$bad,'KEEP')!=='','PO/invoice prerequisite: '.$field);}
foreach(['2026-02-30','0000-00-00','2026-10-28 99:00:00','2026-10-28 10:60:00','tomorrow'] as $date){verifyWorkflow(!callPrivate('validWorkflowDate',$date),'invalid workflow date: '.$date);}
verifyWorkflow(callPrivate('validWorkflowDate','2028-02-29 23:59:59'),'valid leap day accepted');
echo 'RESULT '.$checks.' PASS'.PHP_EOL;
