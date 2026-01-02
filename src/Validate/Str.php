<?php
/**
 * Slap String Validator Implementation
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
final class Str implements Validator {
  #[\Override]
  function validate($value): bool {
    return $value === null || is_string($value);
  }
  #[\Override]
  function convert($value): mixed {
    return strval($value);
  }
}
?>
