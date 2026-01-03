<?php
/**
 * Slap Standard Ansi Escape Colorization
 *
 * @package   Imgurbot12/Slap
 * @category  Colors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Colors;

use Imgurbot12\Slap\Colors\Colors;

/**
 * Ansi Terminal Escape Colorization
 */
class Ansi extends Colors {
  const ERROR     = "\033[31;1m%s\033[0m";
  const WARNING   = "\033[33m%s\033[0m";
  const HIGHLIGHT = "\033[32m%s\033[0m";
  const UNDERLINE = "\033[4;1m%s\033[0m";
  const STANDARD  = '%s';
}
?>
