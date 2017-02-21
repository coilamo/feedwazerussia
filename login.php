<?php

// Страница авторизации



# Функция для генерации случайной строки

function generateCode($length=6) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";

    $code = "";

    $clen = strlen($chars) - 1;  
    while (strlen($code) < $length) {

            $code .= $chars[mt_rand(0,$clen)];  
    }

    return $code;

}

include('config.php');

# Соединямся с БД

include('db-function.php');


if(isset($_POST['submit']))

{

    # Вытаскиваем из БД запись, у которой логин равняеться введенному

    $query = "SELECT user_id, user_password, user_login FROM users WHERE user_login=".mysql_escape($_POST['login'])." AND enabled=1 LIMIT 1";

    $data = mysql_getrow($query);

    //print_r($data);

    # Соавниваем пароли

    if($data['user_password'] === md5(md5($_POST['password'])))

    {

        # Генерируем случайное число и шифруем его

        $hash = md5(generateCode(10));

            

        if(!@$_POST['not_attach_ip'])

        {

            # Если пользователя выбрал привязку к IP

            # Переводим IP в строку

            $insip = ", user_ip=INET_ATON('".$_SERVER['REMOTE_ADDR']."')";

        }

        

        # Записываем в БД новый хеш авторизации и IP

        mysql_q("UPDATE users SET user_hash='".$hash."' ".$insip." WHERE user_id='".$data['user_id']."'");

        

        # Ставим куки

        setcookie("user_id", $data['user_id'], time()+60*60*24*30);
        
        setcookie("wmeusername", $data['user_login'], time()+60*60*24*30);

        setcookie("user_hash", $hash, time()+60*60*24*30);

        

        # Переадресовываем браузер на страницу проверки нашего скрипта

        header("Location:/"); exit();

    }

    else

    {

        print "Вы ввели неправильный логин/пароль";

    }

}

?>
<html>
<head>
<meta charset="UTF-8" />
</head>
<body>
<form method="post" id="formLogin">
<table>
<tr><td>Логин</td><td><input name="login" type="text" id="username"></td></tr>

<tr><td>Пароль</td><td><input name="password" type="password" id="password"></td></tr>

</table>
<input name="submit" type="submit" value="Войти">

</form>
</body>
</html>
