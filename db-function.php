<?php

$mysqli = mysqli_connect($hostname, $username, $password, $dbname);
mysqli_set_charset($mysqli, "utf8");

# by Mikhail Serov (1234ru@gmail.com)
# http://webew.ru/articles/3237.webew
# v. 1.43 (19.12.2016)

function mysql_q($sql, $substitutions = array()) {
	
	global $mysqli;
	
	if ($substitutions) 
		$sql = mysql_substitute($sql, $substitutions);
	
	$result = ($mysqli)
	        ? $mysqli->query($sql)
	        : mysql_query($sql);
	
    if ($result) 
    	return $result;
    
    else {
       $trace = debug_backtrace();
       $mysql_functions = array(
        	  	  'mysql_getcell', 
        	  	  'mysql_getrow', 
        	  	  'mysql_getcolumn', 
        	  	  'mysql_gettable',
        	  	  'mysql_write_row'
        	  );
        if (isset($trace[1]) AND in_array($trace[1]['function'], $mysql_functions))
            $level = 1;
        else 
            $level = 0;
            
        $db_error = ($mysqli)
                  ? mysqli_error($mysqli)
                  : mysql_error();
            
        $message =
            '<p><strong>MySQL error</strong> in file <strong>'.$trace[$level]['file'].'</strong>'.
            " at line <strong>" .$trace[$level]['line']."</strong>
            (function <strong>" . $trace[$level]['function'] ."</strong>):<br/>"
            ."\n<span style='color:blue'>$db_error</span>\n\n<pre>$sql</pre></p>";
        trigger_error($message, E_USER_ERROR);
    }
}

function mysql_substitute($sql, $substitutions) {
	// Чтобы следующая метка не могла затронуть содержание предыдущей,
	// например, в случае $subst = array('id' => 5, 'title' => 'а тут :id'),
	// проводить их замену приходится не по очереди через простой foreach,
	// а за один вызов заменяющий функции,
	// для чего нужно составить регулярное выражение, охватывающее
	// все метки. Впрочем, это несложно.
	// О производительности здесь беспокоиться не будем,
	// т.к. запрос - это довольно короткая строка, поэтому он 
	// будет обработан быстро в любом случае.
	
	$regexp = '/:(';
	foreach ($substitutions as $key => $value)
		$regexp .= $key 
					. ( 
						 substr($key, -1) != '`' // нужно учесть,  
						 ? '\b' // что теоретически метки могут быть
						 : ''   // не только вида :word, но и вида :`...`
					  ) 
					. '|';
	
	$regexp = substr($regexp, 0, -1); // убираем лишний '|'	         
	$regexp .= ')/';
	
	$sql = preg_replace_callback(
			$regexp,
			function($matches) use ($substitutions) {
				return mysql_escape($substitutions[$matches{1}]);
			},
			$sql
		);
	return $sql;
}

function mysql_escape($value) {
	
	global $mysqli;
	
	if (is_array($value)) 
		$escaped = implode(',', array_map(__FUNCTION__, $value) );
	elseif (is_string($value)) 
		$escaped = ($mysqli)
		         ? "'" . mysqli_real_escape_string($mysqli, $value) . "'"
		         : "'" . mysql_real_escape_string($value) . "'" ;
	elseif (is_numeric($value))
		$escaped = $value;
	elseif (is_null($value))
		$escaped = 'NULL';
	else
		$escaped = intval($value);
	
	return $escaped;
}

function mysql_getcell($sql, $substitutions = array()) {
	
    $tmp = mysql_getcolumn($sql, FALSE, $substitutions);
    
    $cell = ($tmp)
          ? reset($tmp)
          : FALSE ;
    
    return $cell;
}

function mysql_getrow($sql, $substitutions = array()) {
	
    $tmp = mysql_gettable($sql, FALSE, $substitutions);
    
    $row = ($tmp)
         ? reset($tmp)
         : array();
    
    return $row;
}

function mysql_getcolumn($sql, $makehash = FALSE, $substitutions = array()) {
	
    $data = array();
    
    $result = mysql_q($sql, $substitutions);
    
    $fn = is_resource($result)
    	? 'mysql_fetch_row'
    	: 'mysqli_fetch_row' ;
    
    if (!$makehash) 
		while ($row = $fn($result)) 
			$data[] = $row[0];
    else 
    	while ($row = $fn($result)) 
			$data[$row{0}] = $row[1];
	
	if (!is_resource($result))
		$result->close();
	
    return $data;
}

function mysql_gettable($sql, $keycol = FALSE, $substitutions = array()) {
	
    $data = array();
    
    $result = mysql_q($sql, $substitutions);
    
    $fn = is_resource($result)
    	? 'mysql_fetch_assoc'
    	: 'mysqli_fetch_assoc' ;
    
    if (!$keycol) 
		while ($row = $fn($result)) 
    		$data[] = $row;
    else 
    	while ($row = $fn($result)) 
    		$data[$row{$keycol}] = $row;
    
	if (!is_resource($result))
		$result->close();
	
    return $data;
}

function mysql_write_row(
		$tablename, 
		$data, 
		$unique_key = FALSE, 
		$mode = FALSE) {
	
	global $mysqli;
	
	if (!$unique_key) { // Уникальный идентификатор не указан - INSERT
		
		if (!$mode)
			$sql = "INSERT";
		
		elseif ($mode == 'IGNORE')
			$sql = "INSERT IGNORE";
		
		elseif ($mode == 'REPLACE')
			$sql = "REPLACE";
		
		else {
			$trace = reset(debug_backtrace());
			$message = "
				Uknown mode \"$mode\" given to $trace[function]() in file $trace[file] at line $trace[line]. 
				Terminating function run. 
				";
			trigger_error($message, E_USER_WARNING);
			return FALSE;
		}
		
		$sql .= " INTO $tablename ";
		
		if ($data) {
			
			$sql .= " SET ";
			
			foreach ($data as $key => $value)
				$sql .= "`$key` = :$key, ";
			
			$sql = substr($sql, 0, -2); // убираем запятую и пробел
		}
		else
			$sql .= " VALUES () ";
		
		$result = mysql_q($sql, $data);
		
		$out = ($mysqli)
		     ? $mysqli->insert_id
		     : mysql_insert_id();
	}
	
	else { // UPDATE или INSERT ON DUPLICATE KEY UPDATE
		
		if (!is_array($unique_key))                  // если указана скалярная величина - 
			$unique_key = array('id' => $unique_key); // воспринмаем её как 'id'
		
		if (!$mode) { // обычный UPDATE
			
			// В данном случае поля из второго аргумента подставляются в часть SET,
			// а поля из третьего - в часть WHERE
			
			$sql = "UPDATE $tablename SET ";
			
			// Чтобы одно и то же поле можно было использовать
			// и в части SET, и в части WHERE с разными значениями, например
			// 	UPDATE table 
			// 	SET col1 = 'A', col2 = 'B'
			// 	WHERE col1 = 'C'
			// подстановку значений в запрос проводим "вручную" - 
			// без использования меток.
			
			foreach ($data as $key => $value)
				$sql .= "`$key` = " . mysql_escape($value) . ", ";
			
			$sql = substr($sql, 0, -2); // убираем запятую и пробел
			
			if ($unique_key) {
				
				$sql .= " WHERE ";
				
				foreach ($unique_key as $key => $value)
					$sql .= " `$key` = " . mysql_escape($value) . " AND ";
				
				$sql = substr($sql, 0, -4); // убираем последний AND и пробел
			}
			
			$result = mysql_q($sql);
			
			$out = ($mysqli)
				  ? $mysqli->affected_rows
				  : mysql_affected_rows() ;
		}
		elseif ($mode == 'DUPLICATE') { // INSERT ... ON DUPLICATE KEY UPDATE
			
			$append = is_string(key($unique_key));
			// $append: если массив $unique_key ассоциативный, 
			// значит, в них данные для уникальных полей -
			// включаем их в INSERT и в подставновку в mysql_q()
			// Если же массив числовой, значит
			// все необходимые данные переданы во втором аргументе,
			// а $unique_key содержит только имена полей,
			// которые следует исключить из ON DUPLICATE KEY
			
			if ($append) {
				$all_data = $data + $unique_key; // Все данные для ON DUPLICATE KEY UPDATE
				$data_to_update = $data;         // есть в $data
			}
			else {
				$all_data = $data;
				$data_to_update = array_diff_key(        // В $unique_key переданы имена полей,
						$data,                             // которые необходимо исключить       
				      array_fill_keys($unique_key, TRUE) // из части ON DUPLICATE KEY UPDATE
				   );
			}
			
			$sql = "INSERT INTO $tablename SET ";
			foreach ($all_data as $key => $value)
				$sql .= "`$key` = :$key, ";
			$sql = substr($sql, 0, -2); // убираем запятую и пробел
			
			
			if ($data_to_update) {
				$sql .= " ON DUPLICATE KEY UPDATE " ;
				foreach ($data_to_update as $key => $value)
					$sql .= " `$key` = :$key, ";
				$sql = substr($sql, 0, -2); // убираем запятую и пробел
			}
			
			$result = mysql_q($sql, $all_data);
			
			// Т.к. запрос INSERT - возвращает LAST_INSERT_ID()
			
			$out = ($mysqli)
				  ? $mysqli->insert_id
		        : mysql_insert_id();
		}
	}
	
	return $out;
}

?>
