<html>
<head>
	<title>Проверка установки</title>
	<meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
</head>
<body>
<h3>Скрипт проверки установки.</h3>
<?php
$dir = dirname(__FILE__);
printMsg('Проверка папок скрипта...');
checkFile($dir . DIRECTORY_SEPARATOR . 'assets');
echo '<br/>';
checkFile($dir . DIRECTORY_SEPARATOR . 'protected');
echo '<br/>';
checkFile($dir . DIRECTORY_SEPARATOR . 'yii', false);
echo '<br/>';
printMsg('Проверка файла конфигурации скрипта...');
checkFile($dir . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config'  . DIRECTORY_SEPARATOR . 'main.php');
echo '<br/>';
printMsg('Проверка .htaccess...');
checkFile($dir . DIRECTORY_SEPARATOR . '.htaccess', false);

function printOk($str) {
	echo '<span style="color:green">' . $str . '</span></br>';
}

function printError($str) {
	echo '<span style="color:red">' . $str . '</span></br>';
}

function printMsg($str) {
	echo '<p>' . $str . '</p>';
}

function checkFile($file, $checkWritable = true, $checkReadable = true) {
	if (file_exists($file)) {
		printOk("Папка/файл: $file существует - OK");
	} else {
		printError("Папка/файл: $file НЕ существует - ОШИБКА");
		return;
	}
	
	if ($checkWritable) {
		if (is_writable($file)) {
			printOk("Папка/файл: $file возможность записи - OK");
		} else {
			printError("Папка/файл: $file НЕТ возможности записи - ОШИБКА");
			return;
		}
	}
	
	if ($checkReadable) {
		if (is_readable($file)) {
			printOk("Папка/файл: $file возможность чтения - ОК");
		} else {
			printError("Папка/файл: $file НЕТ возможности чтения - ОШИБКА");
		}
	}
}
?>
<h4>Подсказки:</h4>
<ul>
	<li>Если появляется ошибка: "НЕТ возможности чтения" или "НЕТ возможности записи", то следует проверить права доступа к файлу.</li>
</ul>
</body>
</html>
