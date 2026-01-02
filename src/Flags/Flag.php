<?php
/**
 * Slap Abstract Flag Object Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Flags;

use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\FlagRequired;
use Imgurbot12\Slap\Errors\MissingValue;
use Imgurbot12\Slap\Validate\Validator;

/**
 * @template T
 */
abstract class Flag {
  /** name associated with flag */
  public string $name;
  /** short identifier for flag */
  public ?string $short;
  /** long identifier for flag */
  public ?string $long;
  /** declare whether flag is required */
  public bool $required;
  /** @var ?T default value for flag */
  public mixed $default;

  /** whether flag always expects a value */
  public bool $requires_value = true;
  /** allow flag to be repeated multiple times */
  public bool $repeat = false;
  /** internal validator implementation */
  private Validator $validator;

  //TODO: validaters for short/long and requiring one or the other
  //TODO: required vs default being exclusive

  /**
   * @param ?T $default
   */
  function __construct(
    string  $name,
    ?string $short    = null,
    ?string $long     = null,
    bool    $required = false,
    mixed   $default  = null,
  ) {
    $this->name      = $name;
    $this->short     = $short;
    $this->long      = $long ?? $name;
    $this->required  = $required;
    $this->default   = $default;
    $this->validator = $this->validator();
  }

  abstract function validator(): Validator;

  /**
   * @param array<Command> $path
   */
  function finalize(array $path, mixed $value): mixed {
    echo "$this->name $value\n";
    if ($value === '<__flag_missing>') {
      if ($this->required) throw new FlagRequired($path, $this);
      return $this->default;
    }
    if ($value === null && $this->requires_value) {
      throw new MissingValue($path, $this);
    }
    if (!$this->validator->validate($value)) {
      throw new InvalidValue($path, $this, $value);
    }
    return $this->validator->convert($value);
  }
}
?>
