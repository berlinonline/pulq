<?php

namespace phpDocumentor\Composer;

use Composer\Package\PackageInterface;

class UnifiedAssetInstaller extends \Composer\Installer\LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        if (substr($package->getPrettyName(), 0, 23) != 'phpdocumentor/template-') {
            throw new \InvalidArgumentException(
                'Unable to install template, phpdocumentor templates should always start their package name with "phpdocumentor/template."'
            );
        }
        return 'data/templates/'.substr($package->getPrettyName(), 23);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
      return (bool)('phpdocumentor-template' === $packageType);
    }
}
