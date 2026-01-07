<?php
/**
 * List Validator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Validation
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Validate;

/**
 * Repeated/List Validator Implementation
 *
 * @implements Validator<array<mixed>>
 */
final class Repeated implements Validator {
  /** inner item validator */
  public Validator $inner;

  /**
   * @param Validator $validator
   */
  function __construct(Validator $validator) {
    $this->inner = $validator;
  }

  #[\Override]
  function validate($value): bool {
    foreach ($value as &$v) {
      if (!($this->inner->validate($v))) return false;
    }
    return true;
  }
  #[\Override]
  function convert($value): mixed {
    return array_map(fn ($v): mixed => $this->inner->convert($v), $value);
  }
}
?>
