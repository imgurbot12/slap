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
  /** label help as resolved (should not count as error) */
  public ?int $exitcode;

  /**
   * @param array<string> $path
   */
  function __construct(
    Context $ctx,
    array   $path,
    ?string $reason   = null,
    ?int    $exitcode = null,
  ) {
    parent::__construct($reason ?? 'help requested');
    $this->ctx      = $ctx;
    $this->path     = $path;
    $this->exitcode = $exitcode;
  }
}
?>
