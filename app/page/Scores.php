<?php
/**
 * User: loveyu
 * Date: 2015/1/14
 * Time: 17:55
 */

namespace UView;


use ULib\Page;

class Scores extends Page{
	public function my_list(){
		$access = access_class();
		if(login_class()->is_login() && login_class()->getLoginType() == "student" && !$access->read("my_curriculum")){
			$this->permission_deny();
			return;
		}
		$this->__view("scores/my_list.php");
	}

	public function get_ajax(){
		$access = access_class();
		if(login_class()->is_login() && login_class()->getLoginType() == "student" && !$access->read("my_curriculum")){
			$this->permission_deny();
			return;
		}
		header("Content-Type: application/json; charset=utf-8");
		$mc_grade = $this->__req->post('mc_grade');
		$mc_number = $this->__req->post('mc_number');
		$info = [];
		if(!empty($mc_number)){
			$info['mc_number'] = $mc_number;
		}
		if(!empty($mc_grade)){
			$info['mc_grade'] = $mc_grade;
		}
		$list = db_class()->student_scores(login_class()->uid(), $info);
		if($list !== false){
			echo json_encode([
				'status' => true,
				'msg' => $list
			]);
		} else{
			echo json_encode([
				'status' => false,
				'msg' => '查询失败'
			]);
		}
	}

	public function add(){
		$access = access_class();
		if(!$access->write("scores_add")){
			$this->permission_deny();
			return;
		}
		$this->__view("scores/add.php");
	}

	public function add_ajax(){
		$access = access_class();
		if(!$access->write("scores_add")){
			$this->permission_deny();
			return;
		}
		header("Content-Type: application/json; charset=utf-8");
		$id_id = $this->__req->post('id_id');
		$icl_id = $this->__req->post('icl_id');
		$mc_id = $this->__req->post('mc_id');
		$rt = [
			'msg' => NULL,
			'status' => false
		];
		if(!is_numeric($id_id) || !is_numeric($mc_id)){
			$rt['msg'] = '专业字段或专业课程课号为空';
		} else{
			$db = db_class();
			if(!$db->check_mg_id_exists($id_id, $mc_id)){
				$rt['msg'] = "专业与课程不匹配";
			} else{
				if(!empty($icl_id)){
					if(!$db->check_class_exists($id_id, $icl_id)){
						$rt['msg'] = "无法找到对应的班级";
					} else{
						$rt['msg'] = $db->insert_mc_id_class_list($id_id, $mc_id, $icl_id);
						if($rt['msg'] === false){
							$rt['msg'] = "插入失败，检测重复或异常";
						} else{
							$rt['status'] = true;
						}
					}
				} else{
					$rt['msg'] = $db->insert_mc_id_list($id_id, $mc_id);
					if($rt['msg'] === false){
						$rt['msg'] = "插入失败，检测重复或异常";
					} else{
						$rt['status'] = true;
					}
				}
			}
		}
		echo json_encode($rt);
	}
}