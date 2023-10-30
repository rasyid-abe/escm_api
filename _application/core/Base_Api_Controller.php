<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';


class Base_Api_Controller extends REST_Controller {

    /**
     *  load Consts class, checkToken here
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('consts');
        
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }

    public function app_response($code = 200, $message = null, $data = array())
    {
        $response = array('msg' => $message);

        if (!empty($data)){
            $response['data'] = (object)$data;
        }   

        $this->response($response, $code);
    }

    public function app_error($code = 400, $data = array())
    {
        $this->response($data, $code);
    }

    public function app_print($data, $die = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($die)
            die();
    }

    /**
     * [createResponse]
     * @param  [type] $status [description]
     * @param  array  $data   [description]
     * @return [type]         [description]
     */
    public function createResponse($status = REST_Controller::HTTP_OK, $data = array()) {
        $this->set_response(array(
            'status' => $status,
            'msg' => Consts::$msgByCode[$status],
            'data' => $data
                ), $status);
    }

    /**
     * [unauthorized description]
     * @return [type] [description]
     */
    public function unauthorized() {

        header("Content-Type: application/json");
        header("HTTP/1.1 401 Unauthorized");
        header("WWW-Authenticate: Bearer realm=\"app\"");

        echo json_encode(array(
            "status" => REST_Controller::HTTP_UNAUTHORIZED,
            "msg" => Consts::$msgByCode[REST_Controller::HTTP_UNAUTHORIZED],
        ));

        exit;
    }

    public function error($httpCode, $msg = "Error", $data = array()) {

        header("Content-Type: application/json");
        header("HTTP/1.1 " . $httpCode . " " . Consts::$msgByCode[$httpCode]);

        if(empty($data)){
            echo json_encode(array(
                "status" => $httpCode,
                "msg" => $msg
            ));
        }else{
            echo json_encode(array(
                "status" => $httpCode,
                "msg" => $msg,
                "data" => $data,
            ));
        }

        exit;
    }

    /**
     * [getToken description]
     * @return [type] [description]
     */
    public function getHeaderToken() {

        $pattern = "/(^|&)(?P<token>[a-z0-9]+\\+[a-z0-9]+)/";

        $token = $this->input->get_request_header(Consts::ARG_TOKEN);

        if (preg_match($pattern, $token, $result)) {

            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * [getUser]
     * @return [int] [user]
     */
    public function getUser() {

        $token = $this->getHeaderToken();

        if (!$token)
            $this->unauthorized();

        $user = $this->getTokenUser($token);

        if (empty($user)) {
            
            $this->unauthorized();
        } else {
            $userId = $user['user_auth_user_id'];
            $expiredTime = $user['user_auth_expired_datetime'];
            $expired = $this->checkTokenExpired($userId, $expiredTime);
            
            if ($expired) {
                $this->error(REST_Controller::HTTP_UNAUTHORIZED, "Token Expired");
            }
        }
            
        return $user;
    }
    
    public function getTokenUser($token) {

        $userArr = array();

        $sql = "
            SELECT 
            user_auth_user_id,
            user_auth_group_id,
            user_auth_branch_id,
            user_auth_type,
            user_auth_name,
            user_auth_username,
            user_auth_expired_datetime,
            user_auth_token
            FROM user_auth
            WHERE user_auth_token = '" . $token . "'
        ";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $userArr = $query->row_array();
        }

        return $userArr;
    }

    /**
     * [generateToken description]
     * @param  [type] $userEmail [description]
     * @param  [type] $userIp    [description]
     * @return [type]            [description]
     */
    public function generateToken($userEmail, $userIp) {
        return sha1($userEmail . microtime()) . '+' . sha1(Consts::APP_NAME . microtime() . $userIp);
    }
    
    /**
     * [checkTokenExpired description]
     * @param  [type] $expiredTime [description]
     * @return [type]            [description]
     */
    private function checkTokenExpired($userId, $expiredTime) {

        $now = time();
        if ($now > $expiredTime) {
            
            $this->db->where('user_auth_user_id', $userId);
            $this->db->delete('user_auth');
            
            return true;
        } else {
            
            $extraTime = strtotime(TOKEN_TIMEOUT, $expiredTime);
            
            $update = array(
                'user_auth_expired_datetime' => $extraTime
            );
            
            $this->db->where('user_auth_user_id', $userId);
            $this->db->update('user_auth', $update);
            
            return false;
        }
    }
	
	
	public function getTokenUserJWT($token) {

        $userArr = array();

        $sql = "
            SELECT 
            *
            FROM dbo.core_user_account
            WHERE user_account_token like '" . $token . "'
        ";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $userArr = $query->row_array();
        }

        return $userArr;
    }
	
	private function checkExpired($expiredTime) {

        $now = time();
        if ($now > $expiredTime) {        
            return true;
        } else {
            
            return false;
        }
    }
	
	public function cektoken(){
		
        $jwt = $this->input->get_request_header('Authorization');
        try {
			
			$user = $this->getTokenUserJWT($jwt);

			if (empty($user)) {
				
				$this->unauthorized();
			} 
			else {
				$decode = $this->jwt->decode($jwt,config_item('jwt_key'));
				$expired = $this->checkExpired($decode->token_expired);
				
				if ($expired) {
					$this->error(REST_Controller::HTTP_UNAUTHORIZED, "Token Expired");
				}
				return $decode;
			}
			
        } catch (Exception $e) {
            $this->unauthorized();
        }
    }
	
	public function cektokenrefresh(){
		
        $jwt = $this->input->get_request_header('Authorization');
        try {
			
			$user = $this->getTokenUserJWT($jwt);

			if (empty($user)) {
				
				$this->unauthorized();
			} 
			else {
				$decode = $this->jwt->decode($jwt,config_item('jwt_key'));
				return $decode;
			}
			
        } catch (Exception $e) {
            $this->unauthorized();
        }
    }
}
