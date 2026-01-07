<?php
/**
 * Slap Flag/Argument Missing Value Exception
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Errors;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Parse\Context;
use Imgurbot12\Slap\Errors\ParseError;
use Imgurbot12\Slap\Flag;

/**
 *
 */
final class Missing extends ParseError {
  /** @var array<Arg|Flag> source that is missing its value */
  public array $missing;

  /**
   * @param array<Arg|Flag> $missing
   */
  function __construct(Context &$ctx, array $missing) {
    parent::__construct($ctx, "expected values are missing " . print_r($missing, true));
    $this->missing = $missing;
  }
}
?>
