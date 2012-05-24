<?php

/**
 * Data file for timezone "Pacific/Guam".
 * Compiled from olson file "australasia", version 8.27.
 *
 * @package    agavi
 * @subpackage translation
 *
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id: Pacific_47_Guam.php 4822 2011-10-07 20:52:10Z david $
 */

return array (
  'types' => 
  array (
    0 => 
    array (
      'rawOffset' => 34740,
      'dstOffset' => 0,
      'name' => 'LMT',
    ),
    1 => 
    array (
      'rawOffset' => 36000,
      'dstOffset' => 0,
      'name' => 'GST',
    ),
    2 => 
    array (
      'rawOffset' => 36000,
      'dstOffset' => 0,
      'name' => 'ChST',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -3944626740,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -2177487540,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => 977493600,
      'type' => 2,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'static',
    'name' => 'ChST',
    'offset' => 36000,
    'startYear' => 2001,
  ),
  'source' => 'australasia',
  'version' => '8.27',
  'name' => 'Pacific/Guam',
);

?>