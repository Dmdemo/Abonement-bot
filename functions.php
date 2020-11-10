<?php

//выкл ошибки
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

//вкл ошибки
//ini_set('error_reporting', E_ALL & ~E_NOTICE);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

//создаем сооединение с бд
$dbhost='localhost';
$dbuser='XXXXXXXXXXX';
$dbpass='XXXXXXXXXXX';
$dbname='XXXXXXXXXXX';        
$link = mysqli_connect( $dbhost, $dbuser, $dbpass, $dbname );
if ( ! $link ) {
   echo "Ошибка: Невозможно установить соединение с MySQL.";
   echo "Код ошибки errno: ".mysqli_connect_errno( );
   echo "Текст ошибки error: ".mysqli_connect_error( );
 }
$link->set_charset("utf8");

// $result = $link->query('SELECT * FROM `user` WHERE id_prepoda=1');
// echo '<pre>'; 
// print_r ($result);
// echo '</pre><br>';
 
 //printf("Изначальная кодировка: %s\n", $mysqli->character_set_name());
 //$mysqli->set_charset("utf8");
// $actor = $result->fetch_assoc();
// echo $actor['name'];
 
 function CheckUser ($link,$chat_id)
  {
    $result = $link->query("SELECT * FROM `user` WHERE mychat_id='$chat_id'");
    $row=$result->fetch_row();
    if ($row) {/*echo $row[1];*/ return true;}        
     else {/*echo '<br> no';*/ return false;}
   // echo '<pre>'; 
   // print_r ($row);
   //echo '</pre><br>';
  }
 
 function AddUser ($link,$chat_id,$mess)
  {
    $result = $link->query("SELECT * FROM `user` WHERE tel = '$mess'");
    $row=$result->fetch_row(); 
     
    if ($row) 
      {
       $result = $link->query("UPDATE `user` SET `mychat_id`='$chat_id' WHERE tel = '$mess'");
       //$row=$result->fetch_row();
       if ($result) {/*echo $result;*/ return true;}        
       else {/*echo '<br> no';*/ return false;}
       // echo '<pre>'; 
       // print_r ($row);
      // echo '</pre><br>'; 
      }
    else { return false;}      
  }
 
 function ShowAb ($link, $id_client, $date1, $date2)
  {
     //даты из таблицы users
     $result = $link->query("SELECT * FROM `visits` WHERE id_client = '$id_client' and (poseshenie >= '$date1' AND poseshenie <= '$date2')");
     //сохраним строки в двумерный массив
      $arr = [];
      $i=0;
      while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC) )
        {
       // $arr[$i] = $row['poseshenie'];
        $arr[$i] = array( $row['poseshenie'], $row['status']);
        $i++;
         }      
        var_dump($arr);
        return $arr;   
  }
 
 function ShowAbon ($link, $tel, $path)
  {
   //из таблицы  users
     $result = $link->query("SELECT * FROM `user` WHERE tel = '$tel'");
     $row=$result->fetch_assoc(); 
   
    if ($row)
      {  
        //рисуем Имя
        $img = $path; // Ссылка на файл
	$font = "./ArialMT.ttf"; // Ссылка на шрифт
	$font_size = 22; // Размер шрифта
	$degree = 0; // Угол поворота текста в градусах
	$text = $row[name]; // Ваш текст
	$y = 350; // Смещение сверху (координата y)
	$x = 600; // Смещение слева (координата x)
	$pic = imagecreatefrompng($img); // Функция создания изображения
	$color = imagecolorallocate($pic, 0, 0, 0); // Функция выделения цвета для текста
	
	imagettftext($pic, $font_size, $degree, $x, $y, $color, $font, $text); // Функция нанесения текста
	//$path= $tel.".jpg";
        $path= 'temp_ab/'.$tel.'.jpg';
        imagepng($pic, $path); // Сохранение рисунка
	imagedestroy($pic); // Освобождение памяти и закрытие рисунка
        
         //рисуем кол-во оставшихся визитов
        $img = $path; // Ссылка на файл
	$font = "./ArialMT.ttf"; // Ссылка на шрифт
	$font_size = 22; // Размер шрифта
	$degree = 0; // Угол поворота текста в градусах
	$text = 'осталось - '.$row[countAbvisit]; // Ваш текст
	$y = 450; // Смещение сверху (координата y)
	$x = 820; // Смещение слева (координата x)
	$pic = imagecreatefrompng($img); // Функция создания изображения
	$color = imagecolorallocate($pic, 0, 0, 0); // Функция выделения цвета для текста
	
	imagettftext($pic, $font_size, $degree, $x, $y, $color, $font, $text); // Функция нанесения текста
	//$path= $tel.".jpg";
        $path= 'temp_ab/'.$tel.'.jpg';
        imagepng($pic, $path); // Сохранение рисунка
	imagedestroy($pic); // Освобождение памяти и закрытие рисунка
        
        //рисуем дату
        $img = $path; // Ссылка на файл
	//$font = "./ArialMT.ttf"; // Ссылка на шрифт
	//$font_size = 22; // Размер шрифта
	//$degree = 0; // Угол поворота текста в градусах
          $date1 = new DateTime($row[abonement_s]);
          $date1 = $date1->format('d/m/Y'); 
          
          $date2 = new DateTime($row[abonement_do]);
          $date2 = $date2->format('d/m/Y'); 
	$text = $date1.' - '.$date2; // Ваш текст
	$y = 195; // Смещение сверху (координата y)
	$x = 670; // Смещение слева (координата x)
	$pic = imagecreatefrompng($img); // Функция создания изображения
	//$color = imagecolorallocate($pic, 0, 0, 0); // Функция выделения цвета для текста
	
	imagettftext($pic, $font_size, $degree, $x, $y, $color, $font, $text); // Функция нанесения текста
	$path= 'temp_ab/'.$tel.'.jpg';
        imagepng($pic, $path); // Сохранение рисунка
	imagedestroy($pic); // Освобождение памяти и закрытие рисунка
        
        //рисуем время посещения
        $img = $path; // Ссылка на файл
        $text = $row[timevisit]; // Ваш текст
	$y = 270; // Смещение сверху (координата y)
	$x = 670; // Смещение слева (координата x)
	$pic = imagecreatefrompng($img); // Функция создания изображения
	//$color = imagecolorallocate($pic, 0, 0, 0); // Функция выделения цвета для текста
	
	imagettftext($pic, $font_size, $degree, $x, $y, $color, $font, $text); // Функция нанесения текста
	$pathend='temp_ab/'.uniqid().'.jpg';
        //$pathend=require __DIR__ .'/temp_ab/'.uniqid().'.jpg';
        // $pathend= uniqid().".jpg"; 
        imagepng($pic, $pathend); // Сохранение рисунка
	imagedestroy($pic); // Освобождение памяти и закрытие рисунка
     
        
        // echo $row[name];
        // echo $row[abonement_s];
        //  echo $row[abonement_do]; 
        //  echo $row[timevisit]; 
    }
        return $pathend;
 }
 
 function DrawAb ($array, $tel)
  {
     $path="ab2020.png";
     $i=0;
     sort($array);
     
    foreach ($array as $value)
      {
        $date = new DateTime($value[0]);
        $date = $date->format('d/m');    
         // echo $date;
    
        $img = $path; // Ссылка на файл
	$font = "./ArialMT.ttf"; // Ссылка на шрифт
	$font_size = 22; // Размер шрифта
	$degree = 75; // Угол поворота текста в градусах
	$text = $date; // Ваш текст
	$y = 510; // Смещение сверху (координата y)
	$x = 90+$i; // Смещение слева (координата x)
	$pic = imagecreatefrompng($img); // Функция создания изображения
	
        if ($value[1]=='1')
        {$color = imagecolorallocate($pic, 240, 13, 74); }// Функция выделения цвета для текста
	else {$color = imagecolorallocate($pic, 0, 0, 0); }
            
	imagettftext($pic, $font_size, $degree, $x, $y, $color, $font, $text); // Функция нанесения текста
	$path= 'temp_ab/'.$tel.'.jpg';
        //$path= $tel.".jpg";
        imagepng($pic, $path); // Сохранение рисунка
	imagedestroy($pic); // Освобождение памяти и закрытие рисунка
        $i=$i+80;
       } 
       return $path;
 }
 
 
function getTel ($link,$chat_id)
  {
  
    $result = $link->query("SELECT * FROM `user` WHERE mychat_id = '$chat_id'");
    $row=$result->fetch_assoc(); 
    $res = $row[tel];
    //echo $res;
    return $res;
   }
 
function getName ($link,$chat_id)
  {
  
    $result = $link->query("SELECT * FROM `user` WHERE mychat_id = '$chat_id'");
    $row=$result->fetch_assoc(); 
    $res = $row[name];
    //echo $res;
    return $res;
  }
 
function getDate1 ($link,$chat_id) //вернуть дату юзеру при просмотре абонемента
  {
    $result = $link->query("SELECT * FROM `user` WHERE mychat_id = '$chat_id'");
    $row=$result->fetch_assoc(); 
    $res = $row[abonement_s];
    //echo $res;
    return $res;
  }
 
function getDate2 ($link,$chat_id)
  {
    $result = $link->query("SELECT * FROM `user` WHERE mychat_id = '$chat_id'");
    $row=$result->fetch_assoc(); 
    $res = $row[abonement_do];
    //echo $res;
    return $res;
  }
  
function getDate1fromTel ($link,$tel) //вернуть дату админу при просмотре абонемента
  {
    $result = $link->query("SELECT * FROM `user` WHERE tel = '$tel'");
    $row=$result->fetch_assoc(); 
    $res = $row[abonement_s];
    //echo $res;
    return $res;
  }
 
function getDate2fromTel ($link,$tel) //вернуть дату админу при просмотре абонемента
  {
    $result = $link->query("SELECT * FROM `user` WHERE tel = '$tel'");
    $row=$result->fetch_assoc(); 
    $res = $row[abonement_do];
    //echo $res;
    return $res;
  }

 
// $a='157612765';
// $b=getTel($link,$a);
// echo $b;
 
//$aid='9215358033';
//$date1='2020.01.01'; 
//$date2='2020.12.11';
//$arr= ShowAb ($link, $aid, $date1, $date2);
//echo '<pre>';
//print_r ($arr[0][1]);
////echo '</pre>';

//foreach ($arr as $value)
   //   {
        //$date = new DateTime($value);
       // $date = $date->format('d/m');    
         // echo $date;
     //    echo '<pre>';
     //    print_r ($value);
     //    echo '</pre>';
         
     //    echo $value[0];//даты
     //    echo $value[0];// если 1 то пропуск занятия
     // }
//$tel='9115006431';
//$path='temp_ab/9115006431.jpg';
//$path=DrawAb ($arr,$tel);

//ShowAbon ($link, $tel, $path);
//
//var_dump($arr);
 //$mes='9115006431';
 //if (AddUser ($link,$a,$mes)) {echo 'true';}
 //else {echo 'false';}
 
