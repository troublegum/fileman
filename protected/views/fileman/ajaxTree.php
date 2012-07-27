<?php $this->layout = false; ?>
<ul class="jqueryFileTree" style="display: none;">
	<?php foreach ($dirs as $dir): ?>
	<li class="directory collapsed">
		<a href="#folder" rel="<?php echo $dir; ?>" title="Двойной щелчек - выбор папки"><?php echo basename($dir); ?></a>
	</li>
	<?php endforeach; ?>
</ul>
