<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://dev.unanth.com');
require "./db.php";
extract($_POST);
$course = str_replace(' ', '_', strtolower($course));
$dir= $user."/".$course;
$filename = $dir."/".$name;
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}
if(file_exists($filename)){
	echo json_encode(['error'=>'duplicate','message'=>" File ".$name.' is already exist.']);
	die;
}
// $query = 'cd /var/www/html/drive && wget --header "Authorization: Bearer '.$authkey.'" --content-disposition https://drive.google.com/a/unanth.com/uc?id='.$url.'&export=download -o "'.$name.'"';

$query = 'curl --header "Authorization: Bearer '.$authkey.'" https://www.googleapis.com/drive/v3/files/'.$url.'?alt=media -o "'.$filename.'"';

system($query,$output);
if($output == 0){
	if(file_exists($filename)){
		$conn = new DB;
		$q = $conn->insert('bulk_files',[
			'user'=>$user,
			'file_name'=>$name,
			'course'=>$course
		]);
		echo json_encode($q);
	}else{
		echo json_encode(['error'=>'api','message'=>$name.' can not copy from google drive.']);
	}
};