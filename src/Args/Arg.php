<?php
/**
 * Slap Abstract Argument Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Arguments
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Args;

use Imgurbot12\Slap\Errors\MissingValue;
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Validate\Validator;

use Imgurbot12\Slap\Args\Boolean;
use Imgurbot12\Slap\Args\Integer;
use Imgurbot12\Slap\Args\Str;

/**
 * Abstract Argument Implementation
 *
 * @template T
 */
abstract class Arg {
  /** name of argument */
  public string $name;
  /** @var ?T default value for argument */
  public mixed $default;

  /** internal validator implementation */
  private Validator $validator;

  /**
   * @param ?T $default
   */
  function __construct(string $name, $default = null) {
    $this->name      = $name;
    $this->default   = $default;
    $this->validator = $this->validator();
  }

  /**
   * Retrieve Associated Validator for Argument Type
   */
  abstract function validator(): Validator;

  /**
   * Build new String Argument
   */
  static function new(string $name): Str {
    return new Str($name);
  }

  /**
   * Build new Boolean Argument
   */
  static function bool(string $name): Boolean {
    return new Boolean($name);
  }

  /**
   * Build new Integer Argument
   */
  static function int(string $name): Integer {
    return new Integer($name);
  }

  /**
   * Builder Method to Set Default Value
   *
   * @param ?T $default
   */
  function default($default): self {
    $this->default = $default;
    return $this;
  }

  /**
   * Validate and Finalize Argument Value for Parsing Result
   *
   * @param array<Command> $path
   */
  function finalize(array $path, mixed $value): mixed {
    $value ??= $this->default;
    if ($value === null) throw new MissingValue($path, $this);
    if (!$this->validator->validate($value)) {
      throw new InvalidValue($path, $this, $value);
    }
    return $this->validator->convert($value);
  }
}
?>
