<?php
// Independent CIKO controller guard/input/error contract check, no DB/network.
define('BASEPATH', __DIR__ . '/../system/');
class CI_Model {}
require __DIR__.'/../application/models/MAkselerasi_2026.php';
$checks=0; $moduleAllow=true; $pageAllow=true; $guards=[]; $inputValue=null; $serviceFail=false;
function verify($condition,$label) {global $checks; ++$checks; if(!$condition)throw new RuntimeException($label);}
function enforce_database_all_po_access(){global $moduleAllow,$guards; $guards[]='module'; if(!$moduleAllow)throw new DomainException('module denied');}
function has_user_page_access($module,$page,$action,$id){global $pageAllow,$guards; $guards[]=[$module,$page,$action,$id]; return $pageAllow;}
function render_no_access($message){throw new DomainException('page denied');}
function log_message($level,$message){}
class QcInput{public function get($key){global $inputValue; verify($key==='end_date','Only cutoff accepted');return $inputValue;}}
class QcOutput{public $status=200;public function set_status_header($status){$this->status=$status;}}
class QcSession{public function userdata($key){verify($key==='id_user','User access checks session user');return 42;}}
class QcService{public $calls=[];public function loadDashboard($date){global $serviceFail;$this->calls[]=$date;if($serviceFail)throw new RuntimeException('secret SQL details');return MAkselerasi_2026::buildDashboard([],[],$date);}}
class QcLoad{public $controller;public $views=[];public function model($name){verify($name==='MAkselerasi_2026','CI3 model loaded');$this->controller->MAkselerasi_2026=new QcService();}public function view($name,$data=[]){$this->views[]=[$name,$data];}}
class CI_Controller{public $load;public $input;public $output;public $db;public $session;public $MAkselerasi_2026;public function __construct(){$this->load=new QcLoad();$this->load->controller=$this;$this->input=new QcInput();$this->output=new QcOutput();$this->db=(object)['db_debug'=>true];$this->session=new QcSession();}}
require __DIR__.'/../application/controllers/Akselerasi_2026.php';
$moduleAllow=false; $blocked=false;try{new Akselerasi_2026();}catch(DomainException $e){$blocked=true;}verify($blocked,'Module denial stops model construction');
$moduleAllow=true;$pageAllow=false;$blocked=false;try{new Akselerasi_2026();}catch(DomainException $e){$blocked=true;}verify($blocked,'PO Monitor page VIEW denial stops controller');
verify(end($guards)===['Database All PO','PO_Monitor','VIEW',42],'Exact existing page permission reused');
$pageAllow=true;$inputValue='2026-12-25';$controller=new Akselerasi_2026();$controller->index();
verify($controller->MAkselerasi_2026->calls===['2026-12-25'],'Flexible cutoff reaches model');
verify(count($controller->load->views)===5,'Existing full template stack rendered');
verify($controller->load->views[2][0]==='Akselerasi_2026/index','Correct view');
verify($controller->load->views[2][1]['title']==='TARGET INVOICE AKSELERASI 2026','Exact page title');
verify($controller->db->db_debug===true,'DB debug restored after success');
foreach (['2026-11-15','2026-02-30',['2026-11-17'],'2026-11-17 OR 1=1']as$bad){$inputValue=$bad;$c=new Akselerasi_2026();$c->index();verify($c->output->status===422,'Invalid cutoff returns422');verify($c->MAkselerasi_2026->calls===[],'Invalid cutoff never queries');verify($c->load->views[2][1]['error']!=='','Invalid cutoff visible error');}
$serviceFail=true;$inputValue='2026-11-17';$c=new Akselerasi_2026();$c->index();verify($c->output->status===503,'Source failure returns503');verify($c->db->db_debug===true,'DB debug restored after failure');verify(strpos($c->load->views[2][1]['error'],'secret')===false,'No query details leaked');verify($c->load->views[2][1]['dashboard']['rows']===[],'Source failure not stale data');
echo "PASS $checks independent controller assertions\n";
