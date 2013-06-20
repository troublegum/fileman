<h2>Настройки</h2>
<?php $form = $this->beginWidget('CActiveForm', array('id' => 'settings-form', 'htmlOptions' => array('class' => 'well'))); ?>
	<div class="form-row">
		<?php echo $form->labelEx($model, 'rootDir'); ?>
		<?php echo $form->textField($model, 'rootDir'); ?>
		<?php echo $form->error($model, 'rootDir'); ?>
	</div>
	<div class="form-row">
		<?php echo $form->labelEx($model,'dateTimeFormat'); ?>
		<?php echo $form->textField($model, 'dateTimeFormat'); ?>
		<?php echo $form->error($model, 'dateTimeFormat'); ?>
	</div>
	<div class="form-row">
		<?php echo $form->labelEx($model, 'dirPermissions'); ?>
		<?php echo $form->textField($model, 'dirPermissions'); ?>
		<?php echo $form->error($model, 'dirPermissions'); ?>
	</div>
	<div class="form-row">
		<?php echo $form->labelEx($model, 'filePermissions'); ?>
		<?php echo $form->textField($model, 'filePermissions'); ?>
		<?php echo $form->error($model, 'filePermissions'); ?>
	</div>
	<div class="form-row">
		<?php echo $form->labelEx($model, 'itemsOnPage'); ?>
		<?php echo $form->textField($model, 'itemsOnPage'); ?>
		<?php echo $form->error($model, 'itemsOnPage'); ?>
	</div>
		<div class="form-row">
		<?php echo $form->labelEx($model, 'forbiddenDirs'); ?>
		<?php echo $form->textArea($model, 'forbiddenDirs'); ?>
		<div><small>По одной директории на строке.</small></div>
		<?php echo $form->error($model, 'forbiddenDirs'); ?>
	</div>	
	<div class="form-row-last">
		<?php echo CHtml::submitButton('Сохранить', array('class' => 'btn')); ?>
	</div>	
<?php $this->endWidget(); ?>