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
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Validate\Custom;
use Imgurbot12\Slap\Validate\Validator;

use Imgurbot12\Slap\Flags\BoolFlag;
use Imgurbot12\Slap\Flags\IntFlag;
use Imgurbot12\Slap\Flags\StrFlag;

/**
 * @template T
 */
abstract class Flag {
  /** name associated with flag */
  public string $name;
  /** usage description tied to flag */
  public string $about;
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
  /** @var array<Custom> custom validators for the flag */
  public array $custom;
  /** internal validator implementation */
  public readonly Validator $validator;

  /**
   * @param ?T $default
   */
  function __construct(
    string  $name,
    ?string $about    = null,
    ?string $short    = null,
    ?string $long     = null,
    bool    $required = false,
    mixed   $default  = null,
  ) {
    $this->name      = $name;
    $this->about     = $about ?? '';
    $this->short     = $short;
    $this->long      = $long ?? $name;
    $this->required  = $required;
    $this->default   = $default;
    $this->custom    = [];
    $this->validator = $this->validator();
  }

  /**
   * @return array<string>
   */
  function __flags(): array {
    $flags = ["--$this->long"];
    if ($this->short !== null) $flags[] = "-$this->short";
    return $flags;
  }

  /**
   * Flag Value Type Validation and Processor
   */
  abstract function validator(): Validator;

  /**
   * Build new String Flag
   */
  static function new(string $name): StrFlag {
    return new StrFlag($name);
  }

  /**
   * Build new Boolean Flag
   */
  static function bool(string $name): BoolFlag {
    return new BoolFlag($name);
  }

  /**
   * Build new Integer Flag
   */
  static function int(string $name): IntFlag {
    return new IntFlag($name);
  }

  /**
   * Builder Method to Modify Flag About
   */
  function about(string $about): self {
    $this->about = $about;
    return $this;
  }

  /**
   * Builder Method to Modify Short Flag
   */
  function short(string $short): self {
    $this->short = $short;
    return $this;
  }

  /**
   * Builder Method to Modify Long Flag
   */
  function long(string $long): self {
    $this->long = $long;
    return $this;
  }

  /**
   * Builder Method to Set Required
   */
  function required(bool $required): self {
    $this->required = $required;
    return $this;
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
   * Apply a Custom Validator to the Flag
   *
   * @param callable(?string):bool $validator
   * @param string                 $reason
   */
  function validate(callable $validator, string $reason): self {
    $this->custom[] = new Custom($validator, $reason);
    return $this;
  }
}
?>
