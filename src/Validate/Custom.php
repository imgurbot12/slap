<?php
/**
 * Custom Value Validator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Validate;

/**
 * Custom Function based Validator
 *
 * @implements Validator<mixed>
 */
final class Custom implements Validator {
  /** \Closure(?string):bool */
  public \Closure $validator;
  /** error reason on validation error */
  public string $reason;

  /**
   * @param callable(?string):bool $validator
   * @param string                 $reason
   */
  function __construct(callable $validator, string $reason) {
    $this->validator = \Closure::fromCallable($validator);
    $this->reason    = $reason;
  }

  #[\Override]
  function validate($value): bool {
    return ($this->validator)($value);
  }
  #[\Override]
  function convert($value): mixed {
    return $value;
  }
}
?>
