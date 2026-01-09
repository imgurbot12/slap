<?php
/**
 * Slap Flag Derivation Attribute Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Derive
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive;

use Imgurbot12\Slap\Validate\Custom;

/**
 *
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Arg {
  public ?\Closure $validator;
  public ?string   $reason;

  function __construct(
    ?Callable $validator = null,
    ?string   $reason    = null,
  ) {
    $this->reason    = $reason;
    $this->validator = ($validator !== null)
      ? \Closure::fromCallable($validator)
      : null;
  }

  /**
   * Build Custom Validators (if any configured)
   *
   * @return ?array<Custom>
   */
  function custom(): ?array {
    if ($this->validator === null) return null;
    $reason = $this->reason ?? 'invalid value';
    return [new Custom($this->validator, $reason)];
  }
}
?>
