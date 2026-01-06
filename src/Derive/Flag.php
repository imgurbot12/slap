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

/**
 *
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Flag {
  public ?string $short;
  public ?string $long;

  function __construct(?string $short = null, ?string $long = null) {
    $this->short = $short;
    $this->long  = $long;
  }
}
?>
