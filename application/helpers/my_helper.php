<?php
/**
*@if(CheckPermission('crm', 'controller','read')) 
**/
	
function login($params=array())
{  
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('users');       // and (access_level=1 or access_level=4 or access_level=5)
	$CI->db->where("password='".$params["password"]."' and (`user_name` LIKE '%".$params["username"]."%' or `email` LIKE '%".$params["username"]."%')");        
	$query = $CI->db->get();        
	$result=$query->row();
	
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
	//return $query->row();
}

function dayEndProcess(){
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('daily_account_closing'); 
	$CI->db->where("opening_date='".date('Y-m-d')."'");        
	$query = $CI->db->get();        
	$result=$query->row();	
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function previousDayEndData(){
	//SELECT * FROM `daily_account_closing` WHERE `opening_date`<CURDATE() limit 1;
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('daily_account_closing'); 
	$CI->db->where("opening_date<CURDATE()");   
	$CI->db->order_by("opening_date","desc");     
	$query = $CI->db->get();        
	$result=$query->row();	
	if(!empty($result)){
		return $result;
	}
	else
	{
		return array(
			"dac_id"=>0,
			"closing_date"=>date('Y-m-d'),
			"opening_date"=>date('Y-m-d'),
			"closing_amount"=>0,
			"income"=>0,
			"expense"=>0,
			"opening_balance"=>0
		);
	}
}

function getIncomeExpense($transaction_date){
	$CI = get_instance();
	$CI->db->select("COALESCE(SUM(CASE WHEN cr_or_dr='CREDIT' THEN amount ELSE 0 END),0) AS total_income, COALESCE(SUM(CASE WHEN cr_or_dr='DEBIT' THEN amount ELSE 0 END),0) AS total_expense");
	$CI->db->from("income_expense");
	$CI->db->where("transaction_date  BETWEEN '".$transaction_date."' and '".date('Y-m-d')."'");        
	$query = $CI->db->get();        
	$result=$query->row();	
	if(!empty($result)){
		return $result;
	}
	else
	{
		return array(
			"total_income"=>0,
			"total_expense"=>0
		);
	}
}


function getUserName($id)
{  	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('users');
	$CI->db->where('user_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->user_name;
	}
	else
	{
		return "";
	}
}

function getGrandTOtal($q, $report_start_date, $report_end_date){
	$CI = get_instance();
	$CI->db->select('sum(i.grand_total) as grand_total');
	$CI->db->from('invoices i');
	$CI->db->join('customer_details cd', 'cd.customer_id = i.customer_id', 'left');
    $CI->db->where('i.branch=' . $_SESSION['loginUser']->branch . ' and(date(invoice_date) BETWEEN \''.$report_start_date.'\' and \''.$report_end_date.'\') and (i.invoice_number LIKE "%' . $q . '%" or i.invoice_date LIKE "%' . $q . '%" or i.invoice_due_date LIKE "%' . $q . '%" or i.total_cgst_percent LIKE "%' . $q . '%" or i.total_sgst_percent LIKE "%' . $q . '%" or i.total_gst_percent LIKE "%' . $q . '%" or i.total_cgst_amount LIKE "%' . $q . '%" or i.total_sgst_amount LIKE "%' . $q . '%" or i.total_gst_amount LIKE "%' . $q . '%" or i.discount_percent LIKE "%' . $q . '%" or i.discount_amount LIKE "%' . $q . '%" or i.grand_total LIKE "%' . $q . '%"  or i.balance_amount LIKE "%' . $q . '%" or i.notes LIKE "%' . $q . '%" or cd.customer_name like "%' . $q . '%" or cd.company_contact_no like "%' . $q . '%" or cd.alternate_mobile_number like "%' . $q . '%" )');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->grand_total;
	}
	else
	{
		return 0;
	}
}

function getPermission($page, $action){
	// Register the custom error handler
	// set_error_handler("customErrorHandler");
	$permission = false;
	$CI = get_instance();
	$userPermissions=$CI->session->userdata('userPermissions');
	// print_r($userPermissions);
	if(!empty($userPermissions)){
		try{
			if(($userPermissions->role_name == 'SUPERADMIN' || $userPermissions->role_name == 'ADMIN' 
			|| $userPermissions->role_name == 'MANAGER') &&  $page=="dayend"){
				$permission = true;	
				//die("yes");
			}
			else if($userPermissions->role_name == 'SUPERADMIN'){
				$permission = true;	
				// print_r("Super admin");die();
			}else{
				$permissions = json_decode($userPermissions->permissions);
				//echo $permissions->$page;
				if(!empty($userPermissions->permissions) && !empty($permissions->$page)){
					//echo "----";
					//print_r($permissions);die();
					$pagePermission = $permissions->$page;
					// print_r($pagePermission);
					if($pagePermission && !empty($pagePermission) && !empty($pagePermission->permission) && !empty($pagePermission->permission->$action)){
						// if($action == "view")
						return $pagePermission->permission->$action;

						// if($pagePermission->permission =="DENIED")
						// 	$permission = false;	
						// else if ($pagePermission->permission =="READ" && $action == "WRITE")
						// 	$permission = false;
						// else if ($pagePermission->permission == "WRITE")
						// 	$permission = true;
					}
				}else{
					// die("e");
				}
			}
		} catch (Exception $e) {
			// error_log("Caught exception: " . $e->getMessage());
			$permission = false;
		}
	}
	//die("--".$permission);
	return $permission;
}
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // This custom error handler will suppress errors and return false.
    return false;
}

function getBranches()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('branch_details');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getBranchName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('branch_details');
	$CI->db->where('branch_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->branch_name;
	}
	else
	{
		return "InfoBell";
	}
}

function insertBackup($path){
	$data = array(
		"file_path"=>$path
	);
	$CI = get_instance();
	$CI->db->insert("backup_db", $data);
}

function checkBackExist()
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->where("date(backup_date) = CURRENT_DATE()");
	$CI->db->order_by("backup_date","asc");
	$CI->db->from('backup_db');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getYesterdayBackup()
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->where("date(backup_date) < CURRENT_DATE() ");
	$CI->db->order_by("backup_date","desc");
	$CI->db->from('backup_db');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}



function getBankName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('bank_accounts');
	$CI->db->where('ba_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->account_no." (".$result->bank_name.")";
	}
	else
	{
		return "";
	}
}

function getAccounts()
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('bank_accounts b');
	$CI->db->where('b.status=1');
	$CI->db->join("users u","u.user_id=b.employee_id");
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getAllTransactionsByType($type="",$pkid="")
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('bank_transactions');
	if($pkid=="")
		$CI->db->where('transaction_type="'.$type.'"');
	else
		$CI->db->where('transaction_type="'.$type.'" and pk_id='.$pkid);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getPayModes()
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('pay_modes');
	$CI->db->where('status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getEmployees($bid)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('users');
	$CI->db->where('access_level<>1 and branch='.$bid);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getSalaryInc($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('employee_salary_info s');
	$CI->db->where('s.user_id='.$id.'');
	$CI->db->order_by('s.esi_id', "desc");
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getEmployeeAdvanceDetails($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('employee_advance_details s');
	$CI->db->where('s.employee_id='.$id.'');
	$CI->db->order_by('s.edd_id', "desc");
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getEmployeeSalaryInfo($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('employee_salary_info s');
	$CI->db->where('s.user_id='.$id.'');
	$CI->db->order_by('s.esi_id', "desc");
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getEmployeeLastSalaryInfo($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('employee_salary_info s');
	$CI->db->where('s.user_id='.$id.'');
	$CI->db->order_by('s.esi_id', "desc");
	$CI->db->limit(1);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getAttendanceByUserId($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('daily_attendance d');
	$CI->db->where('d.employee_id='.$id.' and DATE(d.in_time)=CURDATE()');
	$CI->db->join('users u', "u.user_id=d.employee_id");
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function isPresentByUserId($id, $cdate)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('daily_attendance d');
	$CI->db->where('d.employee_id='.$id.' and DATE(d.in_time)= DATE("'.$cdate.'")');
	//$CI->db->join('users u', "u.user_id=d.employee_id");
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return "P";
	else{
		$CI->db->select('*');
		$CI->db->from('holidays');
		$CI->db->where('DATE(holiday_date)= DATE("'.$cdate.'")');
		$query = $CI->db->get();
		$result = $query->row();
		if(!empty($result))
			return "<span class='text-danger'>H</span>";
		else{
			if(date("D",strtotime($cdate))=="Sun")
				return "H";
			else
				return "A";
		}
	}
}


function getCategoryName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('category');
	$CI->db->where('category_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->category_name;
	}
	else
	{
		return "";
	}
}


function getOrInsetProductBrand($name)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_brand');
	$CI->db->where('brand_name' , $name);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->brand_id;
	else{
	    $CI->db->insert('product_brand', array(
	        "brand_name"=>$name,
	        "category_id"=>1,
	        "status"=>1,
	        'created_by' => $_SESSION['loginUser']->user_id,
			'created_date' => date("Y-m-d H:i:s"),
	        ));
		return $CI->db->insert_id();
	}
}

function getOrInsetProductModal($name)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_model');
	$CI->db->where('model_name' , $name);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->model_id;
	else{
	    $CI->db->insert('product_model', array(
	        "branch"=>1,
	        "model_name"=>$name,
	        "brand_id"=>1,
	        "status"=>1,
	        'created_by' => $_SESSION['loginUser']->user_id,
			'created_date' => date("Y-m-d H:i:s"),
	        ));
		return $CI->db->insert_id();
	}
}

function getPendingInvoices($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	// $CI->db->where("date(task_due_date) >= CURRENT_DATE()");
	$CI->db->order_by("invoice_id","asc");
	$CI->db->from('invoices');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getInvoiceStatus($status){
	$statuscolor = (settingColor('invoice', $status)) ? settingColor('invoice', $status) : '#ff0000';
	$statusDiv='<span class="label " style="background-color:' . $statuscolor . ';">New</span>';
								if ($status == 0) {
									$statusDiv= '<span class="label " style="background-color:' . $statuscolor . ';">New</span>';
								} else if ($status == 1) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Viewed</span>';
								} else if ($status == 2) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Accept</span>';
								} else if ($status == 3) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Rejected</span>';
								} else if ($status == 4) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Paid</span>';
								} else if ($status == 5) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Balance</span>';
								} else if ($status == 6) {
									$statusDiv= '<span class="label" style="background-color:' . $statuscolor . ';">Cancel Invoice</span>';
								}
								return $statusDiv;
}

function getFeatureTask($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->where("date(task_due_date) >= CURRENT_DATE()");
	$CI->db->order_by("task_due_date","asc");
	$CI->db->from('task_details');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getCategories()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('category');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getBrands()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_brand');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result))
		return $result;
	else
		return false;
}

function getBrandName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_brand');
	$CI->db->where('brand_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->brand_name;
	else
		return "";
}

function getModels()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_model');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result))
		return $result;
	else
		return false;
}

function getModelName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_model');
	$CI->db->where('model_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->model_name;
	else
		return "InfoBell";
}

function getColors()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_color');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result))
		return $result;
	else
		return false;
}

function getColorName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('product_color');
	$CI->db->where('color_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->color_value;
	else
		return "InfoBell";
}

/*function getActiveMeasures($bid)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('unit_of_measures');
	$CI->db->where('branch' , $bid);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result))
		return $result;
	else
		return false;
}

*/

function getCustomerId($data)
{  
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('customer_details'); 
	$CI->db->where("company_contact_no='".$data["company_contact_no"]."'");  
	$query = $CI->db->get();        
	$result=$query->row();
	
	if(!empty($result)){
		return $result->customer_id;
	}
	else
	{
		return "";
	}
}

function checkCustomerExist($id="",$field="",$value="")
{  
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('customer_details'); 
	if($id=="")
		$CI->db->where($field."='".$value."'");  
	else
		$CI->db->where($field."='".$value."'  and customer_id<>".$id);  
	$query = $CI->db->get();        
	$result=$query->row();
	
	if(!empty($result)){
		return true;
	}
	else
	{
		return false;
	}
}

function checkUserExist($id="",$field="",$value="")
{  
	$CI = get_instance();
	$CI->db->select('*');            
	$CI->db->from('users'); 
	if($id=="")
		$CI->db->where($field."='".$value."' and (access_level=1 or access_level=4 or access_level=5 or access_level=6)");  
	else
		$CI->db->where($field."='".$value."' and (access_level=1 or access_level=4 or access_level=5 or access_level=6)   and user_id<>".$id);  
	$query = $CI->db->get();        
	$result=$query->row();
	
	if(!empty($result)){
		return true;
	}
	else
	{
		return false;
	}
}

function isLogin(){ 
	//die("sssssssssss");
	if(isset($_SESSION['loginUser'])){
		return true;
	}else{
		redirect( base_url().'admin/login', 'refresh');
		//return true;
	}
}

function getActiveUsers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('users');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getRoles()
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->where('role_name<>"SUPERADMIN"');
	$CI->db->from('roles');
		
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getRoleName($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('roles');
	$CI->db->where('role_id ' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->role_name;
	else
		return "SUPERADMIN";
}


function userRole($role='',$field='scope')
{  
	if(empty($field))
		$field='scope';
	$CI = get_instance();
	if(!empty($role)){
		$CI->db->select('*');
		$CI->db->from('oauth_scopes');
		$CI->db->where('oas_id' , $role);
		$query = $CI->db->get();
		$result1 = $query->row();
		if(!empty($result1)){
			 $result = $result1->$field;
			return $result;
		}
		else
		{
			return false;
		}
	}
	else{
		$CI->db->select('*');
		$CI->db->from('oauth_scopes');
		$query = $CI->db->get();
		$setting = $query->result();
		$result = array(); 
		foreach ($setting as $key => $value) {
			$result[$value->oas_id] = $value->$field;
		}
		return $result;
	}
}

function getUserAccessLevel($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('oauth_scopes');
	$CI->db->where('oas_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->scope;
	}
	else
	{
		return false;
	}
	
}

function getCategoryType($id,$lang="en")
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('category_type');
	$CI->db->where('ct_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		if($lang=="en")
			return $result->category_type;
		else
			return $result->category_type_ar;
	}
	else
	{
		return false;
	}
}

function getEventName($id,$lang="en")
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('events');
	$CI->db->where('event_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	
	if(!empty($result)){
		if($lang=="en")
			return $result->title;
		else
			return $result->title_ar;
	}
	else
	{
		return false;
	}
}

function getCategory($id,$lang="en")
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('category_master');
	$CI->db->where('cat_mas_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		if($lang=="en")
			return $result->name;
		else
			return $result->name_ar;
	}
	else
	{
		return false;
	}
}

function getActiveSuppliers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('supplier_details');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getSupplierDetails($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('supplier_details');
	$CI->db->where('status=1 and supplier_id='.$id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getActiveGSTPercent($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('tax_rates');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getActiveCustomers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('customer_details');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getTotalCustomers($branch=2)
{  
	$CI = get_instance();
	$CI->db->from('customer_details');
	$CI->db->where('branch='.$branch.'');
    return $CI->db->count_all_results();
}

function getTotalBuying($branch=2)
{  
	/*$CI = get_instance();
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.'');
    return $CI->db->count_all_results();*/
	$CI = get_instance();
	$CI->db->select('sum(total_qty) as total_qty');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->total_qty;
	}
	else
	{
		return 0;
	}
}

function getTotalBuyingAmt($branch=2)
{  
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(total_amount) as total_amount');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->total_amount!=null)?$result->total_amount:0;
	}
	else
	{
		return 0;
	}
}

function getTotalSelling($branch=2)
{  
	/*$CI = get_instance();
	$CI->db->from('stock_outward');
	$CI->db->where('branch='.$branch.'');
    return $CI->db->count_all_results();*/
	$CI = get_instance();
	$CI->db->select('sum(qty) as qty');
	$CI->db->from('stock_outward');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->qty;
	}
	else
	{
		return 0;
	}
}

function getTotalSellingAmt($branch=2)
{  
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(amount) as amount');
	$CI->db->from('stock_outward');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->amount!=null)?$result->amount:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodaySelling($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('sum(qty) as qty');
	$CI->db->from('stock_outward');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->qty!=null)?$result->qty:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodaySellingAmt($branch=2)
{  
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(amount) as amount');
	$CI->db->from('stock_outward');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->amount!=null)?$result->amount:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayBuying($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('sum(qty) as total_qty');
	$CI->db->from('stock_inward_history');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->total_qty!=null)?$result->total_qty:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayCustomers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('count(*) as total_in');
	$CI->db->from('customer_details');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		return ($result->total_in!=null)?$result->total_in:0;
	}
	else
	{
		return 0;
	}
}

/*function getTotalCustomers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('count(*) as total_in');
	$CI->db->from('customer_details');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		return ($result->total_in!=null)?$result->total_in:0;
	}
	else
	{
		return 0;
	}
}*/

function getTotalTodayInvoices($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('count(*) as total_in');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->total_in!=null)?$result->total_in:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayInvoicesAmt($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('sum(grand_total) as grand_total');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->grand_total!=null)?$result->grand_total:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayBuyingAmt($branch=2)
{  
/*SELECT *, (DATEDIFF(NOW(), created_date)) AS diff , sum(grand_total) as grand_total
FROM invoices 
WHERE created_date <= (DATE(NOW()) - INTERVAL 30 DAY)
group by diff*/
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(total_amount) as total_amount');
	$CI->db->from('stock_inward_history');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->total_amount!=null)?$result->total_amount:0;
	}
	else
	{
		return 0;
	}
}


function getMonthProfitByYear($branch=2,$y)
{  
	//select *,MONTH(a.cdate) as month_index from(SELECT sum(grand_total) as grand_total,max(created_date) as cdate FROM `invoices` WHERE YEAR(created_date) = 2023 GROUP BY YEAR(created_date),MONTH(created_date)) as a
	$CI = get_instance();
	// $CI->db->select('sum(grand_total) as grand_total,max(created_date) as cdate');
	// $CI->db->from('invoices');
	// if(!empty($y))
	// 	$CI->db->where(' YEAR(created_date) = '.$y);
	// else
	// 	$CI->db->where('YEAR(created_date) = YEAR(CURRENT_DATE())');
	// $CI->db->group_by('MONTH(created_date)');
	// $query = $CI->db->get();
	$sql = "select *,MONTH(a.cdate)-1 as month_index from(SELECT sum(grand_total) as grand_total,max(created_date) as cdate FROM `invoices` WHERE YEAR(created_date) = YEAR(CURRENT_DATE()) ".$y." GROUP BY YEAR(created_date),MONTH(created_date)) as a";
	if(!empty($y))
		$sql ="select *,MONTH(a.cdate)-1 as month_index from(SELECT sum(grand_total) as grand_total,max(created_date) as cdate FROM `invoices` WHERE YEAR(created_date) =  ".$y." GROUP BY YEAR(created_date),MONTH(created_date)) as a";

	$result = $CI->db->query($sql)->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return array();
	}
}

function getNMonthProfitByYear($branch=2,$i=0,$y)
{  
	$CI = get_instance();
	$CI->db->select('sum(grand_total) as grand_total');
	$CI->db->from('invoices');
	if(!empty($y))
		$CI->db->where('MONTH(created_date) = '.$i.' AND YEAR(created_date) = '.$y);
	else
		$CI->db->where('MONTH(created_date) = '.$i.' AND YEAR(created_date) = YEAR(CURRENT_DATE())');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->grand_total!=null)?$result->grand_total:0;
	}
	else
	{
		return 0;
	}
}

function getNMonthProfit($branch=2,$i=0,$yn=0)
{  
//SELECT * FROM `invoices` where created_date > curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month GROUP BY YEAR(created_date), MONTH(created_date), DATENAME(MONTH, created_date)
	$CI = get_instance();
	$CI->db->select('sum(grand_total) as grand_total');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.' and MONTH(created_date) = '.$i.' AND YEAR(created_date) = '.$yn);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->grand_total!=null)?$result->grand_total:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayInvoice($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('count(*) as total_count');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result) && $result!=null){
		//die("11");
		return ($result->total_count!=null)?$result->total_count:0;
	}
	else
	{
		return 0;
	}
}

function getTotalTodayInvoiceAmt($branch=2)
{  
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(grand_total) as grand_total');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.' and DATE(created_date)=CURDATE()');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->grand_total!=null)?$result->grand_total:0;
	}
	else
	{
		return 0;
	}
}

function getTotalInvoice($branch=2)
{  
	$CI = get_instance();
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.'');
    return $CI->db->count_all_results();
}

function getTotalInvoiceAmt($branch=2)
{  
	//SELECT sum(total_amount) as total_amount  FROM `stock_inward` WHERE `branch` = 2
	$CI = get_instance();
	$CI->db->select('sum(grand_total) as grand_total');
	$CI->db->from('invoices');
	$CI->db->where('branch='.$branch.'');
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return ($result->grand_total!=null)?$result->grand_total:0;
	}
	else
	{
		return 0;
	}
}

function getRecentProducts($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward_history');
	$CI->db->where('branch='.$branch.'');
	$CI->db->order_by('created_date', 'desc');
	$CI->db->limit(10, 0);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getRiskProducts($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.' and qty <=min_qty');
	$CI->db->order_by('qty', ' DESC ');
	$CI->db->limit(10, 0);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getHighRiskProducts($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.' and qty <=high_risk_min_qty');
	$CI->db->order_by('qty', ' DESC ');
	$CI->db->limit(10, 0);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getTopTenCustomers($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*,count(*) as totalcount,sum(grand_total) as totalamount');
	$CI->db->from('invoices');
	$CI->db->join('customer_details','customer_details.customer_id=invoices.customer_id');
	$CI->db->where('invoices.branch='.$branch.' and invoices.customer_id<>"" and invoices.customer_id<>0');
	$CI->db->group_by('invoices.customer_id');
	$CI->db->order_by('totalamount', ' DESC ');
	$CI->db->limit(10, 0);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getTopTenSelling($branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*,count(*) as totalcount,sum(stock_outward.qty) as tqty,sum(stock_outward.amount) as totalamount');
	$CI->db->from('stock_outward');
	$CI->db->join('stock_inward','stock_inward.stcok_inward_id=stock_outward.stock_inward_id');
	$CI->db->where('stock_outward.branch='.$branch.'');
	$CI->db->group_by('stock_outward.stock_inward_id');
	$CI->db->order_by('tqty', ' DESC ');
	$CI->db->limit(10, 0);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getInvoiceMaterials($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('invoice_details');
	$CI->db->where('invoice_id='.$id);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getQuotationMaterials($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('quotation_details');
	$CI->db->where('invoice_id='.$id);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getCustomerDetails($cid)
{  
	if($cid!=""){
		
	}else{
		
	}
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('customer_details');
	$CI->db->where('customer_id='.$cid);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getHSNCodeByMaterialid($mid)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('stcok_inward_id='.$mid);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->hsn_code;
	}
	else
	{
		return false;
	}
}


function getNameByMaterialid($mid)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('stcok_inward_id='.$mid);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->item_name;
	}
	else
	{
		return false;
	}
}


function getHSNMaterials($matid,$branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.' and status=1 and stcok_inward_id="'.$matid.'"');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getMaterialsByName($name,$branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.' and status=1 and stcok_inward_id="'.$name.'"');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getUOMName($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('unit_of_measures');
	$CI->db->where('unit_id='.$id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->unit_code;
	}
	else
	{
		return "";
	}
}

function getMaterials($branch=1)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}




function getMaterialDetails($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('stock_inward');
	$CI->db->where('stcok_inward_id='.$id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function getActiveMeasures($branch=1)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('unit_of_measures');
	$CI->db->where('branch='.$branch.' and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getMeasuresName($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('unit_of_measures');
	$CI->db->where('unit_id' , $id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result))
		return $result->unit_code;
	else
		return "InfoBell";
}



// insert data
function insertSystemLog($data)
{
	$CI = get_instance();
    $CI->db->insert('system_logs', $data);
}

function getAllActivePlayers()
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('users');
	$CI->db->where('access_level=2 and status=1');
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}

function getCustomerName($cid)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('customer_details');
	$CI->db->where('customer_id='.$cid);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result->customer_name;
	}
	else
	{
		return "";
	}
}

function getInvoices($id,$cid="",$branch=2)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('invoices');
	if($id!=""){
		//die("if");
		$CI->db->where('branch='.$branch.' and invoice_id='.$id);
		$query = $CI->db->get();
		$result = $query->result();
		if(!empty($result)){
			return $result;
		}
		else
		{
			return false;
		}
	}else{
		//die("else");
		if($cid!="")
			$CI->db->where('branch='.$branch.' and status<>4 and customer_id='.$cid);
		else
			$CI->db->where('branch='.$branch.' and status<>4');
		$CI->db->order_by('invoice_id', 'DESC');
		$query = $CI->db->get();
		$result = $query->result();
		if(!empty($result)){
			return $result;
		}
		else
		{
			return false;
		}
	}
	
}

function getInvoice($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('invoices');
	$CI->db->where('invoice_id='.$id);
	$query = $CI->db->get();
	$result = $query->row();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
	
}

function getInvoicePayments($id)
{  
	
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('invoice_payment');
	$CI->db->where('invoice_id='.$id);
	$query = $CI->db->get();
	$result = $query->result();
	if(!empty($result)){
		return $result;
	}
	else
	{
		return false;
	}
}


function settingColor($keys='', $status )
{  
	$CI = get_instance();
	if(!empty($keys)){
		$CI->db->select('*');
		$CI->db->from('setting');
		$CI->db->where('key_field' , $keys);
		$query = $CI->db->get();
		$result = $query->row();
		if(!empty($result)){
			$result = json_decode($result->value);
			// echo $status."--";print_r($result);die();
			return $result->$status;
		}
		else
		{
			return false;
		}
	}
	else{
		/*$CI->load->model('setting_model');
		$setting= $CI->setting_model->get_setting();*/
		$CI->db->select('*');
		$CI->db->from('setting');
		$query = $CI->db->get();
		$setting = $query->result();
		//echo "<pre>";print_r($setting);die();
		$result = array(); 
		foreach ($setting as $key => $value) {
			$result[$value->key_field] = $value->value;
		}
		return $result;
	}
}
function settingAll($keys='')
{  
	$CI = get_instance();
	if(!empty($keys)){
		$CI->db->select('*');
		$CI->db->from('setting');
		$CI->db->where('key_field' , $keys);
		$query = $CI->db->get();
		$result = $query->row();
		if(!empty($result)){
			// $result = $result->value;
			return $result->value;
		}
		else
		{
			return "MY";
		}
	}
	else{
		/*$CI->load->model('setting_model');
		$setting= $CI->setting_model->get_setting();*/
		$CI->db->select('*');
		$CI->db->from('setting');
		$query = $CI->db->get();
		$setting = $query->result();
		//echo "<pre>";print_r($setting);die();
		$result = array(); 
		foreach ($setting as $key => $value) {
			$result[$value->key_field] = $value->value;
		}
		return $result;
	}
}

function settings() {
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('setting');
	$query = $CI->db->get();
	$setting = $query->result();
	$result = array(); 
	foreach ($setting as $key => $value) {
		$result[$value->key_field] = $value->value;
	}
	return $result;
}

				
/* 
 * $targetPath_md ='uploads/bodyimage/top/';
 * $targetPath_sm='uploads/bodyimage/top/';
 * $bodyimage=$this->fileUpload('image_url_top',$targetPath_md,'',FALSE,$targetPath_sm,'400','320');
 * $urlpath=$targetPath_md.$bodyimage;
 */					
function fileUpload($field_name = '', $target_md = '', $file_name = '', $thumb = FALSE, $target_sm = '', $sm_width = '400', $sm_height = '320')
{	
	//folder path setup
		$target_path = $target_md;
		//$thumb_path = $target_xs;
		$thumb_path1 = $target_sm;
		
		//file name setup
		$filename_err = explode(".",$_FILES[$field_name]['name']);
		$filename_err_count = count($filename_err);
		$file_ext = $filename_err[$filename_err_count-1];
		$file_ext=end($filename_err);
		if($file_name != '')
		{
			$fileName = $file_name.'.'.$file_ext;
		}
		else
		{
			$fileName = $_FILES[$field_name]['name'];
			$fileName = preg_replace("/[^a-zA-Z0-9.]/", "", $fileName);
			//$fileName=$fileName."_".date("Ymdhis");
			$fileName1=explode(".",$fileName);
			$file_ext1=end($fileName1);
			$fileName=$fileName1[0]."_".date("Ymdhis").".".$file_ext1;
		}
		
		//upload image path
		$upload_image = $target_path.basename($fileName);
		
		//upload image
		if(move_uploaded_file($_FILES[$field_name]['tmp_name'],$upload_image))
		{
			//thumbnail creation
			if($thumb == TRUE)
			{
				//$thumbnail = $thumb_path.$fileName;
				$thumbnail1 = $thumb_path1.$fileName;
				list($width,$height) = getimagesize($upload_image);
				//$thumb_create = imagecreatetruecolor($xs_width,$xs_height);
				$thumb_create1 = imagecreatetruecolor($sm_width,$sm_height);
				switch($file_ext){
					case 'jpg':
						$source = imagecreatefromjpeg($upload_image);
						break;
					case 'jpeg':
						$source = imagecreatefromjpeg($upload_image);
						break;
					case 'png':
						$source = imagecreatefrompng($upload_image);
						break;
					case 'gif':
						$source = imagecreatefromgif($upload_image);
						break;
					default:
						$source = imagecreatefromjpeg($upload_image);
				}
				//imagecopyresized($thumb_create,$source,0,0,0,0,$xs_width,$xs_height,$width,$height);
				imagecopyresized($thumb_create1,$source,0,0,0,0,$sm_width,$sm_height,$width,$height);
				switch($file_ext){
					case 'jpg' || 'jpeg':
						//imagejpeg($thumb_create,$thumbnail,100);
						imagejpeg($thumb_create1,$thumbnail1,100);
						break;
					case 'png':
						//imagepng($thumb_create,$thumbnail,100);
						imagepng($thumb_create1,$thumbnail1,100);
						break;
					case 'gif':
						//imagegif($thumb_create,$thumbnail,100);
						imagegif($thumb_create1,$thumbnail1,100);
						break;
					default:
						//imagejpeg($thumb_create,$thumbnail,100);
						imagejpeg($thumb_create1,$thumbnail1,100);
				}
			}

			return $fileName;
		}
		else
		{
			return false;
		}
}



function CallAPI($method, $url, $data = false)
{   
	$curl = curl_init();
	switch ($method)
	{   
	  case "POST":
		  curl_setopt($curl, CURLOPT_POST, 1);
		  if ($data)
			  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		  break;
	  case "PUT":
		  curl_setopt($curl, CURLOPT_PUT, 1);
		  break;
	  default:
		  if ($data)
			  $url = sprintf("%s?%s", $url, http_build_query($data));
	}
	//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',  'Authorization: Basic NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj'));
	curl_setopt($curl, CURLOPT_HTTPHEADER, array());
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_HEADER, FALSE);
	curl_setopt($curl, CURLOPT_USERPWD, "");
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

function getDataByColumn($tableName='',$column='',$columnValue='')
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from($tableName);
	$CI->db->where($column , $columnValue);
	$query = $CI->db->get();
	return $result = $query->row();
}

function getProfile($id)
{  
	$CI = get_instance();
	$CI->db->select('*');
	$CI->db->from('users');
	$CI->db->where('user_id' , $id);
	$query = $CI->db->get();
	return $result = $query->row();
}

function getAllDataByTable($tableName='',$selcolumnValue='*',$columnName='',$columnValue='')
{  
	$CI = get_instance();
	if($columnName!=''){
		$CI->db->select($selcolumnValue);
		$CI->db->from($tableName);
		$CI->db->where($columnName , $columnValue);
		$query = $CI->db->get();
		if($query->num_rows() > 0) {
		   $catlog_data = $query->result();
			
			return $catlog_data;
		}else {return false;}
	}else{
		$CI->db->select($selcolumnValue);
		$CI->db->from($tableName);
		$query = $CI->db->get();
		if($query->num_rows() > 0) {
		   $catlog_data = $query->result();
			
			return $catlog_data;
		}else {return false;}
	}
	
}
	
function addEditRowByTable($tableName='',$params=array(),$idName='',$id='',$matchColum='',$matchvalue='')
{  
	$CI = get_instance();
	if($id==""){
		if($matchColum!=''){
			$CI->db->where($matchColum, $matchvalue);
			$isexist=$CI->db->get($tableName)->row();
			if($isexist){
				$CI->db->where($matchColum, $matchvalue);
				$CI->db->update($tableName, $params);
			}else{
				$CI->db->insert($tableName, $params);
			}
		}else{
			$CI->db->insert($tableName, $params);
		}
		
	}else{
		$CI->db->where($idName, $id);
		$CI->db->update($tableName, $params);
	}
}

function sendPushNotification($type,$value=array(),$option=array())
{
	$content = array("en" => "KPMG new message.");
	$data= array("type" => "kpmg");
	
	switch ($type) {
		case "event":
			$content = array(
				"en" => "New event."
			);
			$data= array("type" => "event",'id'=>$option['id']);
			break;
		case "news":
			$content = array(
				"en" => "New news."
			);
			$data= array("type" => "news",'id'=>$option['id']);
			break;
		
		default:
			$content = array("en" => "KPMG new message.");
			$data= array("type" => "kpmg");
	} 
	
	$fields = array(
		'app_id' => "f2d65dcb-d815-4965-bd50-a32717f72eb8",
		'include_player_ids' => $value,
		'data' => $data,
		'isAndroid' => true,
		'contents' => $content
	);
	
	$fields = json_encode($fields);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://gamethrive.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
						   'Authorization: Basic NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
	//echo $response;
	return $response;
}

function convert_number_to_words_us($number) {

	$hyphen      = ' ';
	$conjunction = ' ';
	$separator   = ', ';
	$negative    = 'negative ';
	$decimal     = ' and ';
	$dictionary  = array(
		0                   => 'zero',
		1                   => 'one',
		2                   => 'two',
		3                   => 'three',
		4                   => 'four',
		5                   => 'five',
		6                   => 'six',
		7                   => 'seven',
		8                   => 'eight',
		9                   => 'nine',
		10                  => 'ten',
		11                  => 'eleven',
		12                  => 'twelve',
		13                  => 'thirteen',
		14                  => 'fourteen',
		15                  => 'fifteen',
		16                  => 'sixteen',
		17                  => 'seventeen',
		18                  => 'eighteen',
		19                  => 'nineteen',
		20                  => 'twenty',
		30                  => 'thirty',
		40                  => 'fourty',
		50                  => 'fifty',
		60                  => 'sixty',
		70                  => 'seventy',
		80                  => 'eighty',
		90                  => 'ninety',
		100                 => 'hundred',
		1000                => 'thousand',
		1000000             => 'million',
		1000000000          => 'billion',
		1000000000000       => 'trillion',
		1000000000000000    => 'quadrillion',
		1000000000000000000 => 'quintillion'
	);

	if (!is_numeric($number)) {
		return false;
	}

	if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
		// overflow
		trigger_error(
			'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
			E_USER_WARNING
		);
		return false;
	}

	if ($number < 0) {
		return $negative . convert_number_to_words(abs($number));
	}

	$string = $fraction = null;

	if (strpos($number, '.') !== false) {
		list($number, $fraction) = explode('.', $number);
	}

	switch (true) {
		case $number < 21:
			$string = $dictionary[$number];
			break;
		case $number < 100:
			$tens   = ((int) ($number / 10)) * 10;
			$units  = $number % 10;
			$string = $dictionary[$tens];
			if ($units) {
				$string .= $hyphen . $dictionary[$units];
			}
			break;
		case $number < 1000:
			$hundreds  = $number / 100;
			$remainder = $number % 100;
			$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
			if ($remainder) {
				$string .= $conjunction . convert_number_to_words($remainder);
			}
			break;
		case $number < 10000:
			$hundreds  = $number / 1000;
			$remainder = $number % 1000;
			$string = $dictionary[$hundreds] . ' ' . $dictionary[1000];
			if ($remainder) {
				$string .= $conjunction . convert_number_to_words($remainder);
			}
			break;	
		default:
			$baseUnit = pow(1000, floor(log($number, 1000)));
			$numBaseUnits = (int) ($number / $baseUnit);
			$remainder = $number % $baseUnit;
			$string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
			if ($remainder) {
				$string .= $remainder < 100 ? $conjunction : $separator;
				$string .= convert_number_to_words($remainder);
			}
			break;
	}

	if (null !== $fraction && is_numeric($fraction)) {
		$string .= $decimal;
		$words = array();
		foreach (str_split((string) $fraction) as $number) {
			$words[] = $dictionary[$number];
		}
		$string .= implode(' ', $words);
	}

	return $string;
}

function convert_number_to_words($number) {
    $pointvalue = explode(".",$number);
		if(count($pointvalue)==2){
			$no = floor($number);
			$point = round($number - $no, 2) * 100;
			return convert_number_gen($no)." Rupees And ".convert_number_gen($point)." Paise";
		}else{
			return convert_number_gen($number);
		}
}

function convert_number_gen($number) {
		$pointvalue = explode(".",$number);
		if(count($pointvalue)==2){
			$no = floor($number);
			$point = round($number - $no, 2) * 100;
		}
		$tera = floor($number / 10000000);
		
		// Crore (tera)
		// $number -= $tera * 10000000;
		// $giga = floor($number / 1000000);
		
        // Millions (mega)
		$number -= $tera * 10000000;
		$mega = floor($number / 100000);
				
        // Lakhs (Mega)
        $number -= $mega * 100000;
		$kilo = floor($number / 1000);
		
        // Thousands (kilo)
        $number -= $kilo * 1000;
		$hecto = floor($number / 100);
		
        // Hundreds (hecto)
        $number -= $hecto * 100;
		$deca = floor($number / 10);
		
        // Tens (deca)
        $n = $number % 10;
        // Ones
        $result = "";
        if ($tera) {
			$result .= convert_number_gen($tera) .  " Crore";
        }
        // if ($giga) {
			// $result .= (empty($result) ? "" : " ") .convert_number_gen($giga) . " Million";
        // }
        if ($mega) {
            $result .= (empty($result) ? "" : " ") .convert_number_gen($mega) . " Lakhs";
        }
        if ($kilo) {
            $result .= (empty($result) ? "" : " ") .convert_number_gen($kilo) . " Thousand";
        }
        if ($hecto) {
            $result .= (empty($result) ? "" : " ") .convert_number_gen($hecto) . " Hundred";
        }
        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", "Seventy", "Eigthy", "Ninety");
        if ($deca || $n) {
            if (!empty($result)) {
                $result .= " and ";
            }
            if ($deca < 2) {
                $result .= $ones[$deca * 10 + $n];
            } else {
                $result .= $tens[$deca];
                if ($n) {
                    $result .= " " . $ones[$n];
                }
            }
        }
        if (empty($result)) {
            $result = "zero";
        }
		
		return $result;
    }
?>
