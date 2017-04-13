<?php

// Страница регситрации нового пользователя


# Соединямся с БД
include("config.php");	
include("db-function.php");



if(isset($_POST['submit']))

{

    $err = array();


    # проверям логин

    if(!preg_match("/^[a-zA-Z0-9_]+$/",$_POST['login']))

    {

        $err[] = "Логин может состоять только из букв английского алфавита и цифр";

    }

    

    if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 30)

    {

        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";

    }

    

    # проверяем, не сущестует ли пользователя с таким именем

    $count = mysql_getcell("SELECT COUNT(user_id) FROM users WHERE user_login=".mysql_escape($_POST['login'])."");

    if($count > 0)

    {

        $err[] = "Пользователь с таким логином уже существует в базе данных";

    }
    
    # Проверяем не пустой ли пароль
    
    if(empty($_POST['password'])) {
		$err[] = "Пароль не может быть пустым";
	}
    
    
    # Проверяем сходятся ли пароли
    
    if($_POST['password'] != $_POST['confirmation']) {
		$err[] = "Пароли не совпадают";
	}

    

    # Если нет ошибок, то добавляем в БД нового пользователя

    if(count($err) == 0)

    {

        
        $login = $_POST['login'];

        

        # Убераем лишние пробелы и делаем двойное шифрование

        $password = md5(md5(trim($_POST['password'])));

        

        mysql_q("INSERT INTO users SET user_login='".$login."', user_password='".$password."'");

        header("Location: login.php"); exit();

    }

    else

    {

        print "<b>При регистрации произошли следующие ошибки:</b><br>";

        foreach($err AS $error)

        {

            print $error."<br>";

        }

    }

}

?>
<html>
<head>
<meta charset="UTF-8" />

<script type="text/javascript">
window.onload = function () {
    document.getElementById("password1").onchange = validatePassword;
    document.getElementById("password2").onchange = validatePassword;
}
function validatePassword(){
var pass2=document.getElementById("password2").value;
var pass1=document.getElementById("password1").value;
if(pass1!=pass2)
    document.getElementById("password2").setCustomValidity("Passwords Don't Match");
else
    document.getElementById("password2").setCustomValidity('');
//empty string means no validation error
}
</script>

</head>
<body>

<p>Заполните форму ниже. После того, как правильно заполните форму и нажмёте кнопку "Зарегистрироваться", свяжитесь с пользователем MegaRipZ и подтвердите ему серьёзность своих намерений :) </p>

<form method="POST">
<table>
<tr><td>Логин</td><td><input name="login" type="text" pattern="[a-zA-Z0-9._]{3,30}" required></td></tr>

<tr><td>Пароль</td><td><input id="password1" name="password" type="password" required></td></tr>

<tr><td>Подтверждение</td><td><input id="password2" name="confirmation" type="password"></td></tr>

</table>
<input name="submit" type="submit" value="Зарегистрироваться">

</form>
</body>
</html>
