<?php 
	$app = Yii::app();
	$baseUrl = $app->request->baseUrl;
	$cs = $app->getClientScript();
	$cs->registerCoreScript('jquery');
	$cs->registerScriptFile($baseUrl . '/assets/bootstrap/js/bootstrap.min.js');
	$cs->registerScriptFile($baseUrl . '/assets/jquery.filetree/jqueryFileTree.js');
	$cs->registerScriptFile($baseUrl . '/assets/fileman/js/script.js');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link href="<?php echo $baseUrl; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="<?php echo $baseUrl; ?>/assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
		<link href="<?php echo $baseUrl; ?>/assets/fileman/css/styles.css" rel="stylesheet" />
		<link href="<?php echo $baseUrl; ?>/assets/jquery.filetree/jqueryFileTree.css" rel="stylesheet" />
		<link rel="shortcut icon" href="favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<title><?php echo $app->name; ?></title>
		<script type="text/javascript">
			var currentDir = "<?php echo addslashes($this->getCurrentDir()); ?>";
			var rootDir = "<?php echo addslashes($this->getRootDir()); ?>";
			var urlRmdir = "<?php echo $this->createUrl("rmdir"); ?>";
			var urlUnlink = "<?php echo $this->createUrl("unlink"); ?>";			
			var urlAjaxTree = "<?php echo $this->createUrl('ajaxTree'); ?>";
			var urlZip = "<?php echo $this->createUrl("zip"); ?>";
			var urlDownload = "<?php echo $this->createUrl("download"); ?>";
		</script>
	</head>
	<body>
		<div class="container">
			<!-- 
				#
				#	Top menu 
				#
			-->
			<div class="navbar">
				<div class="navbar-inner">
					<div class="container">
						<a class="brand" href="<?php echo $this->createUrl('index', array('dir' => $this->getRootDir())); ?>"><?php echo $app->name; ?></a>
						<ul class="nav">
							<li><a href="<?php echo $this->createUrl('settings'); ?>">Настройки</a></li>
							<li><a href="#about" data-toggle="modal">О программе</a></li>
						</ul>
					</div>
				</div>
			</div>
			<!-- 
				#	
				#	Critical error 
				#
			-->
			<?php if ($app->user->hasFlash('critical')): ?>
				<div class="alert alert-error">
					<?php echo $app->user->getFlash('critical'); ?>
				</div>
			<?php else: ?>
				<!-- 
					#	
					#	Alerts 
					#
				-->
				<?php if ($app->user->hasFlash('error')): ?>
					<div class="alert alert-error">
						<strong>Ошибка:</strong> <?php echo $app->user->getFlash('error'); ?>
					</div>
				<?php endif; ?>
				<?php if ($app->user->hasFlash('warning')): ?>
					<div class="alert">
						<strong>Предупреждение:</strong> <?php echo $app->user->getFlash('warning'); ?>
					</div>
				<?php endif; ?>
				<?php if ($app->user->hasFlash('success')): ?>
					<div class="alert alert-success">
						<?php echo $app->user->getFlash('success'); ?>
					</div>
				<?php endif; ?>
				<!-- 
					#
					#	Content
					#
				-->
				<?php echo $content; ?>
			<?php endif; ?>
		</div>
		<!-- 
			#
			#	Dailog "About"
			#
		-->
		<div class="modal hide" id="about">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>О программе</h3>
			</div>
			<div class="modal-body">
				<p><b><?php echo $app->name; ?></b> v<?php echo FILEMAN_VERSION; ?></p>
				<p><?php echo $app->name; ?> on GitHub <a href="https://github.com/troublegum/fileman" targrt="_blank">https://github.com/troublegum/fileman</a></p>
			</div>
		</div>
	</body>
</html>