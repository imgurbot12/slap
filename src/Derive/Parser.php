<?php
/**
 * Slap Dataclass Command Parser Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Derive
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive;

use Imgurbot12\Slap\Derive\Command;
use Imgurbot12\Slap\Derive\Flag;
use Imgurbot12\Slap\Derive\ParserMap;
use Imgurbot12\Slap\Derive\SubCommands;
use Imgurbot12\Slap\Derive\Errors\DoubleCommand;
use Imgurbot12\Slap\Derive\Errors\InvalidType;
use Imgurbot12\Slap\Derive\Errors\MissingType;

use Imgurbot12\Slap\Args\Boolean;
use Imgurbot12\Slap\Args\Integer;
use Imgurbot12\Slap\Args\Str;
use Imgurbot12\Slap\Command as CommandBuilder;
use Imgurbot12\Slap\Flags\BoolFlag;
use Imgurbot12\Slap\Flags\IntFlag;
use Imgurbot12\Slap\Flags\StrFlag;

const RE_TYPEHINT = '/@var\s+(array<(?:string|int|bool)>|\w+)/';

const ARG_TYPES = [
  'string' => Str::class,
  'bool'   => Boolean::class,
  'int'    => Integer::class,
];

const FLAG_TYPES = [
  'string' => StrFlag::class,
  'bool'   => BoolFlag::class,
  'int'    => IntFlag::class,
];

/**
 * Retrieve Documentation String from Reflection Object
 */
function get_doc(\ReflectionClass|\ReflectionProperty &$ref): ?string {
  $about = $ref->getDocComment();
  if ($about === false) return null;
  $r_about = preg_replace(RE_TYPEHINT, '', $about);
  return trim($r_about ?? $about, "/* \r\n\t\0");
}

/**
 * Retrieve Required Status and Named Type Associated with Reflection Property
 *
 * @return array{bool, bool, string}
 */
function get_type(string $class, \ReflectionProperty &$ref): array {
  $type = $ref->getType();
  if ($type === null) throw new MissingType($class, $ref->getName());
  if (!($type instanceof \ReflectionNamedType)) {
    throw new InvalidType($class, $ref->getName(), strval($type));
  }

  $name = $type->getName();
  $doc  = $ref->getDocComment();
  if ($doc === false) $doc = '';
  preg_match(RE_TYPEHINT, $doc, $matches);
  if (!empty($matches)) $name = $matches[1];

  $repeated = false;
  if (str_starts_with($name, 'array')) {
    $repeated = true;
    $name     = trim(substr($name, 5), '<>');
    if ($name === '') $name = 'string';
  }
  return [!$type->allowsNull(), $repeated, $name];
}

/**
 * Render All Attribute and Instantiate their Associated Classes
 *
 * @param  array<\ReflectionAttribute> $refattrs
 * @return array<string, mixed>
 */
function render_attrs(array $refattrs): array {
  $attrs = [];
  foreach ($refattrs as $attr) $attrs[$attr->getName()] = $attr->newInstance();
  return $attrs;
}

/**
 * Command Application Derivation Parser
 *
 * @api
 */
class Parser {

  /**
   * Build Command Object and Property Mapper from Subclass of Self
   *
   * @param class-string $class
   * @return array{CommandBuilder, ParserMap}
   */
  static function build(string $class): array {
    $map     = new ParserMap($class);
    $ref     = new \ReflectionClass($class);
    $props   = $ref->getProperties();
    $attrs   = render_attrs($ref->getAttributes());

    $command = new CommandBuilder($class);
    $command->about = get_doc($ref) ?? '';
    foreach ($attrs as &$attr) {
      if (!($attr instanceof Command)) continue;
      $command->name    = $attr->name  ?? $command->name;
      $command->about   = $attr->about ?? $command->about;
      $command->version = $attr->version ?? $command->version;
      array_push($command->authors, ...$attr->authors);
      array_push($command->aliases, ...$attr->aliases);
    }

    foreach ($props as &$prop) {
      $name     = $prop->getName();
      $default  = $prop->getDefaultValue();
      $attrs    = render_attrs($prop->getAttributes());
      [$required, $repeated, $type] = get_type($class, $prop);
      $required = ($default === null) && $required;
      $about    = get_doc($prop);

      if (is_subclass_of($type, SubCommands::class, true)) {
        if ($map->command_prop !== null) throw new DoubleCommand($class);
        $map->command_prop  = $name;
        $map->command_class = $type;
        $c_ref       = new \ReflectionClass($type);
        $subcommands = [];
        foreach ($c_ref->getProperties() as &$c_prop) {
          /** @var class-string $type */
          [,$repeat,$type] = get_type($class, $c_prop);
          if ($repeat) throw new InvalidType($class, $ref->getName(), $name);

          [$subcommand, $submap] = static::build($type);
          $subcommand->name  = $c_prop->getName();
          $subcommand->about = get_doc($c_prop) ?? $subcommand->about;
          $subcommands[] = $subcommand;
          $map->command_map[$c_prop->getName()] = $submap;
        }
        $command->subcommands(...$subcommands);
        $command->subcommand_required($required);
        continue;
      }

      foreach ($attrs as &$attr) {
        if (!($attr instanceof Flag)) continue;
        $fclass = FLAG_TYPES[$type] ?? null;
        if ($fclass === null) throw new InvalidType($class, $name, $type);
        $command->flags(new $fclass(
          name:     $name,
          about:    $about,
          short:    $attr->short,
          long:     $attr->long,
          required: $required,
          default:  $default,
          repeat:   $repeated,
          custom:   $attr->custom(),
        ));
        continue 2;
      }

      $aclass = ARG_TYPES[$type] ?? null;
      if ($repeated || $aclass === null) throw new InvalidType($class, $name, $type);
      $command->args(new $aclass(
        name:     $name,
        about:    $about,
        required: $required,
        default:  $default,
      ));
    }
    return [$command, $map];
  }

  /**
   * Try to Parse the Specified Arguments or Return ExitCode on Fail
   */
  static function try_parse(): static|int {
    [$command, $map] = static::build(static::class);
    $result = $command->try_parse();
    if (is_int($result)) return $result;
    return $map->apply($result);
  }

  /**
   * Parse the Specified Arguments or Exit on Failure
   */
  static function parse(): static {
    [$command, $map] = static::build(static::class);
    $result = $command->parse();
    return $map->apply($result);
  }
}

?>
