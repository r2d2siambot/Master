<?php 
/**
* Create By Peerapat Matheang
* Facebook : Peerapat Matheang
* Line : progame69
*/
class Database
{
	public $db;

	public function __construct()
	{
		$this->db = new mysqli('localhost','root','pass','dbname');
	}

}

?>