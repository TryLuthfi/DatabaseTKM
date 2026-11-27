<?php
/** CIKO independent extraction parity against actual pre-change Project source. No DB. */
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
class CI_Model {public $db;public $session;}
require APPPATH.'libraries/Myrep_project_status.php';
$proc=proc_open(['git','show','dccfc8f:application/models/MMyRepublik_Project.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,dirname(__DIR__));
fclose($pipes[0]);$source=stream_get_contents($pipes[1]);fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);
if(proc_close($proc)!==0||strpos($source,'private function resolveEffectiveStatus')===false)throw new RuntimeException('Frozen baseline source unavailable');
$source=str_replace('class MMyRepublik_Project extends','class CikoBaselineProject extends',$source);
eval('?>'.$source);
$ref=new ReflectionClass('CikoBaselineProject');$baseline=$ref->newInstanceWithoutConstructor();
$method=$ref->getMethod('resolveEffectiveStatus');$method->setAccessible(true);
$checks=0;function cq($ok,$label){global$checks;++$checks;if(!$ok)throw new RuntimeException($label);}
$statuses=['','DRAFT','NTP','BA OPEN','BAK','VALSAL','WAITING HO','WAITING MYREP','WAITING FINANCE','RELEASED','DONE BATCH APPROVAL','DRM','RFS','ATP','CHECKLIST','CHECKLIST DOKUMENT','DONE','IMPLEMENTASI','REJECTED','HOLD','unknown',' done '];
foreach($statuses as$status)foreach(['','DONE','PUNCLIST','NY',' done ']as$atp)foreach(['','PARTIAL','PARTIAL RFS','FULL RFS','NY RFS']as$rfs)foreach([null,'','0000-00-00','invalid-date','2026-11-09','2025-09-03']as$date){
    $row=['status_current'=>$status,'status_atp'=>$atp,'status_rfs'=>$rfs,'actual_atp_date'=>$date];
    $expected=$method->invoke($baseline,$row);$actual=Myrep_project_status::resolve($row);
    cq($actual===$expected,'Exact frozen Project parity:'.json_encode($row));
    if(trim(strtoupper($status))!=='CHECKLIST')cq(Myrep_project_status::rank($actual)>=Myrep_project_status::rank($status),'No stored-stage downgrade');
}
foreach(['tanggal_rfs','latest_claim_date']as$field)foreach([null,'0000-00-00','2026-11-27','bad-date']as$date){$row=['status_current'=>'DRM',$field=>$date];cq(Myrep_project_status::resolve($row)===$method->invoke($baseline,$row),'Date fallback parity');}
foreach([
    ['status_current'=>'ATP','status_atp'=>'DONE','actual_atp_date'=>'2025-09-03'],
    ['status_current'=>'DRM','status_atp'=>'DONE','actual_atp_date'=>'2026-11-09'],
]as$row)cq(Myrep_project_status::resolve($row)==='CHECKLIST DOKUMENT','Reported actual case Checklist');
$display=$ref->getMethod('resolveDisplayStatus');$display->setAccessible(true);
foreach($statuses as$status)foreach(['','IMPLEMENTASI','DONE IMPLEMENTASI','NY DRM']as$drm)foreach(['CLUSTER','SUBFEEDER','MAINFEEDER','FWA']as$type){$row=['status_current'=>$status,'status_drm'=>$drm,'project_type'=>$type];cq(Myrep_project_status::display($row)===$display->invoke($baseline,$row),'Frozen display parity');}
require APPPATH.'models/MMyRepublik_Project.php';
$newRef=new ReflectionClass('MMyRepublik_Project');$newModel=$newRef->newInstanceWithoutConstructor();
$newResolve=$newRef->getMethod('resolveEffectiveStatus');$newResolve->setAccessible(true);
$newDisplay=$newRef->getMethod('resolveDisplayStatus');$newDisplay->setAccessible(true);
foreach($statuses as$status){$row=['status_current'=>$status,'status_atp'=>'DONE','actual_atp_date'=>'2026-11-09','status_drm'=>'IMPLEMENTASI'];cq($newResolve->invoke($newModel,$row)===$method->invoke($baseline,$row),'Project delegates resolver');cq($newDisplay->invoke($newModel,$row)===$display->invoke($baseline,$row),'Project delegates display');}
require APPPATH.'models/MPO_MyRep.php';
class CikoStatusResult{private$rows;public function __construct($rows){$this->rows=$rows;}public function result_array(){return$this->rows;}public function row_array(){return$this->rows[0]??[];}}
class CikoStatusDb{
    public$queries=[];public$meta=[];public$absent=[];public$missingRfsId=false;
    public function field_exists($column,$table){cq($column==='rfs_cluster_id'&&$table==='tb_myrep_cluster','Only expected schema field probe');return!$this->missingRfsId;}
    public function table_exists($table){return!in_array($table,$this->absent,true);}
    public function query($sql,$bindings){
        cq(preg_match('/^SELECT\b/',$sql)===1,'Read-only metadata SELECT');cq(count($bindings)<=500,'Bounded500 query');cq(substr_count($sql,'?')===count($bindings),'Bound ids/placeholders match');
        cq(strpos($sql,'tanggal_rfs')===false&&strpos($sql,'claim_date')===false,'No extra date fallback drift from Projectlist');
        $this->queries[]=[$sql,$bindings];$out=[];foreach($bindings as$id)if(isset($this->meta[$id]))$out[]=$this->meta[$id];return new CikoStatusResult($out);
    }
}
$poRef=new ReflectionClass('MPO_MyRep');$po=$poRef->newInstanceWithoutConstructor();$apply=$poRef->getMethod('applyEffectiveProjectStatuses');$apply->setAccessible(true);$po->db=$db=new CikoStatusDb();
$rows=[
    ['id_myrep_cluster'=>1,'po_type'=>'CLUSTER','status_current'=>'ATP','po_number'=>'7400052155'],
    ['id_myrep_cluster'=>2,'po_type'=>'SUBFEEDER','status_current'=>'DRM','po_number'=>'7400152944'],
    ['id_myrep_cluster'=>3,'po_type'=>'CLUSTER','status_current'=>'DONE'],
    ['id_myrep_cluster'=>4,'po_type'=>'CLUSTER','status_current'=>'HOLD'],
    ['id_myrep_cluster'=>1,'id_mainfeeder'=>7,'po_type'=>'MAINFEEDER','status_current'=>'DRM'],
    ['id_myrep_cluster'=>1,'id_mainfeeder'=>8,'po_type'=>'FWA','status_current'=>'CHECKLIST'],
    ['id_myrep_cluster'=>5,'po_type'=>'CLUSTER','status_current'=>'DRM'],
];
foreach($rows as&$r)$r+=['id_po_header'=>123,'po_value'=>1000.25,'done_invoice_total'=>10.25,'termin_certificate_per_termin'=>[1=>'2026-11-05'],'termin_invoice_date_per_termin'=>[1=>'2026-11-06']];unset($r);
foreach([1=>'2025-09-03',2=>'2026-11-09',3=>'2026-11-09',4=>'2026-11-09']as$id=>$date)$db->meta[$id]=['id_myrep_cluster'=>$id,'status_current'=>$id===3?'DONE':($id===4?'HOLD':'DRM'),'status_atp'=>'DONE','status_rfs'=>'FULL RFS','actual_atp_date'=>$date,'status_drm'=>$id===3?'DONE IMPLEMENTASI':'DRM'];
$out=$apply->invoke($po,$rows);
cq(array_column($out,'status_current')===['CHECKLIST DOKUMENT','CHECKLIST DOKUMENT','IMPLEMENTASI','HOLD','DRM','CHECKLIST DOKUMENT','DRM'],'All target statuses + no mainfeeder/fwa borrowed cluster status');
cq(count($db->queries)===1&&$db->queries[0][1]===[1,2,3,4,5],'Only unique already-scoped cluster ids queried');
foreach($rows as$i=>$r){$a=$r;$b=$out[$i];unset($a['status_current'],$b['status_current']);cq($a===$b,'All financial/certificate/identity fields byte-preserved');}
$db->queries=[];cq($apply->invoke($po,[])===[]&&$db->queries===[],'Emptyrows no query');
$batch=[];for($id=1;$id<=1001;$id++)$batch[]=['id_myrep_cluster'=>$id,'po_type'=>'CLUSTER','status_current'=>'DRM'];$batch[]=$batch[0];
$apply->invoke($po,$batch);cq(array_map(fn($x)=>count($x[1]),$db->queries)===[500,500,1],'1001unique ids exactly3 bounded queries');
$ids=[];foreach($db->queries as$q)$ids=array_merge($ids,$q[1]);cq($ids===range(1,1001),'No unrelated or duplicate ids in batches');
foreach([['tb_rfs_myrep_cluster'],['tb_rfs_myrep_doc_package','tb_myrep_drm']]as$missing){$db->absent=$missing;$db->queries=[];$result=$apply->invoke($po,[['id_myrep_cluster'=>9,'po_type'=>'CLUSTER','status_current'=>'CHECKLIST']]);cq($result[0]['status_current']==='CHECKLIST DOKUMENT','Missing optional tables storedstatus fallback');if(in_array('tb_rfs_myrep_cluster',$missing,true))cq($db->queries===[],'No missingRFS SQL');else cq(strpos($db->queries[0][0],'NULL AS status_drm')!==false&&strpos($db->queries[0][0],'NULL AS actual_atp_date')!==false,'Nullprojection absent optional schema');}
$db->missingRfsId=true;$db->queries=[];$apply->invoke($po,[['id_myrep_cluster'=>9,'po_type'=>'CLUSTER','status_current'=>'ATP']]);cq($db->queries===[],'Missingbridge field no query');
$db->queries=[];$db->missingRfsId=false;$db->absent=[];$onlyMf=[['id_myrep_cluster'=>9999,'po_type'=>'MAINFEEDER','status_current'=>'CHECKLIST']];$result=$apply->invoke($po,$onlyMf);cq($db->queries===[]&&$result[0]['status_current']==='CHECKLIST DOKUMENT','Mainfeeder-only never queries linkedcluster');
// Exercise real public PO-list path with existing city restriction and termin projection.
class CikoScopedDb extends CikoStatusDb{
    public$headers=[];public$terms=[];public$table='';public$conditions=[];public$builderReads=[];
    public function table_exists($table){return in_array($table,['tb_myrep_cluster','tb_myrep_po_header','tb_myrep_po_termin','tb_rfs_myrep_cluster','tb_rfs_myrep_doc_package','tb_myrep_drm'],true);}
    public function field_exists($field,$table){return in_array($field,['rfs_cluster_id','invoice_value','sertifikat_invoice_date'],true);}
    public function escape($value){return "'".str_replace("'","''",$value)."'";}
    public function select($columns,$escape=null){return$this;}public function from($table){$this->table=$table;return$this;}public function join($table,$on,$type){return$this;}public function order_by($column,$direction){return$this;}
    public function where($field,$value=null,$escape=null){$this->conditions[]=[$field,$value];return$this;}
    public function where_in($field,$values){$this->conditions[]=[$field,$values];return$this;}public function group_start(){return$this;}public function group_end(){return$this;}
    public function reset_query(){$this->conditions=[];return$this;}
    public function get(){
        $this->builderReads[]=[$this->table,$this->conditions];$isHeader=$this->table==='tb_myrep_po_header p';$rows=$isHeader?$this->headers:$this->terms;
        foreach($this->conditions as[$field,$value]){
            if($field==='UPPER(c.city_name)')$rows=array_values(array_filter($rows,fn($r)=>strtoupper($r['city_name'])===$value));
            elseif(strpos($field,'UPPER(c.city_name) IN')===0){preg_match_all("/'([^']*)'/",$field,$m);$rows=array_values(array_filter($rows,fn($r)=>in_array(strtoupper($r['city_name']),$m[1],true)));}
            elseif($field==='id_po_header')$rows=array_values(array_filter($rows,fn($r)=>in_array($r['id_po_header'],$value,true)));
            else throw new RuntimeException('Unexpected scoping condition');
        }
        $this->conditions=[];return new CikoStatusResult($rows);
    }
}
class CikoStaffSession{public function userdata($key){return['id_user'=>40,'id_level'=>3,'nama_level'=>'Staff'][$key]??null;}}
$poScoped=$poRef->newInstanceWithoutConstructor();$poScoped->session=new CikoStaffSession();$poScoped->db=$scoped=new CikoScopedDb();
$cityProperty=$poRef->getProperty('currentUserAllowedCitySet');$cityProperty->setAccessible(true);$cityProperty->setValue($poScoped,['CITY A'=>true]);
foreach([1=>'CITY A',2=>'CITY B']as$id=>$city){
    $scoped->headers[]=['id_po_header'=>$id,'id_myrep_cluster'=>$id,'id_mainfeeder'=>null,'po_type'=>'CLUSTER','po_category'=>'FINAL','po_number'=>'QC-'.$id,'po_date'=>'2026-11-01','po_value'=>1000.25,'status_po'=>'ISSUED','po_version_label'=>'FINAL','cluster_name'=>'CLUSTER-'.$id,'city_name'=>$city,'regional_name'=>'R','status_current'=>'DRM'];
    $scoped->terms[]=['id_po_header'=>$id,'termin_no'=>1,'termin_value'=>200.25,'invoice_value'=>180.25,'status_termin'=>'BILLED','invoice_date'=>'2026-11-20','remark_termin'=>'Plan Invoice: 200.25','sertifikat_invoice_date'=>'2026-11-19'];
    $scoped->meta[$id]=['id_myrep_cluster'=>$id,'status_current'=>'DRM','status_atp'=>'DONE','status_rfs'=>'FULL RFS','actual_atp_date'=>'2026-11-09','status_drm'=>'DRM'];
}
$public=$poScoped->getPoListRows('CITY A','');
cq(count($public)===1&&$public[0]['status_current']==='CHECKLIST DOKUMENT','Public PO-list effective status after authorized cityscope');
cq($scoped->queries[0][1]===[1],'Public path metadata never requests unauthorized city id2');
cq($public[0]['plan_invoice_total']===200.25&&$public[0]['done_invoice_total']===180.25,'Public plan/invoice values preserved');
cq($public[0]['termin_certificate_per_termin'][1]==='2026-11-19','Public certificate date preserved');
cq($public[0]['termin_invoice_date_per_termin'][1]==='2026-11-20','Public invoice date preserved');
$page=$poScoped->getPoListDataTable('CITY A','','','','0',10,'QC-1');cq($page['recordsFiltered']===1&&$page['rows'][0]['status_current']==='CHECKLIST DOKUMENT','Public datatable retains effective CurrentStatus after existing PO search');
$scoped->queries=[];$cityProperty->setValue($poScoped,[]);cq($poScoped->getPoListRows('','')===[]&&$scoped->queries===[],'Unauthorized cityset no rows and no metadata requests');
echo "PASS $checks independent frozen-source and PO projection assertions\n";
