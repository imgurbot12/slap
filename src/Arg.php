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
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Args\Boolean;
use Imgurbot12\Slap\Args\Integer;
use Imgurbot12\Slap\Args\Str;

use Imgurbot12\Slap\Validate\Custom;
use Imgurbot12\Slap\Validate\Validator;

/**
 * Abstract Argument Implementation
 *
 * @template T
 */
abstract class Arg {
  /** name of argument */
  public string $name;
  /** usage description tied to argument */
  public string $about;
  /** declare whether arg is required */
  public bool $required;
  /** @var ?T default value for argument */
  public mixed $default;

  /** @var array<Custom> custom validators for the arg */
  public array $custom;
  /** internal validator implementation */
  readonly public Validator $validator;

  /**
   * @param ?T            $default
   * @param array<Custom> $custom
   */
  function __construct(
    string  $name,
    ?string $about    = null,
    mixed   $default  = null,
    bool    $required = true,
    ?array  $custom   = null
  ) {
    $this->name      = $name;
    $this->about     = $about ?? '';
    $this->required  = ($default !== null) || $required;
    $this->default   = $default;
    $this->custom    = $custom ?? [];
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
   * Builder Method to Modify Arg About
   */
  function about(string $about): self {
    $this->about = $about;
    return $this;
  }

  /**
   * Builder Method to Set Default Value
   *
   * @param ?T $default
   */
  function default($default): self {
    $this->required = false;
    $this->default  = $default;
    return $this;
  }

  /**
   * Apply a Custom Validator to the Argument
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
