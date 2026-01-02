<?php
/**
 * Slap Abstract Argument Object Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Arguments
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Args;

/**
 *
 */
abstract class Argument {

  /**
   *
   */
  abstract function validate(?string $value): mixed;
}
?>
