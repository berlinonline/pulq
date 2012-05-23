<?xml version="1.0" encoding="utf-8" ?>
<?php
$locale = $tm->getCurrentLocale();
$dir = (($locale->getCharacterOrientation() == 'right-to-left') ? 'rtl' : 'ltr');
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" lang="<?php echo $locale->getLocaleLanguage(); ?>" dir="<?php echo $dir; ?>">
    <head>
        <title><?php echo htmlspecialchars($t['_title']); ?></title>
        <base href="<?php echo $ro->getBaseHref(); ?>" id="base_href"/>
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    </head>
    <body>
        <?php echo $slots['header']; ?>

        <?php echo $inner; ?>

        <footer class="container-fluid">
            <p>&#169;BerlinOnline 2012</p>
        </footer>
    </body>
</html>