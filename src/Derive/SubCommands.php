<?php
/**
 * Slap SubCommand(s) Derivation Attribute Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Derive
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive;

/**
 * Subcommand Derrivation Designator
 *
 * @api
 */
class SubCommands {

  /**
   * Retrieve First Non Null Command from Self
   */
  public function get(): mixed {
    foreach ($this as $key => $value) {
      if ($value !== null) return $value;
    }
    throw new \Exception('no command set');
  }
}
?>
