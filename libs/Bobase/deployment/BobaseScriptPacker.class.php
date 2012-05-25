<?php

/**
 * The BobaseScriptPacker packs an compresses js and css scripts.
 *
 * @version         $Id: BobaseScriptPacker.class.php 1063 2012-04-05 11:59:16Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Bobase
 * @subpackage      Deployment
 */
class BobaseScriptPacker
{
    public function pack(array $files, $type, $baseDir = NULL)
    {
        $combined = '';

        foreach ($files as $file)
        {
            $path = ($baseDir) ? ($baseDir . DIRECTORY_SEPARATOR . $file) : $file;
            if (! is_readable($path))
            {
                throw new Exception(
                    "File " . $file . " is not readable. If you tried to provide an url,
                    please notice that packing is only available for local files."
                );
            }

            $combined .= file_get_contents($path) . "\n\n\n";
        }

        return $this->compressScript($combined, $type);
    }

    protected function compressScript($source, $type)
    {
        $outfile = tempnam(sys_get_temp_dir(), 'compress.'.$type);
        $dev_dir = dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'dev';
        $jar = sprintf('%s/yuicompressor-2.4.6/build/yuicompressor-2.4.6.jar', $dev_dir);

        if (! file_exists($jar))
        {
            throw new Exception('YUICompressor binary cannot be found in "'.$jar.'".');
        }

        $cmd = sprintf(
            'java -Xmx256M -jar %s --charset %s --type %s -o %s',
            $jar,
            escapeshellarg('utf-8'),
            $type,
            escapeshellarg($outfile)
        );

        if (FALSE === ($handle = popen($cmd, 'w')))
        {
            throw new Exception(sprintf('Unable to process file %s.', $outfile));
        }

        fwrite($handle, $source);
        pclose($handle);

        $contents = file_get_contents($outfile);
        unlink($outfile);

        return $contents;
    }
}

?>
