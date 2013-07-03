<!--
	#
	#	Breadcrumbs
	#
-->
<h3><?php 
	$pathSize = count($path);
	$index = 1;
	foreach ($path as $value) {
		echo '<a href="' . $this->createUrl($this->route) . '?dir=' . urlencode($value['url']) . '">' . (($this->isOsWindows()) ? $this->convertCp1251ToUtf8($value['text']) : $value['text']) . '</a>';				
		if ($index < $pathSize) echo ' / ';
		$index++;
	}
?></h3>
<!-- 
	#
	#	Control buttons
	#
-->
<div id="control-btn">
	<a class="btn" href="#upload-file" data-toggle="modal">Загрузить файл</a>
	<a class="btn" href="#mkdir" data-toggle="modal">Создать папку</a>
</div>
<!-- 
	#
	#	Grid
	#
-->
<table id="table" class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<th class="center"><input type="checkbox" name="checkAll" title="Выделить все / Снять выделение" /></th>
			<?php 
				$getParams = $_GET;
				$getParams['sort'] = 'label';
				if (isset($getParams['direction'])) {
					if ($getParams['direction'] == 'DESC') $getParams['direction'] = 'ASC'; else $getParams['direction'] = 'DESC';
				} else {
					$getParams['direction'] = 'DESC';
				}
			?>
			<th field="label"><a href="<?php echo $this->createUrl('fileman/index', $getParams); ?>"><i class="icon icon-arrow-up hidden"></i>Название</a></th>
			<?php $getParams['sort'] = 'filetime'?>
			<th field="filetime"><a href="<?php echo $this->createUrl('fileman/index', $getParams); ?>"><i class="icon icon-arrow-up hidden"></i>Дата&nbsp;изменения</a></th>
			<?php $getParams['sort'] = 'intsize'?>
			<th field="intsize"><a href="<?php echo $this->createUrl('fileman/index', $getParams); ?>"><i class="icon icon-arrow-up hidden"></i>Размер&nbsp;(байты)</a></th>
			<?php $getParams['sort'] = 'extension'?>
			<th field="extension"><a href="<?php echo $this->createUrl('fileman/index', $getParams); ?>"><i class="icon icon-arrow-up hidden"></i>Тип</a></th>
			<th><i class="icon icon-arrow-up hidden"></i>Права</a></th>
			<th>Упр.</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($list as $i => $row): ?>
			<?php if ($row['type'] == 'up'): ?>
				<tr>
					<td></td>
					<td colspan="6">						
						<a href="<?php echo $row['url']; ?>" title="На вверх"><i class="icon icon-share-alt"></i><?php echo $row['label']; ?></a>
					</td>
				</tr>
			<?php else: ?>
				<tr id="table-row-<?php echo $i; ?>" class="grid-row">
					<td width="25px" class="center">
						<input type="checkbox" class="select" />
					</td>
					<td style="display:none;" >
						<span data="path"><?php echo $row['path']; ?></span>
						<span data="type"><?php echo $row['type']; ?></span>
					</td>
					<td>
						<?php if ($row['type'] == 'dir'): ?>
						<a href="<?php echo $row['url']; ?>">
								<i class="icon icon-folder-close"></i><span data="label"><?php echo $row['label']; ?></span>
								<?php if ($row['isLink']): ?><up title="Это ссылка">(*)</up><?php endif; ?>
							</a>
						<?php else: ?>
							<i class="icon icon-file"></i><span data="label"><?php echo $row['label']; ?></span>
							<?php if ($row['isLink']): ?><up title="Это ссылка">(*)</up><?php endif; ?>
						<?php endif; ?>
					</td>					
					<td width="20%"><?php echo $row['modificationDateTime']; ?></td>
					<td width="15%" data="size"><?php echo $row['size']; ?></td>
					<td width="10%" data="extension"><?php echo $row['extension']; ?></td>
					<td width="10%" data="permissions"><?php echo $row['permissions']; ?></td>
					<td width="20px" class="center"><a href="#context-menu" title="Управление" data-toggle="modal"><i class="icon icon-tasks" rowId="table-row-<?php echo $i; ?>"></i></a></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</tbody>
</table>
<div class="row">
	<div class="span5">
		<!-- 
			#
			#	Group actions
			#
		-->
		<ul class="nav nav-pills" id="group-actions">
			<li><a href="#groupMove">Переместить</a></li>
			<li><a href="#groupCopy">Скопировать</a></li>
			<li><a href="#groupDelete">Удалить</a></li>
		</ul>
	</div>
	<div class="span7">
		<?php if ($pagination->getPageCount() > 1): ?>
			<!-- 
				#
				#	Pagination 
				#
			-->
			<div class="pagination pagination-right">
				<ul>
					<?php for ($i = 0; $i < $pagination->getPageCount(); $i++): ?>
						<li <?php if ($pagination->getCurrentPage() == $i) echo 'class="active"'; ?>><a href="<?php echo $pagination->createPageUrl($this, $i); ?>"><?php echo $i + 1; ?></a></li>
					<?php endfor; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>
<!-- 
	#	
	#	Context menu
	#
-->
<div class="modal hide" id="context-menu">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Управление</h3>
	</div>
	<div class="modal-body">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="#rename" data-dismiss="modal">Переименовать</a></li>
			<li><a href="#move" data-dismiss="modal">Переместить</a></li>			
			<li><a href="#copy" data-dismiss="modal">Копировать</a></li>
			<?php if (PHP_OS == "Linux"): ?>
				<li><a href="#chmod" data-dismiss="modal">Сменить права</a></li>
			<?php endif; ?>	
			<li><a href="#download" target="_blank" data-dismiss="modal">Скачать</a></li>
			<li><a href="#zip" data-dismiss="modal" waiting="waiting">Заархивировать (zip)</a></li>
			<li><a href="#unzip" data-dismiss="modal">Распокавать архив (zip)</a></li>
			<li><a href="#remove" data-dismiss="modal">Удалить</a></li>
		</ul>
	</div>
</div>
<!--
	# 
	#	Dialog "Upload file" 
	#
-->
<div class="modal hide" id="upload-file">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Загрузить файл</h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-info">
			<div>Макс. размер загружаемого файла: <?php echo ini_get('upload_max_filesize'); ?>.</div>
			<![if !IE]><div>Доступна множ. загрузка файлов.</div><![endif]>
		</div>
		<form name="fileupload" action="<?php echo $this->createUrl('upload'); ?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="dir" value="<?php echo $dir; ?>" />			
			<label><input type="radio" name="select" checked="checked" value="local" /> Локально:</label>
			<input type="file" name="files[]" multiple="multiple" class="input-file input-xlarge" />
			<div style="height:10px"></div>
			<label><input type="radio" name="select" value="http" /> Удаленно через HTTP:</label>
			<input type="text" name="url" placeholder="Введите url" disabled="disabled" class="input-xlarge" />
		</form>		
	</div>
	<div class="modal-footer">
		<a href="#doUploadFile" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('fileupload')">Загрузить</a>
	</div>
</div>
<!-- 
	#
	# Dialog "Mkdir"
	#
-->
<div class="modal hide" id="mkdir">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Создать папку</h3>
	</div>
	<div class="modal-body">
		<form name="mkdir" action="<?php echo $this->createUrl('mkdir'); ?>" method="post" class="form-inline">
			<input type="hidden" value="<?php echo $dir ?>" name="dir" />
			<label>Название папки:</label>
			<input type="text" name="name" />
		</form>
	</div>
	<div class="modal-footer">
		<a href="#doMkDir" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('mkdir')">Создать</a>
	</div>
</div>
<!-- 
	#
	#	Dailog "Rmdir"
	#
-->
<div class="modal hide" id="rmdir">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<p>Удалить эту папку и все ее подпапки?</p> 
	</div>
	<div class="modal-footer">
		<a href="#close" class="btn" data-dismiss="modal">Нет</a>
		<a href="#doRmdir" class="btn btn-primary" data-dismiss="modal" waiting="waiting">Да</a>
	</div>
</div>
<!-- 
	#	
	#	Dialog "Unlink"
	#
-->
<div class="modal hide" id="unlink">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<p>Удалить этот файл?</p> 
	</div>
	<div class="modal-footer">
		<a href="#close" class="btn" data-dismiss="modal">Нет</a>
		<a href="#doUnlink" class="btn btn-primary" data-dismiss="modal" waiting="waiting">Да</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Group delete"
	#
-->
<div class="modal hide" id="group-delete">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Удалить все</h3>
	</div>
	<div class="modal-body">
		<form name="group-delete" action="<?php echo $this->createUrl('groupDelete'); ?>" method="post" style="display:none">
			<input type="hidden" value="<?php echo $this->getCurrentDir(); ?>" name="dir" />			
		</form>
		<p>Удалить выбраные папки и файлы?</p>
	</div>
	<div class="modal-footer">
		<a href="#close" class="btn" data-dismiss="modal">Нет</a>
		<a href="#doGroupDelete" class="btn btn-primary" data-dismiss="modal" waiting="waiting">Да</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Rename"
	#
-->
<div class="modal hide" id="rename">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<form name="rename" action="<?php echo $this->createUrl('rename'); ?>" method="post" class="form-inline">
			<input type="hidden" value="<?php echo $dir; ?>" name="dir" />
			<input type="hidden" value="" name="oldName" />
			<label>Новое название</label>
			<input type="text" name="newName" />
		</form>
	</div>
	<div class="modal-footer">
		<a href="#doRename" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('rename')">Переименовать</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Move"
	#
-->
<div class="modal hide" id="move">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<form name="move" action="<?php echo $this->createUrl('move'); ?>" method="post" class="form-inline">
			<input type="hidden" value="" name="source" />
			<label>Переместить в:</label>
			<input type="text" value="<?php echo $dir; ?>" name="destination" />
			<a href="#tree-load" class="btn" title="Выбрать папку"><i class="icon-list-alt"></i></a>
		</form>
		<div class="fileTree"></div>
	</div>
	<div class="modal-footer">
		<a href="#doMove" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('move')">Переместить</a>
	</div>
</div>
<!-- 
	#
	#	Dailog "Group move"
	#
-->
<div class="modal hide" id="group-move">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Переместить все</h3>
	</div>
	<div class="modal-body">
		<form name="group-move" action="<?php echo $this->createUrl('groupMove'); ?>" method="post" class="form-inline">
			<input type="hidden" value="<?php echo $this->getCurrentDir(); ?>" name="dir" />
			<label>Переместить в:</label>
			<input type="text" value="<?php echo $dir; ?>" name="destination" />
			<a href="#tree-load" class="btn" title="Выбрать папку"><i class="icon-list-alt"></i></a>
		</form>
		<div class="fileTree"></div>
	</div>
	<div class="modal-footer">
		<a href="#doGroupMove" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('group-move')">Переместить</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Copy"
	#
-->
<div class="modal hide" id="copy">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<form name="copy" action="<?php echo $this->createUrl('copy'); ?>" method="post" class="form-inline">
			<input type="hidden" value="" name="source" />
			<label>Скопировать в:</label>
			<input type="text" value="<?php echo $dir; ?>" name="destination" />
			<a href="#tree-load" class="btn" title="Выбрать папку"><i class="icon-list-alt"></i></a>
		</form>
		<div class="fileTree"></div>
	</div>
	<div class="modal-footer">
		<a href="#doCopy" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('copy')">Копировать</a>
	</div>
</div>
<!-- 
	#
	#	Group copy
	# 
-->
<div class="modal hide" id="group-copy">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Скорировать все</h3>
	</div>
	<div class="modal-body">
		<form name="group-copy" action="<?php echo $this->createUrl('groupCopy'); ?>" method="post" class="form-inline">
			<input type="hidden" value="<?php echo $this->getCurrentDir(); ?>" name="dir" />
			<label>Скопировать в:</label>
			<input type="text" value="<?php echo $dir; ?>" name="destination" />
			<a href="#tree-load" class="btn" title="Выбрать папку"><i class="icon-list-alt"></i></a>
		</form>
		<div class="fileTree"></div>
	</div>
	<div class="modal-footer">
		<a href="#doGroupCopy" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('group-copy')">Копировать</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Chmod"
	#
-->
<div class="modal hide" id="chmod">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<form name="chmod" action="<?php echo $this->createUrl('chmod'); ?>" method="post" class="form-inline">
			<input type="hidden" value="<?php echo $dir; ?>" name="dir" />
			<input type="hidden" value="" name="file" />
			<label>Права доступа:</label>
			<input type="text" name="mode" />
		</form>
	</div>
	<div class="modal-footer">
		<a href="#doChmod" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('chmod')">Изменить</a>
	</div>
</div>
<!-- 
	# 
	#	Dialog "Unzip"
	#
-->
<div class="modal hide" id="unzip">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><span class="placeholder-label"></span></h3>
	</div>
	<div class="modal-body">
		<form name="unzip" action="<?php echo $this->createUrl('unzip'); ?>" method="post" class="form-inline">			
			<input type="hidden" value="" name="file" />
			<input type="hidden" value="<?php echo $dir; ?>" name="dir" />
			<label>Куда распаковать:</label>
			<input type="text" value="<?php echo $dir; ?>" name="destination" />
			<a href="#tree-load" class="btn" title="Выбрать папку"><i class="icon-list-alt"></i></a>
			<div class="fileTree"></div>
		</form>
	</div>
	<div class="modal-footer">
		<a href="#doUnzip" class="btn" data-dismiss="modal" waiting="waiting" onclick="submitForm('unzip')">Распаковать</a>
	</div>
</div>
<!-- 
	#
	#	Dialog "Alert nothing selected"
	#
-->
<div class="modal hide" id="alert-nothing-selected">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Ошибка</h3>
	</div>
	<div class="modal-body">
		<i class="icon icon-ban-circle"></i>Ничего не выбранно!
	</div>
	<div class="modal-footer">
		<a href="#close" class="btn" data-dismiss="modal">Закрыть</a>
	</div>
</div>

<div class="modal hide" id="waiting" data-backdrop="static">
	<div class="modal-body">
		Ждем...
	</div>
</div>