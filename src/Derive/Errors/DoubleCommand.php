<?php
/**
 * Slap Derive Property Double Subcommand Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive\Errors;

use Imgurbot12\Slap\Derive\Errors\DeriveError;

/**
 *
 */
final class DoubleCommand extends DeriveError {

  function __construct(string $class) {
    parent::__construct("$class must only have one subcommand property");
  }
}
?>
