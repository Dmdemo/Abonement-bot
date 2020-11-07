<?php

require_once ('functions.php');

function checkAdmin($link, $chatid)
  {
    $result = $link->query("SELECT * FROM `prepods` WHERE mychat_id='$chatid'");
    $row=mysqli_fetch_row($result);
    if (is_null($row)) {/*echo $row[1];*/ return false;}        
     else {/*echo '<br> no';*/ return true;}
  }  

function GetUsersFromMyGroup ($link, $n_group)
  {
     //даты из таблицы users
     $date=date('Y-m-d');
     $result = $link->query("SELECT * FROM `user` WHERE group_num = '$n_group' and (abonement_do >= '$date' AND abonement_s <= '$date') ");
  
     //сохраним строки в двумерный массив
     $arrName = [];
     $ArrTel = [];
     $i=0;
       while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC) )
         {
           $arrName[$i] = $row['name'];
           $arrTel[$i] = $row['tel'];
           $i++;
         } 
         $arr=[$arrName,$arrTel];
         //var_dump($arr);
     return $arr;  
    // $arr[0] - имена
    // $arr[1] - телефоны
   }
   
function GetUsersFromMyGroupAll ($link, $n_group)
  {
     //даты из таблицы users
     $date=date('Y-m-d');
     $result = $link->query("SELECT * FROM `user` WHERE group_num = '$n_group' ");
  
     //сохраним строки в двумерный массив
     $arrName = [];
     $ArrTel = [];
     $i=0;
       while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC) )
         {
           $arrName[$i] = $row['name'];
           $arrTel[$i] = $row['tel'];
           $i++;
         } 
         $arr=[$arrName,$arrTel];
         //var_dump($arr);
     return $arr;  
    // $arr[0] - имена
    // $arr[1] - телефоны
   }   

function AddUserVisit($link,$message)
 {
    $date1=date('Y-m-d');
    $res = $link->query("SELECT * FROM `visits` WHERE id_client='$message' and poseshenie='$date1'");
    $row=mysqli_fetch_row($res);
    //   echo $row;
    if (is_null($row)) 
      {  
          $resCountVis = $link->query("SELECT * FROM `user` WHERE tel='$message'");
          $row=$resCountVis->fetch_assoc(); 
          $resCountVisit = $row[countAbvisit];
          
          if ($resCountVisit==1)
            {
             $messageVisit=hex2bin('e29d97').'У вас последнее занятие '.$row[name]; 
             $result = $link->query("INSERT INTO `visits` (`id_client`, `n_abonement`, `poseshenie`) VALUES ($message, '1', '$date1')");
             //добавили визит в таблицу визитов
             $resCountVisit=$resCountVisit-1;
             $resultCV = $link->query("UPDATE `user` SET `countAbvisit`='$resCountVisit' WHERE tel = '$message'");
   
             // return $result;
            }
            elseif ($resCountVisit==0)
            {
              $messageVisit=hex2bin('e29d8c').'У вас закончились занятия '.$row[name];  
            }
            else 
            {
             $vis=$resCountVisit-1; 
             $messageVisit= hex2bin('e29c85').'('."$vis". ')'.'отметили - '.$row[name]; 
             $result = $link->query("INSERT INTO `visits` (`id_client`, `n_abonement`, `poseshenie`) VALUES ($message, '1', '$date1')");
             //добавили визит в таблицу визитов
             $resCountVisit=$resCountVisit-1;
             $resultCV = $link->query("UPDATE `user` SET `countAbvisit`='$resCountVisit' WHERE tel = '$message'");
             }
             return $messageVisit;
        }
 }      
 //отметим тех кто не пришел
 function AddNoVisit($link, $arr)
 {
    
    $date1=date('Y-m-d');
   // echo '<pre>';
   // print_r($arr[1]);
   // echo '</pre>';
    foreach ($arr[1] as $value)
    {
       // echo $value;
        $res = $link->query("SELECT * FROM `visits` WHERE id_client='$value' and poseshenie='$date1'");
        $row=mysqli_fetch_row($res);
       if (is_null($row)) //если еще не отмечен визит будем ставить отсутсвие -1 визит
           { 
             
             $resCountVis = $link->query("SELECT * FROM `user` WHERE tel='$value'");
             $row=$resCountVis->fetch_assoc(); 
             $resCountVisit = $row[countAbvisit];
            
             $vis=$resCountVisit-1; 
             $messageVisit= hex2bin('e29c85').'('."$vis". ')'.'отсутсвие - '.$row[name]; 
             $result = $link->query("INSERT INTO `visits` (`id_client`, `n_abonement`, `poseshenie`,`status`) VALUES ($value, '1', '$date1','1')");
             //добавили визит в таблицу визитов
             $resCountVisit=$resCountVisit-1;
             $resultCV = $link->query("UPDATE `user` SET `countAbvisit`='$resCountVisit' WHERE tel = '$value'");
             }
             
      }
     // return $messageVisit;
   }
      

 function AddNewAb($link,$arrNewAb)
   {
     //$date1=date('Y-m-d'); 
     $date = strtotime("$arrNewAb[2]+27 day");
     $abonemenDo= date('Y-m-d', $date);
     $groupNum=''; 
     $timeVisit=''; 
     $countAbvisit=$arrNewAb[3];
      // echo $arrNewAb[0];
      // echo $arrNewAb[1];
      // echo $arrNewAb[2];
     $result = $link->query("INSERT INTO `user`(`mychat_id`,`name`, `tel`, `group_num`, `abonement_s`, `abonement_do`,`timevisit`,`countAbvisit`) VALUES ('','$arrNewAb[0]','$arrNewAb[1]','$groupNum','$arrNewAb[2]','$abonemenDo','$timeVisit','$countAbvisit' )");
     return $result;
   }
 
function takePrepodGroups($link, $chat_id)
   {
    $result = $link->query("SELECT * FROM `prepods` WHERE mychat_id = '$chat_id'");
    $row=$result->fetch_assoc(); 
    $res = $row[prepod_id];
    // echo $res;
    //return $res;
    $result = $link->query("SELECT * FROM `groups` WHERE prepod_id = '$res'");
    $i=0;    
    while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC) )
         {
        $arrGroupId[$i] = $row['nomer_group'];
        $arrGroupName[$i] = $row['name_group'];
        $i++;
         } 
         $arr=[$arrGroupId,$arrGroupName];
    //var_dump($arr);
     return $arr;  
    // $arr[0] - имена
    // $arr[1] - телефоны
   }
 
function AddGroupForUser ($link, $ngroup, $tel, $chatid)
  {
    $resPrepod = $link->query("SELECT * FROM `prepods` WHERE mychat_id='$chatid'");
    $row=$resPrepod->fetch_assoc(); 
    $PrepodId = $row['prepod_id'];
    //Echo $PrepodId;
   
    $resNameGroup = $link->query("SELECT * FROM `groups` WHERE prepod_id='$PrepodId' and nomer_group='$ngroup'");
    $row=$resNameGroup->fetch_assoc(); 
    $NameGroup = $row['name_group'];
    // Echo $NameGroup;
    $result = $link->query("SELECT * FROM `user` WHERE tel = '$tel'");
    $row=$result->fetch_row(); 
       
    $result = $link->query("UPDATE `user` SET `group_num`='$ngroup',  `timevisit`='$NameGroup' WHERE tel = '$tel'");
    //$row=$result->fetch_row();
    if ($result) {/*echo $result;*/ return true;}        
     else {/*echo '<br> no';*/ return false;}
     // echo '<pre>'; 
     // print_r ($row);
     // echo '</pre><br>'; 
   }

//echo 'd';
 //  $n_group='1';
//$arr1=GetUsersFromMyGroup ($link, $n_group) ;

//AddNoVisit($link, $arr1);
  // echo '<pre>';
  //  print_r($arr1);
  //  echo '</pre>';
//$ngroup='1';
  //$tel='9813456554';
 // $chatid='157612765';
 // $a= AddGroupForUser ($link, $ngroup, $tel, $chatid);
 // echo $a;
 
  //$message='1, 9115006433';
  //$subject='/[0-9]{10}/';
  // if (preg_match($subject, $message)==1 )
 //   {
 //      echo '1';
 //  }
// $chat_id='157612765';
// $a=takePrepodGroups($link, $chat_id);
 
// echo '<pre>';
// print_r($a);
// echo '</pre>';
 
// $i=0;
// while ($i<count($a))
// {
//  $b[$i]=$a[0][$i].' - '.$a[1][$i]   ;
//   $i++;
// }
//$c= implode(",\r\n", $b);
// echo $c;
//var_dump($b) ;
 //echo '<pre>';
 //print_r($b);
 //echo '</pre>';
//'','$arrNewAb[0]','$arrNewAb[1]','$groupNum','$arrNewAb[1]','$abonemenDo','$timeVisit'
//$string='Маша Силиверстова, 9115006455, 2020-10-05'; 
//$arrNewAb= explode(',', $string); 
//$a=AddNewAb($link,$arrNewAb); 
//echo($a);
 //$arrNewAb='2020-10-05';
 //$date = strtotime("$arrNewAb+3 day");
 // $abonemenDo= date('Y-m-d', $date);
//echo $abonemenDo;
//echo $arrNewAb;
//$date = strtotime("+3 day");
//$abonemenDo= date('Y-m-d', $date);
//echo $abonemenDo;
 //$chatid='157612765';
// echo  checkAdmin($link, $chatid);
//if (checkAdmin($link, $chatid)) {echo 'true';}
// else {echo 'false';}
//$n_group='1';
//$arr=GetUsersFromMyGroup ($link, $n_group);
//echo '<pre>';
//print_r ($arr);
//echo '</pre>';

//$arr1=explode(",", $arr);
///var_dump($arr[0]);
//echo '----------';
///var_dump($arr[1]);
//echo $arr[0][0];
///$keyb=array_chunk($arr, 2);
//echo '----------';
//var_dump($keyb);

//for ($i=0; $i<count($arr[0]); $i++)
//{
//   $temparr=array('text' => $arr[0][$i], 'callback_data'=> $arr[1][$i]);
//   $keyb[]=$temparr;  
//}

//echo '----------<br><br><br><br><br><br>';

//echo AddUserVisit($link,'9115006431');

//$keyb=implode(",", $keyb);
//echo '----------';
//echo '<pre>';
//print_r ($keyb);
//echo '</pre>';
//var_dump($keyb);
//echo $keyb[1];

/*$arr1=$keyb;
$arr2=[];
for($i=0;$i<count($arr1);$i++)
{
if($i>0&&count($arr)!=$i)$arr2.=',';
$arr2.=$arr1[$i];
}
//echo '<pre>';
//print_r ($arr2);
//echo '</pre>';
echo '----------';
var_dump($arr2);*/