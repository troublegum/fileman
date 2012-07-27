<?php
class ZipHelper 
{
	public function zip($archiveFilename, $filename, $flags = ZipArchive::OVERWRITE)
	{
		$zip = new ZipArchive();
		$res = $zip->open($archiveFilename, $flags);
		if ($res === false) throw new CException(); //TODO Сообщение об ошибке
		if (is_dir($filename)) {			
			self::recursive($filename, $zip, '');
		} else {
			$zip->addFile($filename, basename($filename));
		}
		$zip->close();
	}
	
	public function unzip($filename, $dir)
	{
		$zip = new ZipArchive();
		$res = $zip->open($filename);
		if ($res === false) throw new CException(); //TODO Сообщение об ошибке
		if ($zip->extractTo($dir) === false) throw new CException(); //TODO Сообщение об ошибке
		$zip->close();
	}

	private static function recursive($filename, $zip, $zipFolder = '')
	{
		if (is_dir($filename)) {
			$zipFolder = (empty($zipFolder)) ? basename($filename) : $zipFolder . DIRECTORY_SEPARATOR . basename($filename);
			$zip->addEmptyDir($zipFolder);
			$list = FileHelper::readdir($filename);
			foreach ($list as $file) {
				self::recursive($file, $zip, $zipFolder);
			}
		} else {
			$zip->addFile($filename, $zipFolder . DIRECTORY_SEPARATOR . basename($filename));
		}
	}
}