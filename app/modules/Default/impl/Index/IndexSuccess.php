<div class="container-fluid">
	<div class="row-fluid modules-wrapper">
        <div class="span12">
            <h1><?php echo $tm->_('Available Modules','default.ui') ?></h1>
            <ul class="module-list">
                <li class="module-item">
                    <h3 class="label"><span><?php echo $tm->_('News','default.ui')?></span></h3>
                    <ul class="nav nav-list">
                        <li><a href="<?php echo $ro->gen('news.list'); ?>"><i class="icon-list icon-white"></i>Veredeln</a></li>
                        <li><a href="<?php echo $ro->gen('news.stats'); ?>"><i class="icon-bar-chart"></i>Statistik</a></li>
                    </ul>
                </li>
                <li class="module-item">
                    <h3 class="label"><span><?php echo $tm->_('Orte','default.ui')?></span></h3>
                    <ul class="nav nav-list">
                        <li><a href="<?php echo $ro->gen('shofi.list'); ?>"><i class="icon-list icon-white"></i>Orte</a></li>
                        <li><a href="<?php echo $ro->gen('shofi_categories.list'); ?>"><i class="icon-list icon-white"></i>Branchen</a></li>
                        <li><a href="<?php echo $ro->gen('shofi_verticals.list'); ?>"><i class="icon-list icon-white"></i>Leuchtt&uuml;rme</a></li>
                    </ul>
                </li>
            </ul>
        </div>
	</div>
</div>
