<!-- BEGIN <?php echo htmlspecialchars(__FILE__) ?> -->
<div class="container" style="margin-top: 40px">
	<div class="content">
		<div class="page-header">
			<h1><?php echo htmlspecialchars($t['_title']); ?></h1>
		</div>
		<div class="row error">
<?php if (! empty($t['url'])) : ?>
			<p class="url">
				URL: <?php echo htmlspecialchars($t['url'])?>
			</p>
<?php endif ?>
<?php if (! empty($t['_module'])) : ?>
			<div class="module">
				<?php echo htmlspecialchars($tm->_('Module').": ".$t['_module'])?>
			</div>
			<div class="action">
				<?php echo htmlspecialchars($tm->_('Action').": ".$t['_action'])?>
			</div>
<?php endif ?>
			<div class="messages">
<?php
	if (! empty($t['errors']) && is_array($t['errors']))
	{
		echo '<dl>';
		foreach ($t['errors'] as $error)
		{
			if ($error instanceof AgaviValidationError)
			{
				echo '<dt>'.htmlspecialchars(implode(', ',$error->getFields())).'</dt>';
				echo '<dd>'.htmlspecialchars($error->getMessage()).'</dd>';
			}
		}
		echo '</dl>';
	}
?>
			</div>
		</div>
	</div>
</div>
<!-- END <?php echo htmlspecialchars(__FILE__) ?> -->
