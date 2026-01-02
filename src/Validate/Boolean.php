<?php
/**
 * Slap Boolean Validator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Validate;

use Imgurbot12\Slap\Validate\Validator;

const B_TRUE  = ['1', 'true', 'y', 'ye', 'yes'];
const B_FALSE = ['0', 'false', 'n', 'na', 'no'];

/**
 * @implements Validator<mixed>
 */
final class Boolean implements Validator {
  #[\Override]
  function validate($value): bool {
    if ($value === null || is_bool($value)) return true;
    if (in_array($value, B_TRUE)) return true;
    if (in_array($value, B_FALSE)) return true;
    return false;
  }
  #[\Override]
  function convert($value): mixed {
    if ($value === null || in_array($value, B_TRUE)) return true;
    if (in_array($value, B_FALSE)) return false;
    return boolval($value);
  }
}
?>
