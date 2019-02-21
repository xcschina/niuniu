<?php
/*
 * author: i@doingthing.com
 * author website: http://www.doingthing.com
 */
class validateApi{
	private $username='stacy_wxl';
	private $userpwd='w136k56948gh';
	
	
	public function yundamaApi($imgPath){
		$validate = array('status'=>0,'data'=>'');
		
		$username = $this->username;
		$pwd = $this->userpwd;
		$appid = 4978;
		$appkey = '8b72b16d87aa1c7c9abed28dd13e8e42';
		
		$posts = array('method'=> 'upload', 
		'username'=> $username, 
		'password'=> $pwd, 
		'appid'=> 4978,
		'appkey'=>$appkey, 
		'codetype'=>'1004', 
		'timeout'=>60,
		'file'=>'@'.$imgPath
		);
		
		$apiUrl = 'http://api.yundama.com/api.php';
		$data = $this->http($apiUrl, 'POST',$posts);
		$data = json_decode($data);
		if(is_object($data) && property_exists($data, 'cid')){
			$cid = $data->cid;
			for($i=1;$i>0;){
				$posts = array(
						'method'=>'result', 
						'username'=>$username, 
						'password'=> $pwd, 
						'appid'=>$appid, 
						'appkey'=>$appkey, 
						'cid'=>$cid);
				$data = $this->http($apiUrl, 'POST',$posts);
				$data = json_decode($data);
				
				if(is_object($data) && property_exists($data, 'text') && $data->text){
					$validate['status'] = 1;
					$validate['data'] = $data->text;
					
					break;
				}
			}
		}
		
		
		return $validate;
	}
	
	public function http($url, $method, $postfields = NULL, $headers = array(),$outputHeader=false) {
	
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ci, CURLOPT_TIMEOUT, 60);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
	
		if($outputHeader){
	
			curl_setopt($ci, CURLOPT_HEADER, true);
		}
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
	
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
			case 'GET':
				curl_setopt($ci, CURLOPT_HTTPGET, true);
		}
	
	
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
	
		$response = curl_exec($ci);
	
		curl_close ($ci);
		return $response;
	}
}

/*
 * use Example:
 *
 *
 * $imgpath = 'randcode.png';
 * $api = new validateApi();
 * $data = $api->yundamaApi($imgpath);
 * echo $data['data'];
 */