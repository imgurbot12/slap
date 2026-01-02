<?php
/**
 * Slap String Flag Object Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Flags;

use Imgurbot12\Slap\Flags\Flag;
use Imgurbot12\Slap\Validate\Str;
use Imgurbot12\Slap\Validate\Validator;

/**
 * String Flag Object Definition
 *
 * @extends Flag<int>
 */
final class StrFlag extends Flag {
  #[\Override]
  function validator(): Validator {
    return new Str();
  }
}
?>
