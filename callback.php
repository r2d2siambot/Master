<?php
	require 'yyc.php';

	$content = file_get_contents('php://input');
	$arrJson = json_decode($content, true);

	$strAccessToken = "5CML08NTRWlCEnzEIqn8cqQNFe2HxU0fGEMBJc1h+FrnYviWL3VX0+ok3aMekTu+uichV3Zk2QFh/WoH0LT30A0y8Gh6cDPKbbSFPWYKn/Kd66yorK3nEm1Vf9ZvLn0j8oPnJIoc6MN61B2/qSKoOwdB04t89/1O/w1cDnyilFU=";
	$yyc = new Yyc($strAccessToken);
	$yyc->ask($arrJson);
	$yyc->reply();
	 
?>