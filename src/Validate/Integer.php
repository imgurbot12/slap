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

/**
 * @implements Validator<mixed>
 */
final class Integer implements Validator {
  #[\Override]
  function validate($value): bool {
    return $value === null || is_int($value) || ctype_digit($value);
  }
  #[\Override]
  function convert($value): mixed {
    return intval($value);
  }
}
?>
