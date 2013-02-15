<div class="container-fluid wrapper">
	<div class="row-fluid">
        <div class="span12">
            <h1>404 - Not Found</h1>
            <hr />
<?php 
	if (! empty($t['url']))
	{
?>
            <p class="status-message">
            	Unterhalb der Url: <i><?php echo htmlspecialchars($t['url'])?></i> ist keine Resource verf&uuml;gbar. 
            </p>
<?php 
	}
?>
            <hr />
        </div>
	</div>
	<div class="info-box well">
		<h4>Details:</h4>
<?php
	if (empty($t['errors']) && empty($t['_module']))
	{
?>
		<p>Die Url hat keine der definierten Routen getroffen.</p>
<?php
	}

	if (! empty($t['errors']) && is_array($t['errors']))
	{
?>
		<dl>
<?php
		foreach ($t['errors'] as $error)
		{
			if ($error instanceof AgaviValidationError)
			{
?>
			<dt><?php echo htmlspecialchars(implode(', ',$error->getFields())); ?></dt>
			<dd><?php echo htmlspecialchars($error->getMessage()); ?></dd>
<?php
			}
		}
?>
		</dl>
<?php
	}
	
	if (! empty($t['_module']))
	{
?>
		<p>
			Die Url trifft zu, doch die resultierende "Module/Action" Kombination konnte nicht geladen werden.
		</p>
        <p>
        	Module: <b><?php echo htmlspecialchars($t['_module']); ?></b>
        	Action: <b><?php echo htmlspecialchars($t['_action']); ?></b>
        </p>
<?php
	}
?>
    </div>
    <div class="push"></div>
</div>
