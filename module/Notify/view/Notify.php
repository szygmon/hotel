<?php
foreach ($_SESSION['notify'] as $msg):
	?>
	<div class="alert alert-<?= $msg['type'] ?>">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong><?= $msg['title'] ?></strong> <?= $msg['body'] ?>
	</div>

	<?php
endforeach;
?>	
