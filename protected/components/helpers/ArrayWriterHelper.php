<?php
class ArrayWriterHelper 
{
	public static function write($file, $data, $lock = LOCK_EX)
	{
		$export = var_export($data, true);
		if ($export === false) return false;
		$content = "<?php\n return $export\n?>";
		return file_put_contents($file, $content, $lock);
	}
}