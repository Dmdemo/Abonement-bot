   <?php
      
   
require_once ('functions.php');
require_once ('adminfunc.php');

   #Принимаем запрос
   $data = json_decode(file_get_contents('php://input'),TRUE);
   file_put_contents('file.txt', '$data: '.print_r($data,1)."\n",FILE_APPEND);
   
   $data=$data['callback_query'] ? $data['callback_query'] : $data['message'];
   define ('TOKEN', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
  
   //записываем сообщения пользователя и приводим к нижнему регистру
   $message= mb_strtolower(($data['text'] ? $data['text'] : $data['data']),'utf-8');
   
   
   //просим юзера ввести номер телефона чтоб залогинить
   $subject='/[0-9]{10}/';
   if ((preg_match($subject, $message)==1) and (strlen($message)==10)) //если это номер тел 
    {
        //обработаем callback_query по номеру телефона функция отмечания занятия
        if ($data['data']) //если это колбэк квери
           {
            //функция отметить пользовалтелю посещение
              $mes= AddUserVisit($link,$message);  //вызов функции отметить челу посещени по номеру тел  
              if (is_null($mes)) {$mes='уже отмечен сегодня';}
              
              $method= 'sendMessage'; //посылаем сообщение то отмечено
              $send_data=[ 'text' => $mes ];
              $send_data['chat_id'] = $data['message']['chat']['id'];
              $res = sendTelegram($method, $send_data);  
              exit();     
            }
   
        //добавим нового пользователя если у нас еще его нет chat_id
        if (AddUser ($link,$data['chat']['id'],$message))
           {     
            $method= 'sendMessage';
            $send_data= [
              'text' => 'Успех, Мы вас узнали! Кнопка Абонемент ниже',
              'reply_markup'=>[
              'resize_keyboard' => true,
              'keyboard' => [[
                     ['text' => 'Абонемент'],
                     ['text' => 'Информация'],
                             ]]
                               ] 
                         ];
            if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
            else {$send_data['chat_id'] = $data['message']['chat']['id'];}
            $res = sendTelegram($method, $send_data);exit;
              
            }
         else {
             $method= 'sendMessage';
             $send_data=['text' => 'Ваш преподаватель пока еще не занес ваш номер телефона в базу, попробуйте позже'];
             if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
             else {$send_data['chat_id'] = $data['message']['chat']['id'];}
             $res = sendTelegram($method, $send_data);exit;
                  
         }
 
    }
  
         //всех отметим кого нет
      if ((substr_count($message, 'allchecked')==1) and ($data['data']))
      {
          $n_group = trim(substr("$message", 10)); 
   //     $n_group=$groupn;//потом сделать выбор группы предагаем преподу, из доступных по времени
         // $n_group='1';
          $arr=GetUsersFromMyGroup ($link, $n_group);
      
          AddNoVisit($link,$arr); //отмечаем всем минусы кого нет, и уменьшаем кол-во остав визитов на 1
          
              $method= 'sendMessage';
              $send_data=['text' => 'Отметили всех кого нет'.$n_group];
              $send_data['chat_id'] = $data['message']['chat']['id'];
              $res = sendTelegram($method, $send_data);exit;
          
      } 
    
       //добавим номер группы куда запишем нового человека в какую группу
   if ((substr_count($message, ',')==1) and ($data['data'])) //если это колбэк квери и одна запятая (1,9115006464)
       {
        $arrTemp= explode(',', $message);
        $ngroup=trim($arrTemp[0]); //номер группы занимающегося
        $tel=trim($arrTemp[1]);
        
          if (AddGroupForUser ($link, $ngroup, $tel, $data['message']['chat']['id']))
              {
              $method= 'sendMessage';
              $send_data=['text' => hex2bin('f09f918D').'добавили'];
             
              if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
              else {$send_data['chat_id'] = $data['message']['chat']['id'];}
              $res = sendTelegram($method, $send_data);exit;
               } 
        }
   
      //Если есть две запятые в тексте, похоже что это новый абонемент от админа
   if (substr_count($message, ',')==3 and checkAdmin($link, $data['chat']['id']))
      {
        $arrNewAb= explode(',', $message);
        $arrNewAb[0]=trim($arrNewAb[0]); //имя фамилия
        $arrNewAb[1]=trim($arrNewAb[1]); //номер телефона
        $arrNewAb[2]=trim($arrNewAb[2]); //дата начала абон
        $arrNewAb[3]=trim($arrNewAb[3]); //кол-во занятий в аб
    
        if (preg_match($subject, $arrNewAb[1])==1 )  //только если номер телефона введен верно
           {   
             if (AddNewAb($link,$arrNewAb))  //если успешно добавили, напишем что все ок
               {
                  //функция возвр группы препода 
                  $arr=takePrepodGroups ($link, $data['chat']['id']);
                   //готовим inline keyboard
                  $keyb=[];
                  for ($i=0; $i<count($arr[0]); $i++)
                     {
                       $temparr = array('text' => $arr[1][$i], 'callback_data' => $arr[0][$i].','.$arrNewAb[1]);
                       $keyb[] = $temparr;
                      }
                    $inline_keyboard =array_chunk($keyb, 2); //разделим инлайон клавиатуру по 2 в строке
 
                  //создаем клавиатуру колбэк квери
                   $method= 'sendMessage';
                   $send_data=[  
                   'text' => hex2bin('f09f998F').' В какую группу запишем?',  
                   'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                             ]; 

                   //отправляем колбэк квери
                 if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
                 else {$send_data['chat_id'] = $data['message']['chat']['id'];}
                 $res = sendTelegram($method, $send_data);exit;
                }
            }
        else { //y на случай если криво ввел админ номер тел юзера
              $method= 'sendMessage';
              $send_data=['text' => 'Проверьте, что номер телефона вы ввели 10 цифр'];
              $send_data['chat_id'] = $data['chat']['id'];
              $res = sendTelegram($method, $send_data);exit;
              }   
    }
  
   //выбор группы от админа при отмечании 
  if ((substr_count($message, 'groupn')==1) and ($data['data']))
      {
          $n_group = substr("$message", 6); 
   //     $n_group=$groupn;//потом сделать выбор группы предагаем преподу, из доступных по времени
          $arr=GetUsersFromMyGroup ($link, $n_group);
          $keyb=[];
          for ($i=0; $i<count($arr[0]); $i++)
               {
                $temparr = array('text' => $arr[0][$i], 'callback_data' => trim($arr[1][$i]));
                $keyb[] = $temparr;
                }
    $keyb[]=array('text' => 'Всех отметил', 'callback_data' => 'allchecked'.$n_group);
           
          $inline_keyboard =array_chunk($keyb, 2);
 
           $method= 'sendMessage';
            $send_data=[  
               'text' => hex2bin('f09f998F').' namaste',  
               'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                        ];
           $send_data['chat_id'] = $data['message']['chat']['id'];
           $res = sendTelegram($method, $send_data);exit;  
        }
    //обработка callback query когда админ хочет посмотреть абонемент, выбор группы дадим ему
   if ((substr_count($message, 'abadmgrup')==1) and ($data['data']))
      {
          $n_group = substr("$message", 9); 
    //    $n_group=$groupn;//потом сделать выбор группы предагаем преподу, из доступных по времени
          $arr=GetUsersFromMyGroup ($link, $n_group);
          $keyb=[];
          for ($i=0; $i<count($arr[0]); $i++)
               {
                $temparr = array('text' => $arr[0][$i], 'callback_data' => 'abadmntel'.$arr[1][$i]);
                $keyb[] = $temparr;
                }
          $inline_keyboard =array_chunk($keyb, 2);
  
          $method= 'sendMessage';
          $send_data=[  
            'text' => hex2bin('f09f998F').' namaste',  
            'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                       ];
           
             $send_data['chat_id'] = $data['message']['chat']['id'];
             $res = sendTelegram($method, $send_data);exit;  
       }
       
  
  //обработка callback query когда админ хочет посмотреть номер телефона для продлить напр абонемент, выбор группы дадим ему
   if ((substr_count($message, 'admprodgrup')==1) and ($data['data']))
      {
          $n_group = substr("$message", 11); 
    //    $n_group=$groupn;//потом сделать выбор группы предагаем преподу, из доступных по времени
          $arr=GetUsersFromMyGroupAll ($link, $n_group);
          $keyb=[];
          for ($i=0; $i<count($arr[0]); $i++)
               {
                $temparr = array('text' => $arr[0][$i].' - '.$arr[1][$i], 'callback_data' => '_');
                $keyb[] = $temparr;
                }
          $inline_keyboard =array_chunk($keyb, 1);
  
          $method= 'sendMessage';
          $send_data=[  
            'text' => hex2bin('f09f998F').' namaste',  
            'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                       ];
           
             $send_data['chat_id'] = $data['message']['chat']['id'];
             $res = sendTelegram($method, $send_data);exit;  
       }     
  //обраб callback query по номеру телефона покажем абонемент запрашиваемого юзера  
 if ((substr_count($message, 'abadmntel')==1) and ($data['data']))
  {
       $tel = substr("$message", 9); 
       $date1= getDate1fromTel ($link, $tel);
       $date2= getDate2fromTel ($link, $tel);
       
       //показать абонемент
       $arr=[];
       $arr= ShowAb ($link, $tel, $date1, $date2);
       $path=DrawAb ($arr,$tel);
       $pathend=ShowAbon ($link, $tel, $path);
       
       $method= 'sendPhoto';
       $send_data= [
          // 'photo' => 'https://yogaprotiv.ru/bot_abonement/1.jpg',  
         'photo' => 'https://yogaprotiv.ru/bot_abonement/'.$pathend,
         'reply_markup'=>[
            'resize_keyboard' => true,
            'keyboard' => [
                 [
                  ['text' => 'см Абонемент'],   
                 ],
                 [
                     ['text' => 'Отметить'],
                     ['text' => 'Добавить'],
                 ],
                 [
                 
                  ['text' => 'Готово'],   
                 ]
                          ]
                          ] 
                   ];
       
        $send_data['chat_id'] = $data['message']['chat']['id'];
        $res = sendTelegram($method, $send_data);exit;  
  }
   
  
  //ниже обработка обычных сообщений не колбек квери
   switch ($message)
   {
    
    case '/start' :
       //данные пользователя
        $mychat_id = $data['chat']['id'];
             
        if (CheckUser ($link,$mychat_id)) 
          {
           $method= 'sendMessage';
           $send_data=[
           'text' => 'Добро пожаловать! Информация по абонементу по кнопке ниже',
           'reply_markup'=>[
             'resize_keyboard' => true,
             'keyboard' => [
                 [
                     ['text' => 'Абонемент'],
                     ['text' => 'Информация'],
                 ]
                            ]
                            ]  
                            ];
            break;
           }
         else { //на случай если человека не признали, если еще нет его chat_id в бд
                $method= 'sendMessage';
                $send_data=[
                'text' => 'Введите номер телефона в формате 10 цифр, без 8, например 9115006431 и нажмите отправить'  
                            ];
                 break;
               }
      
    case 'абонемент': //юзер смотрит абонемент
       
      $tel = getTel ($link, $data['chat']['id']);
      $date1= getDate1 ($link, $data['chat']['id']);
      $date2= getDate2 ($link, $data['chat']['id']);

      $arr=[];
      $arr= ShowAb ($link, $tel, $date1, $date2);
      $path=DrawAb ($arr,$tel);
      $pathend=ShowAbon ($link, $tel, $path);
      
      $method= 'sendPhoto';
      $send_data= [
       // 'photo' => 'https://yogaprotiv.ru/bot_abonement/1.jpg',  
         'photo' => 'https://yogaprotiv.ru/bot_abonement/'.$pathend,
         'reply_markup'=>[
             'resize_keyboard' => true,
             'keyboard' => [
                 [
                     ['text' => 'Абонемент'],
                     ['text' => 'Информация'],
                 ]
             ]
         ] 
       ]; break; 
        
  
      
    case 'информация' :
       
       $method= 'sendMessage';
       $send_data=[
         'text' => 'Спасибо, что занимаетесь йогой. Все вопросы и предложения по работе программы вы можете отправить @yogavologda'  
                  ]; 
          break;
   
     case 'готово': //скрыть клавиатуру и очистить экран админа
       
       $method= 'sendMessage';
       $send_data=[
         'text' => "_________".hex2bin('f09f998F')."_________"."\r\n"."\r\n"."\r\n"."Хорошей практики "."\r\n"." \r\n"." \r\n"."_________".hex2bin('f09f998F')."_________",
          
          'reply_markup'=>[
             'hide_keyboard' => true,
                  ]];
   
        if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
        else {$send_data['chat_id'] = $data['message']['chat']['id'];}
        $res = sendTelegram($method, $send_data);
       
       //---
        $Addurl=rand(1, 14);
        $method='sendPhoto';
        $send_data=[
              'photo' => 'https://yogaprotiv.ru/bot_abonement/sticker/'.$Addurl.'.png',
                   ];         
        if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
        else {$send_data['chat_id'] = $data['message']['chat']['id'];}
        $res = sendTelegram($method, $send_data);
        exit;
       
   
    case 'админ': //чтоб получить меню админа, введите "админ"
       
      if (checkAdmin($link, $data['chat']['id'])) //проверим есть ли данный препод в таблице преподов
       {
          $method= 'sendMessage';
          $send_data=[  
            'text' => hex2bin('f09f998F').' namaste',  
            'reply_markup'=>[
            'resize_keyboard' => true,
            'keyboard' => [
                 [
                     ['text' => 'см Абонемент'],   
                 ],
                 [
                     ['text' => 'Отметить'],
                     ['text' => 'Добавить'],
                 ],
                 [
                     ['text' => 'телефон'],
                     ['text' => 'Готово'],   
                 ]
                           ]
                             ]  
                      ];
        }
       else
          { //не нашли админа в таблице преподов
           $method= 'sendMessage';
           $send_data=[
           'text' => 'Вас еще не добавили, обратитьсь к администратору'  
                       ]; 
          }
          break; 
        
    case 'см абонемент': //админ препод нажал посмотреть абонемент юзера
       
       if (checkAdmin($link, $data['chat']['id']))
        {
          $arrGroups=takePrepodGroups($link, $data['chat']['id']); //из какой группы будем смотреть?
          $keyb=[];
            for ($i=0; $i<count($arrGroups[0]); $i++)
             {
              $temparr = array('text' => $arrGroups[1][$i], 'callback_data' => 'abadmgrup'.$arrGroups[0][$i]);//добавим groupn в колбек квери чтоб узнать что это выбор группы
              $keyb[] = $temparr;
             }
          $inline_keyboard =array_chunk($keyb, 2);
 
          $method= 'sendMessage';
          $send_data=[  
            'text' => hex2bin('f09f998F').' В какой группе',  
            'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                       ];
  
         } 
         break;  
         
     case 'телефон': //админ препод нажал продлить абонемент юзера
       
       if (checkAdmin($link, $data['chat']['id']))
        {
          $arrGroups=takePrepodGroups($link, $data['chat']['id']); //из какой группы будем смотреть?
          $keyb=[];
            for ($i=0; $i<count($arrGroups[0]); $i++)
             {
              $temparr = array('text' => $arrGroups[1][$i], 'callback_data' => 'admprodgrup'.$arrGroups[0][$i]);//добавим groupn в колбек квери чтоб узнать что это выбор группы
              $keyb[] = $temparr;
             }
          $inline_keyboard =array_chunk($keyb, 2);
 
          $method= 'sendMessage';
          $send_data=[  
            'text' => hex2bin('f09f998F').' В какой группе',  
            'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                       ];
  
         } 
         break;       
    
    case 'добавить': //админ препод - добавить абонемент
       
      if (checkAdmin($link, $data['chat']['id']))
       {
        $method= 'sendMessage';
        $send_data=[
             'text' => "Строго внимательно! Введите обязательно через запятую:\r\n Имя Фамилия,\r\n телефон(10 цифр),\r\n дата начала абонемента(гггг-мм-дд),\r\n Кол-во занятий(8)"  
                    ];    
       }
       break; 
    
    case 'отметить':
       
      if (checkAdmin($link, $data['chat']['id']))
       {
        $arrGroups=takePrepodGroups($link, $data['chat']['id']);
        $keyb=[];
          for ($i=0; $i<count($arrGroups[0]); $i++)
            {
             $temparr = array('text' => $arrGroups[1][$i], 'callback_data' => 'groupn'.$arrGroups[0][$i]);//добавим groupn в колбек квери чтоб узнать что это выбор группы
             $keyb[] = $temparr;
             }
        $inline_keyboard =array_chunk($keyb, 2);
    
        $method= 'sendMessage';
        $send_data=[  
          'text' => hex2bin('f09f998F').' Какую группу отметить?',  
          'reply_markup'=>array('inline_keyboard' => $inline_keyboard)
                   ];
           
        }
      else
        {
          $method= 'sendMessage';
          $send_data=[
           'text' => 'Вас еще не добавили, обратитьсь к администратору'  
                      ]; 
        }
        break;      
       
       
    default :
       $method='sendMessage';
       $send_data=[
             'text' => hex2bin('f09f9986').' Не смог понять'
                  ]; break; 
   }
   
     //отправляем то что выбрано было в case
   if ($data['text']) {$send_data['chat_id'] = $data['chat']['id'];}
   else {$send_data['chat_id'] = $data['message']['chat']['id'];}
   $res = sendTelegram($method, $send_data);
   
   function sendTelegram($method, $data, $headers = [])
   {
       $curl = curl_init();
       curl_setopt_array($curl,[
       CURLOPT_POST => 1,
       CURLOPT_HEADER => 0,
       CURLOPT_RETURNTRANSFER =>1,
       CURLOPT_URL =>'https://api.telegram.org/bot'.TOKEN.'/'.$method,
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"),$headers)    
       ]);
       $result= curl_exec($curl);
       curl_close($curl);
       return(json_decode($result, 1) ? json_decode($result, 1) : $result);
   }
   
        ?>
   
