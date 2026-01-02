<?php
/**
 * Slap Integer Argument Implementation
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
use Imgurbot12\Slap\Validate\Integer as IntegerV;

/**
 * Integer Argument Type
 *
 * @extends Argument<bool>
 */
final class Integer extends Arg {
  function validator(): Validator {
    return new IntegerV();
  }
}
?>
