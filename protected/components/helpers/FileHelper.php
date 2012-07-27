<?php
class FileHelper extends CFileHelper
{
	public static function chmod($filename, $mode)
	{
		if (file_exists($filename) && chmod($filename, $mode) == false) throw new CException(); //TODO: Сообщение об ошибке
	}
	
	public static function unlink($filename)
	{
		if (file_exists($filename) && unlink($filename) == false) throw new CException(); //TODO: Сообщение об ошибке.
	}
	
	public static function rename($oldname, $newname)
	{
		if (rename($oldname, $newname) == false) throw new CException(); //TODO: Сообщение об ошибке.
	}
	
	public static function copy($source, $dest, $chmod = null)
	{
		if (copy($source, $dest) == false) throw new CException(); //TODO: Сообщение об ошибке.
		if ($chmod !== null) self::chmod($dest, $chmod);
	}
	
	public static function mkdir($dir, $chmod = 0755)
	{
		if (mkdir($dir, $chmod) == false) throw new CException(); //TODO: Сообщение об ошибке.
	}
	
	public static function rmdir($dir, $recursively = false)
	{
		if ($recursively) {
			$files = self::readdir($dir);
			foreach ($files as $file) {
				if (is_dir($file)) {
					self::rmdir($file, true);
				} else {
					self::unlink($file);
				}
			}
		}
		if (rmdir($dir) == false) throw new CException(); //TODO: Сообщение об ошибке.
	}
	
	public static function getBasename($filename)
	{		
		return basename($filename);
	}
	
	public static function getFilename($path)
	{
		return pathinfo($path, PATHINFO_FILENAME);
	}
	
	public static function readdir($dir, $onlyDirs = false)
	{
		if (!is_dir($dir)) throw new CException(); //TODO: Сообщение об ошибке.
		if (!preg_match('#[\\\\/]$#u', $dir)) {
			$dir .= DIRECTORY_SEPARATOR;
		}
		if ($handle = opendir($dir)) {
			$dirs = $files = array();
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$file = $dir . $file;
					if (is_dir($file)) {
						$dirs[] = $file . DIRECTORY_SEPARATOR;
					} else {
						$files[] = $file;
					}
				}
			}
			closedir($handle);
			sort($dirs);
			sort($files);
			if ($onlyDirs) {
				return $dirs;
			} else {
				return array_merge($dirs, $files);
			}
		} else {
			throw new CException(); //TODO: Сообщение об ошибке.
		}
	}
}