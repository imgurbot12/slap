<?php
/**
 * Slap Boolean Argument Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Arguments
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Args;

use Imgurbot12\Slap\Args\Arg;
use Imgurbot12\Slap\Validate\Validator;
use Imgurbot12\Slap\Validate\Boolean as BooleanV;

/**
 * Boolean Argument Type
 *
 * @extends Arg<bool>
 */
final class Boolean extends Arg {
  #[\Override]
  function validator(): Validator {
    return new BooleanV();
  }
}
?>
