<?php
/**
 * Slap Flag Missing Value Exception
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
final class InvalidValue extends ParseError {
  /** source of invalid value */
  public Arg|Flag $src;
  /** invalid value given */
  public mixed $value;
  /** reason associated with invalidation */
  public string $reason;

  function __construct(
    Context &$ctx,
    Arg|Flag $src,
    mixed    $value,
    string   $reason
  ) {
    $show = json_encode($value);
    if ($show === false) $show = strval($value);
    parent::__construct($ctx, "$src->name = $show $reason");
    $this->src    = $src;
    $this->value  = $show;
    $this->reason = $reason;
  }
}
?>
