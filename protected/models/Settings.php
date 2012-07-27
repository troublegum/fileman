<?php

/**
 * Settings
 * 
 * @author Сергей
 * @package your package name
 * @version 0.9
 */

class Settings extends CFormModel
{
	public $rootDir;
	public $dateTimeFormat;
	public $dirPermissions;
	public $filePermissions;
	public $itemsOnPage;
	
	public function init()
	{
		$this->attributes = Yii::app()->params->toArray();
	}
	
	public function attributeLabels()
	{
		return array(
			'rootDir' => 'Корневая папка',
			'dateTimeFormat' => 'Формат даты и времени',
			'dirPermissions' => 'Права по умолчанию для папки',
			'filePermissions' => 'Права по умолчанию для файла',
			'itemsOnPage' => 'Кол-во строк в таблице'
 		);
	}
	
	public function rules()
	{
		return array(
			array('dateTimeFormat, dirPermissions, filePermissions, itemsOnPage', 'required'),
			array('rootDir', 'safe'),			
			array('itemsOnPage', 'type', 'type' => 'integer')
		);
	}
	
	public function save()
	{
		if ($this->validate() === false) return false;
		$file = Yii::getPathOfAlias('application.config') . DIRECTORY_SEPARATOR . 'params.php';
		return ArrayWriterHelper::write($file, $this->attributes);
	}
}