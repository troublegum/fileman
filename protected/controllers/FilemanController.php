<?php

/**
 * SiteController
 * 
 * @author Сергей
 * @package FileMan
 * @version 0.9
 */

class FilemanController extends CController
{
	public function actionIndex()
	{
		$dir = $this->getCurrentDir();
		$app = Yii::app();
		if (!is_dir($dir)) $app->user->setFlash('critical', 'Задан неверный путь к папке.');
		
		try {
			$array = @FileHelper::readdir($dir);
		} catch (Exception $e) {
			$array = array();
			$app->user->setFlash('critical', 'Невозможно прочитать папку "' . $dir . '".');
		}
		
		$data = array();
		$isOsWindows = $this->isOsWindows();
		foreach ($array as $item) {
			$label = FileHelper::getBasename($item);
			if ($isOsWindows) $label = $this->convertCp1251ToUtf8($label);
			$path = $item;
			$isLink = is_link($path);
			$dateTimeFormat = $app->params['dateTimeFormat'];
			$filetime = @filemtime($path);
			$modificationDateTime = $app->dateFormatter->format($dateTimeFormat, $filetime);
			$permissions = @fileperms($path);
			if ($permissions === false) {
				$permissions = 'N/A';
			} else {
				$permissions = substr(sprintf('%o', $permissions), -4);
			}

			if (is_dir($item)) {
			 	$data[] = array(
					'type' => 'dir',
					'isLink' => $isLink,
					'path' => $path,
					'label' => $label,
					'modificationDateTime' => $modificationDateTime,
					'filetime' => $filetime,
					'size' => '-',
					'intsize' => 0,
					'permissions' => $permissions,
					'extension' => '-',
					'url' => $this->createUrl($this->route, array('dir' => $path))
				);
			} else {
				$size = @filesize($item);
				if ($size === false) {
					$intsize = 0;
					$size = 'N/A';
				} else if ($size < 0) {
					$intsize = 0;
					$size = '> 1Гb';
				} else {
					$intsize = $size;
					$size = number_format($size, 0, ' ', ' ');
				}
				$extension = FileHelper::getExtension($path);
				if (empty($extension)) $extension = '-';
				$data[] = array(
					'type' => 'file',
					'isLink' => $isLink,
					'path' => $path, 
					'label' => $label,
					'modificationDateTime' => $modificationDateTime,
					'filetime' => $filetime,
					'size' => $size,
					'intsize' => $intsize,
					'permissions' => $permissions,
					'extension' => $extension,
				);
			}
		}
		
		// Сортировка таблицы
		if (isset($_GET['sort']) && isset($_GET['direction'])) {
			$data = $this->sortData($data, $_GET['sort'], $_GET['direction']);
		}

		$provider = new CArrayDataProvider($data, array('pagination' => array('pageSize' => Yii::app()->params->itemsOnPage)));
		$list = $provider->data;
		$upDir = dirname($dir) . DIRECTORY_SEPARATOR;
		array_unshift($list, array('type' => 'up', 'path' => $upDir, 'label' => '..', 'url' => $this->createUrl($this->route, array('dir' => $upDir))));
		
		// Создаем путь (хлебные крошки)
		$currDir = rtrim($this->getCurrentDir(), '\\/');
		$currExplodedDir = preg_split('#\\\\|/#', $currDir);
		if (isset($currExplodedDir[0]) && $currExplodedDir[0] == '') $currExplodedDir[0] = DIRECTORY_SEPARATOR; //FIX для UNIX
		$path = array();
		$url = '';
		foreach ($currExplodedDir as $value) {
			if ($value != DIRECTORY_SEPARATOR) {
				$url .= ($value . DIRECTORY_SEPARATOR);
			} else {
				$url = DIRECTORY_SEPARATOR;
			}
			$path[] = array('text' => $value, 'url' => $url);
		}
		
		$this->render('index', array('list' => $list, 'pagination' => $provider->pagination, 'dir' => $dir, 'path' => $path));
	}
	
	//+
	public function actionMkdir()
	{
		$app = Yii::app();
		$dir = $_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['name'];
		
		if (!is_dir($_POST['dir'])) {
			$app->user->setFlash('error', 'Задан неверный путь к папке "' . $_POST['dir'] . '".');
			$dir = $this->getCurrentDir();
		} else if (file_exists($dir)) {
			$app->user->setFlash('error', 'Неудалось создать папку "' . $_POST['name'] . '" т.к. папка с таким именем уже существует.');
			$dir = $_POST['dir'];
		} else {
			if (@mkdir($dir, octdec($app->params['dirPermissions']))) {
				$app->user->setFlash('success', 'Папка "' . $_POST['name'] .'" успешно создана');
			} else {
				$app->user->setFlash('error', 'Произошла ошибка при создании папки "' . $dir . '"');
			}
			$dir = $_POST['dir'];
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//+
	public function actionRmdir()
	{
		$app = Yii::app();
		$dir = dirname($_GET['path']);
		
		if (file_exists($_GET['path'])) {
			try {
				FileHelper::rmdir($_GET['path'], true);
				$app->user->setFlash('success', 'Папка "' . FileHelper::getBasename($_GET['path']) . '" успешно удалена');
			} catch (CException $e) {
				$app->user->setFlash('error', 'Произошла ошибка при удалении папки "' . $_GET['path'] . '"');
			}
		}
				
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//+
	public function actionUnlink()
	{
		$app = Yii::app();
		
		if (file_exists($_GET['path'])) {
			if (@unlink($_GET['path'])) {
				$app->user->setFlash('success', 'Файл "' . FileHelper::getBasename($_GET['path']) . '" успешно удален.');
			} else {
				$app->user->setFlash('error', 'Произошла ошибка при удалении файла "' . $_GET['path'] . '".');
			}
		}
		$dir = dirname($_GET['path']);
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//-
	public function actionGroupDelete()
	{
		$app = Yii::app();
		
		foreach ($_POST['files'] as $file) {
			if (is_dir($file)) {
				try {
					FileHelper::rmdir($file, true);
				} catch (Exception $e) {
					$app->user->setFlash('error', 'Произошла ошибка при удалении папки "' . $file . '".');
					break;
				}
			} else {
				if (@unlink($file) === false) {
					$app->user->setFlash('error', 'Произошла ошибка при удалении файла "' . $file . '".');
					break;
				}
			}
		}
		
		if (!$app->user->hasFlash('error')) {
			$app->user->setFlash('success', 'Все файлы или папки успешно удалены.');
		}
		
		$this->redirect(array('index', 'dir' => $_POST['dir']));
	}

	//+
	public function actionRename()
	{
		$app = Yii::app();
		
		if ($_POST['oldName'] == $_POST['newName']) {
			//Ничего не делаем
		} else if (!file_exists($_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['oldName'])) {
			$app->user->setFlash('error', 'Файл или папка "'. $_POST['oldName'] . '" несуществует.');
		} else if (file_exists($_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['newName'])) {
			$app->user->setFlash('error', 'Файл или папка "' . $_POST['newName'] . '" с таким именем уже существует.');
		} else {
			if (@rename($_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['oldName'], $_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['newName'])) {
				$app->user->setFlash('success', 'Файл или папка "' . $_POST['newName'] . '" успешно переименнован(а).');
			} else {
				$app->user->setFlash('error', 'Произошла ошибка при попытке переименовать файл или папку.');
			}
		}
		
		$this->redirect(array('index', 'dir' => $_POST['dir']));
	}
	
	//+
	public function actionMove()
	{
		$app = Yii::app();
		$destination = $_POST['destination'] . DIRECTORY_SEPARATOR . FileHelper::getBasename($_POST['source']);
		
		if (!file_exists($_POST['source'])) {
			$app->user->setFlash('error', 'Источник файл или папка ненайден.');
			$dir = $app->params['rootDir'];
		} else if (!is_dir($_POST['destination'])) {
			$app->user->setFlash('error', 'Путь назначения задан неверно.');
			$dir = (is_dir($_POST['source'])) ? $_POST['destination'] : dirname($_POST['destination']);
		} else if ($_POST['source'] == $_POST['destination']) {
			$app->user->setFlash('error', 'Нельзя скопировать файл или папку в себя.');
			$dir = (is_dir($_POST['source'])) ? $_POST['destination'] : dirname($_POST['destination']);
		} else if (file_exists($destination)) {
			$app->user->setFlash('error', 'Файл или папка "' . FileHelper::getBasename($_POST['source']) . '" уже существуют в папке назначения.');
			$dir = dirname($_POST['source']);
		} else {
			if (@rename($_POST['source'], $destination)) {
				$app->user->setFlash('success', 'Файл или папка "' . FileHelper::getBasename($_POST['source']) . '" успешно перемещен(а).');
			} else {
				$app->user->setFlash('error', 'Произошла ошибка при попытке переместить файл или директорию.');
			}
			$dir = $_POST['destination'];
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//-
	public function actionGroupMove()
	{
		$app = Yii::app();

		if (!is_dir($_POST['destination'])) {
			$app->user->setFlash('error', 'Путь назначения задан неверно.');
			$dir = $_POST['dir'];
		} else {
			foreach ($_POST['files'] as $file) {
				$destination = $_POST['destination'] . DIRECTORY_SEPARATOR . FileHelper::getBasename($file);
				if (!@rename($file, $destination)) {
					$app->user->setFlash('error', 'Произошла ошибка при попытке переместить файл или директорию "' . $file . '".');
					$dir = $_POST['dir'];
					break;
				}
			}
			if (!$app->user->hasFlash('error')) {
				$app->user->setFlash('success', 'Все файлы или папки успешно перемещены.');
				$dir = $_POST['destination'];
			}	
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//+
	public function actionCopy()
	{
		$app = Yii::app();		
		$dir = dirname($_POST['source']);
		$destination = $this->normalizeDirPath($_POST['destination']) . FileHelper::getBasename($_POST['source']);
		
		if (!file_exists($_POST['source'])) {
			$app->user->setFlash('error', 'Копируемый файл или папка "' . $_POST['source'] . '" не найден.');
		} else if (!is_dir($_POST['destination'])) {
			$app->user->setFlash('error', 'Неверный путь к папке назначения.');			
		} else if (file_exists($destination)) {
			$app->user->setFlash('error', 'Папка или файл с именем "' . FileHelper::getBasename($_POST['source']) . '" уже существует в папке назначения.');
		} else if ($_POST['destination'] == $_POST['source']) {
			$app->user->setFlash('error', 'Нельзя скопировать файл или папку в себя.');
		} else {
			if (is_dir($_POST['source'])) {
				CFileHelper::copyDirectory($_POST['source'], $destination); //return void
				if (file_exists($destination)) {
					$app->user->setFlash('success', 'Папка успешно скопирована.');
				} else {
					$app->user->setFlash('error', 'Произошла ошибка при попытке скопировать директорию.');
				}
			} else {
				if (@copy($_POST['source'], $destination)) {
					$app->user->setFlash('success', 'Файл успешно скопирован.');
				} else {
					$app->user->setFlash('error', 'Произошла ошибка при попытке скопировать файл.');
				}
			}
			$dir = $_POST['destination'];
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//-
	public function actionGroupCopy()
	{
		$app = Yii::app();
				
		if (!is_dir($_POST['destination'])) {
			$app->user->setFlash('error', 'Путь назначения задан неверно.');
			$dir = $_POST['dir'];
		} else {
			$destination = $this->normalizeDirPath($_POST['destination']);
			foreach ($_POST['files'] as $file) {
				if (is_dir($file)) {
					CFileHelper::copyDirectory($file, $destination . FileHelper::getBasename($file));
					if (!file_exists($destination . FileHelper::getBasename($file))) {
						$app->user->setFlash('error', 'Произошла ошибка при попытке скопировать папку "' . $file . '".');
						$dir = $_POST['dir'];
						break;
					}
				} else {
					if (!@copy($file, $destination . FileHelper::getBasename($file))) {
						$app->user->setFlash('error', 'Произошла ошибка при попытке скопировать файл "' . $file . '".');
						$dir = $_POST['dir'];
						break;
					}
				}
			}
			if (!$app->user->hasFlash('error')) {
				$app->user->setFlash('success', 'Все файлы или папки успешно скопированы.');
				$dir = $destination;
			}
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//+
	public function actionChmod()
	{
		$app = Yii::app();
		if (file_exists($_POST['file'])) {
			if (@chmod($_POST['file'], octdec($_POST['mode']))) {
				$app->user->setFlash('success', 'Права на файл или папку "' . FileHelper::getBasename($_POST['file']) . '" успешно изменены.');
			} else {
				$app->user->setFlash('error', 'Нудалось сменить права на файл или папку "' . FileHelper::getBasename($_POST['file']) . '".');
			}
		} else {
			$app->user->setFlash('error', 'Файл или папка "' . FileHelper::getBasename($_POST['file']) . '" не существует.');
		}
		
		$this->redirect(array('index', 'dir' => $_POST['dir']));
	}
	
	//+
	public function actionZip()
	{
		$app = Yii::app();		
		if (!extension_loaded('zip')) {
			$app->user->setFlash('error', 'Неподключенно PHP расширение "zip".');
		} else if (!file_exists($_GET['file'])) {
			$app->user->setFlash('error', 'Архивируемый файл или папка "' . $_POST['file'] . '" не найден.');
		} else {
			$archiveFilename = dirname($_GET['file']) . DIRECTORY_SEPARATOR . FileHelper::getBasename($_GET['file']) . '.zip';
			if (file_exists($archiveFilename)) {
				$app->user->setFlash('error', 'Архив с таким именем существует "' . $archiveFilename . '".');
			} else {
				try {
					ZipHelper::zip($archiveFilename, $_GET['file']);
					$app->user->setFlash('success', 'Архив "' . FileHelper::getBasename($archiveFilename) . '" успешно создан.');
				} catch (Exception $e) {
					$app->user->setFlash('error', 'Произошла ошибка при попытке создать архив.');
				}
			}
		}
		
		$this->redirect(array('index', 'dir' => $_GET['dir']));
	}
	
	//+
	public function actionUnzip()
	{
		$app = Yii::app();
		$dir = $_POST['dir'];
		if (!extension_loaded('zip')) {
			$app->user->setFlash('error', 'Неподключенно PHP расширение "zip".');
		} else if (!file_exists($_POST['file'])) {
			$app->user->setFlash('error', 'Архивируемый файл или папка "' . $_POST['file'] . '" не найден.');
		} else if (!is_dir($_POST['destination'])) {
			$app->user->setFlash('error', 'Папка для распаковки задана не верная"' . $_POST['destination'] . '".');
		} else {
			try {
				ZipHelper::unzip($_POST['file'], $_POST['destination']);
				$app->user->setFlash('success', 'Архив "' . FileHelper::getBasename($_POST['file']) . '" успешно распокован.');
				$dir = $_POST['destination'];
			} catch (Exception $e) {
				$app->user->setFlash('error', 'Произошла ошибка при попытке распоковать архив.');
			}
		}
		
		$this->redirect(array('index', 'dir' => $dir));
	}
	
	//+
	public function actionUpload()
	{
		$app = Yii::app();
		if (!empty($_FILES['files'])) {
			$files = CUploadedFile::getInstancesByName('files');
			foreach ($files as $file) {
				$uploadFilename = $_POST['dir'] . DIRECTORY_SEPARATOR . $file->getName();
				$file->saveAs($uploadFilename);
				@chmod($uploadFilename, octdec($app->params['filePermissions']));
			}
			if ($files) $app->user->setFlash('success', 'Локальные файлы успешно загруженны.');
		} else if (!empty($_POST['url'])) {
			$uploadFilename = $_POST['dir'] . DIRECTORY_SEPARATOR . basename($_POST['url']);
			if (@copy($_POST['url'], $uploadFilename)) {
				$app->user->setFlash('success', 'Удаленные файлы успешно загруженны.');
				@chmod($uploadFilename, octdec($app->params['filePermissions']));
			} else {
				$app->user->setFlash('error', 'Неудалось скачать удаленно файлы.');
			}
		} else {
			$app->user->setFlash('error', 'Ошибка запроса.');
		}
		
		$this->redirect(array('index', 'dir' => $this->getCurrentDir()));
	}
	
	//-
	public function actionAjaxTree()
	{
		$dir = urldecode($_POST['dir']);
		$dirs = FileHelper::readdir($dir, true);
		$this->render('ajaxTree', array('dirs' => $dirs));
	}
	
	//+
	public function actionSettings()
	{
		$model = new Settings();
		$app = Yii::app();
		if ($app->request->isPostRequest) {
			$model->attributes = $_POST['Settings'];
			if ($model->save()) {
				$app->user->setFlash('success', 'Данные успешно сохранены.');
				$this->refresh();
			} else {
				$app->user->setFlash('error', 'Неудалось сохранить данные.');
			}
		}
		$this->render('settings', array('model' => $model));
	}
	
	//- 
	public function actionDownload()
	{
		$content = @file_get_contents($_GET['file']);
		$app = Yii::app();
		if ($content === false) {
			$app->user->setFlash('error', 'Ошибка чтения файла.');
		} else {
			$app->request->sendFile(FileHelper::getBasename($_GET['file']), $content);
		}
	}
	
	public function actionForbidden()
	{
		$this->render('forbidden');
	}
	
	protected function getCurrentDir()
	{
		$app = Yii::app();
		return ($app->request->getParam('dir')) ? $app->request->getParam('dir') : $this->getRootDir();
	}
	
	public function getRootDir()
	{
		$app = Yii::app();
		return (empty($app->params['rootDir'])) ? Yii::getPathOfAlias('webroot') : $app->params['rootDir']; 
	}
	
	public function normalizeDirPath($path)
	{
		$path = preg_replace('#[\\\\/]#', DIRECTORY_SEPARATOR, $path);
		if (!preg_match('#[\\\\/]$#', $path)) {
			$path .=  DIRECTORY_SEPARATOR;
		}		
		return $path;
	}
	
	protected function sortData($data, $sort, $direction = 'ASC')
	{
		$dirs = $files = array();
		foreach ($data as $item) {
			if ($item['type'] == 'dir') {
				$dirs[] = $item;
			} else {
				$files[] = $item;
			}
		}		
		$dirs = $this->sort($dirs, $sort, $direction);
		$files = $this->sort($files, $sort, $direction);
		if ($direction == 'ASC') {
			return array_merge($dirs, $files);
		} else {
			return array_merge($files, $dirs);
		}
	}
	
	protected function sort($data, $sort, $direction)
	{
		$list = array();
		foreach ($data as $i => $value) {
			$list[$i] = $value[$sort];
		}		
		if ($direction == 'ASC') {
			asort($list);
		} else {
			arsort($list);
		}		
		$keys = array_keys($list);		
		$sortdata = array();
		foreach ($keys as $key) {
			$sortdata[] = $data[$key];
		}
		return $sortdata;
	}
	
	public function filters()
	{
		return array(
			'dirAccessControl'
		);
	}
	
	public function filterDirAccessControl($filterChain)
	{
		$forbbidenDirs = Yii::app()->params['forbiddenDirs'];
		$actionId = Yii::app()->controller->action->id;
		if ($actionId != 'forbidden' && $actionId != 'settings' && $forbbidenDirs) {
			$arrForbbidenDirs = preg_split('/\n/', $forbbidenDirs);
			if (!is_array($arrForbbidenDirs)) $arrForbbidenDirs = array($arrForbbidenDirs);
			$currDir = $this->normalizeDirPath($this->getCurrentDir());
			foreach ($arrForbbidenDirs as $i => $dir) {
				if (DIRECTORY_SEPARATOR == '\\') {
					$dir = preg_replace('#\\\\#', '\\\\\\', $dir);
				}
				if (preg_match("#^$dir#", $currDir)) {
					Yii::app()->user->setFlash('error', 'Доступ к директории вида "' . htmlspecialchars($arrForbbidenDirs[$i]) . '" запрещен!');
					$this->redirect(array('forbidden'));
					Yii::app()->end();
				}
			}
		}
		$filterChain->run();
	}
	
	public function convertCp1251ToUtf8($str)
	{
		return iconv('windows-1251', 'utf-8', $str);
	}
	
	protected function isOsWindows()
	{
		return (bool)stristr(PHP_OS, 'WIN'); 
	}
}