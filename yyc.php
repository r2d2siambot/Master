<?php 
/**
* Create By Peerapat Matheang
* Facebook : Peerapat Matheang
* Line : progame69
*/
require 'database.php';
class Yyc extends Database
{
	private $ask;
	private $reply;
	private $access_token;
	private $arrJson;

	public function __construct($access_token='')
	{
		parent::__construct();
		$this->ask = null;
		$this->reply['id'] = 0;
		$this->reply['msg'] = "ฉันไม่เข้าใจคำสั่ง";
		$this->access_token = $access_token;
	}

	public function ask($arrJson=array())
	{
		$this->arrJson = $arrJson;
		$this->ask = trim($arrJson['events'][0]['message']['text']);
		$cmdAsk = explode('/?', $this->ask);
		$cmdReply = explode('/=', $this->ask);
		if(count($cmdAsk) > 1 && count($cmdReply) > 1){
			$tmp = explode('/=', $cmdAsk[1]);
			$strAsk = $this->db->escape_string(trim($tmp[0]));
			$strReply = $this->db->escape_string(trim($cmdReply[1]));
			$this->learning($strAsk,$strReply);
			return true;
		}
		$rs = $this->db->query('SELECT * FROM `bl_ai` WHERE `ai_ask` LIKE "%'.$this->db->escape_string($this->ask).'%" ORDER BY RAND() LIMIT 1');
		if($rs->num_rows > 0){
			$obj = $rs->fetch_object();
			$this->reply['id'] = $obj->ai_id;
			$this->reply['msg'] = $obj->ai_reply;
		}
		
	}

	public function reply()
	{
		$this->db->query('INSERT INTO `bl_logs`(`logs_userid`, `logs_aiid`, `logs_datetime`) VALUES ("'.$this->arrJson['events'][0]['source']['userId'].'","'.$this->reply['id'].'","'.date('Y-m-d H:i:s',time()).'")');

		$url = "https://api.line.me/v2/bot/message/reply";
		$arrHeader = array();
		$arrHeader[] = "Content-Type: application/json";
		$arrHeader[] = "Authorization: Bearer ".$this->access_token;
		$arrPostData = array();
		$arrPostData['replyToken'] = $this->arrJson['events'][0]['replyToken'];
		$arrPostData['messages'][0]['type'] = "text";
		$arrPostData['messages'][0]['text'] = $this->reply['msg'];
		$res = $this->curl($url,$arrHeader,json_encode($arrPostData),true);
		if($this->reply['id'] == 0 || $this->reply['id'] == -2) $this->push();
	}

	public function push()
	{
		$url = "https://api.line.me/v2/bot/message/push";
		$arrHeader = array();
		$arrHeader[] = "Content-Type: application/json";
		$arrHeader[] = "Authorization: Bearer ".$this->access_token;
		$arrPostData = array();
		$arrPostData['to'] = $this->arrJson['events'][0]['source']['userId'];
		$arrPostData['messages'][0]['type'] = "text";
		$str = "คุณสามารถสอนบอทได้\n";
		$str .= "เพียงทำตาม 3 ขั้นตอนดังนี้\n";
		$str .= "1.พิมพ์ /?แล้วตามด้วยคำถาม\n";
		$str .= "2.พิมพ์ต่อ /=แล้วตามด้วยคำตอบ\n";
		$str .= "3.ตรวจสอบความถูกต้องแล้วกดส่ง\n";
		$str .= "== ตัวอย่าง ==\n";
		$str .= "/?ชื่ออะไร?=เราชื่อ Botline\n";
		$str .= "=============\n";
		$str .= "เพียงเท่านี้บอทก็สามารถจดจำคำสอนได้แล้ว";
		$arrPostData['messages'][0]['text'] = $str;
		$res = $this->curl($url,$arrHeader,json_encode($arrPostData),true);
	}

	public function learning($ask='',$reply='')
	{
		$rs = $this->db->query('SELECT * FROM `bl_ai` WHERE `ai_ask` LIKE "'.$ask.'" AND `ai_reply` LIKE "'.$reply.'" LIMIT 1');
		if($rs->num_rows > 0){
			$this->reply['id'] = -3;
			$this->reply['msg'] = "การสอนนี้มีอยู่ในระบบอยู่แล้ว";
			return true;
		}

		$rs = $this->db->query('INSERT INTO `bl_ai`(`ai_ask`, `ai_reply`, `ai_userid`, `ai_datetime`) VALUES ("'.$ask.'","'.$reply.'","'.$this->arrJson['events'][0]['source']['userId'].'","'.date('Y-m-d H:i:s',time()).'")');
		if($rs){
			$this->reply['id'] = -1;
			$this->reply['msg'] = "ขอบคุณที่สอนเรานะ";
		}else{
			$this->reply['id'] = -2;
			$this->reply['msg'] = "วิธีการสอนบอทไม่ถูกต้อง";
		}
	}

	public function curl($url='',$arrHeader='',$postData='',$type=false)
	{
		$ch = curl_init();
		curl_setopt_array($ch,array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_POST => $type,
			CURLOPT_HTTPHEADER => $arrHeader,
			CURLOPT_POSTFIELDS => $postData,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		));
		$result = curl_exec($ch);
		curl_close ($ch);
		return $result;
	}
	
}
?>