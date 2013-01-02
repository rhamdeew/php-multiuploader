<?php
/*
* Возвращает путь для сохранения файла
*/
function getpath($uploadDir = "img/") {

	$path = $uploadDir.date("Y")."/".date("m")."/";
	if(!file_exists($path))	mkdir($path,0755,TRUE);
	return $path;

}

//Собственно старт

$uploadDir = 'img/'; //папка для хранения файлов
$miniuploadDir = 'img/mini/'; //папка для хранения файлов
$dir = ''; //базовый путь к скрипту
$mwidth = 500;

$allowedExt = array('jpg', 'jpeg', 'png', 'gif');
$maxFileSize = 10 * 1024 * 1024; //10 MB

$uploadDir = getpath($uploadDir);
$miniuploadDir = getpath($miniuploadDir);

//если получен файл
//старый код, новый пишем чуть
if (isset($_FILES)) {
    //проверяем размер и тип файла
    $ext = end(explode('.', strtolower($_FILES['user_file']['name'][0])));
    if (!in_array($ext, $allowedExt)) {
        return;
    }
    if ($maxFileSize < $_FILES['user_file']['size'][0]) {
        return;
    }
    if (is_uploaded_file($_FILES['user_file']['tmp_name'][0])) {
	//Магия с созданием уникального имени. Начало
	    $fileName = $uploadDir.$_FILES['user_file']['name'][0];
		$nameParts = explode('.', $_FILES['user_file']['name'][0]);
        $nameParts[count($nameParts)-2] =substr(md5(time()),7);
        $fileName = $uploadDir.implode('.', $nameParts);
		//если файл с таким именем уже существует... Невероятное! =)
        while(file_exists($fileName)) {
            //...добавляем текущее время к имени файла
            $nameParts = explode('.', $_FILES['user_file']['name'][0]);
            $nameParts[count($nameParts)-2] =substr(md5(time()),7);
            $fileName = $uploadDir.implode('.', $nameParts);
        }
		//Генерим путь к mini
		$dirParts=explode('/',$fileName);
		$dirParts[0].="/mini";
		$fileName2 = implode('/',$dirParts);
	//Магия с созданием уникального имени. Конец
        move_uploaded_file($_FILES['user_file']['tmp_name'][0], $fileName);
		//Костыль ресайза картинок до width:500px; начало
		// Get new sizes
		list($width, $height, $type, $attr) = getimagesize($fileName);
		if($width>500) {
		$newwidth=500;
		$k=$newwidth/$width;
		$newheight = $height * $k;
		// Load
		$thumb = imagecreatetruecolor($newwidth, $newheight);
		if($type==3) $source = imagecreatefrompng($fileName);
		elseif($type==1) $source = imagecreatefromgif($fileName);
		elseif($type==2) $source = imagecreatefromjpeg($fileName);
		else {
			// $error = array("success" => "false");
			// die(json_encode($error));
			die("IO error");
		}
	
		// Resize
		imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		if($type==3) $source = imagepng($thumb, $fileName2);
		elseif($type==1) $source = imagegif($thumb, $fileName2);
		elseif($type==2) $source = imagejpeg($thumb, $fileName2);
		else {
			// $error = array("success" => "false");
			// die(json_encode($error));
			die("IO error");
		}

		echo "http://".$_SERVER["HTTP_HOST"]."/".$dir.$fileName."|http://".$_SERVER["HTTP_HOST"]."/".$dir.$fileName2;
		}
		//Костыль ресайза картинок до width:500px; конец
		else {

		echo $dir.$fileName;			
		}
    }
}
else {
	echo "<pre>";
	print_r($_FILES);
	echo "</pre>";
}