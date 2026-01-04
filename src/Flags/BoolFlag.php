<?php
/**
 * Slap Boolean Flag Object Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Flags;

use Imgurbot12\Slap\Flags\Flag;
use Imgurbot12\Slap\Validate\Boolean;
use Imgurbot12\Slap\Validate\Validator;

/**
 * Boolean Flag Object Definition
 *
 * @extends Flag<bool>
 */
final class BoolFlag extends Flag {
  function __construct(
    string  $name,
    ?string $about      = null,
    ?string $short      = null,
    ?string $long       = null,
    bool    $required   = false,
    ?bool   $default    = false,
    bool    $allow_null = true,
  ) {
    parent::__construct($name, $about, $short, $long, $required, $default);
    $this->requires_value = !$allow_null;
  }

  #[\Override]
  function validator(): Validator {
    return new Boolean();
  }
}
?>
