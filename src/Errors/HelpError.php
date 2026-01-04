<?php
/**
 * Slap Help Exception
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
 * Help Event Exception
 */
final class HelpError extends \Exception {
  /** context associated with help request */
  public Context $ctx;
  /** @var array<string> path requested for help */
  public array $path;

  /**
   * @param array<string> $path
   */
  function __construct(Context $ctx, array $path) {
    parent::__construct("help requested");
    $this->ctx  = $ctx;
    $this->path = $path;
  }
}
?>
