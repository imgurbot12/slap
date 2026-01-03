<?php
/**
 * Slap Parser Error Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Errors;

use Imgurbot12\Slap\Parse\Context;

/**
 *
 */
class ParseError extends \Exception {
  /** context of command/argument construction */
  public Context $ctx;

  /**
   * @param Context $ctx
   */
  function __construct(Context &$ctx, string $message) {
    parent::__construct($message);
    $this->ctx = $ctx;
  }
}
?>
