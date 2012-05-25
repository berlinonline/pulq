<?php

/**
 * The BobaseFileSystemRegexpIterator lets you traverse files on the file system thereby applying
 * a given regexp to iterate only files that the expression.
 *
 * @version         $Id: BobaseDirectoryRegexpIterator.class.php 1024 2012-03-03 15:35:16Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Bobase
 * @subpackage      Iterator
 */
class BobaseDirectoryRegexpIterator extends FilterIterator
{
    const REGEXP_DELIMITER = '~';

    protected $filterRegexp;

    public function __construct($directoryPath, $filterRegexp)
    {
        parent::__construct(
            new FilesystemIterator(
                $directoryPath,
                FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
            )
        );

        $this->filterRegexp = sprintf(
            '%1$s%2$s%1$sis',
            self::REGEXP_DELIMITER,
            $filterRegexp
        );

        // Make sure that we are valid for fresh instances.
        // Otherwise while($it->valid()) { $it->next(); } will skip the first item.
        $this->rewind();
    }

    public function accept()
    {
        $fileName = basename($this->getInnerIterator()->current());
        return (0 < preg_match($this->filterRegexp, $fileName));
    }

    public function getMTime()
    {
        return $this->getInnerIterator()->getMTime();
    }
}

?>
