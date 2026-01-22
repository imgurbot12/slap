<?php
/**
 * Slap Command UnitTests
 */
declare(strict_types=1);
namespace Itatem\Dataclass\Tests;

use PHPUnit\Framework\TestCase;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Errors\Unexpected;

class CommandTest extends TestCase {

  function testSubCommand(): void {
    $app = Command::new('app')
      ->subcommands(Command::new('foo'));

    $res = $app->run([]);
    $this->assertEqualsCanonicalizing([], $res['args']);
    $this->assertEqualsCanonicalizing([], $res['commands']);
    $this->assertEqualsCanonicalizing(['help' => false], $res['flags']);

    $res = $app->run(['foo']);
    $this->assertEqualsCanonicalizing([], $res['args']);
    $this->assertEqualsCanonicalizing(['help' => false], $res['flags']);
    $this->assertCount(1, $res['commands']);
    $this->assertEqualsCanonicalizing([], $res['commands']['foo']['args']);
    $this->assertEqualsCanonicalizing([], $res['commands']['foo']['commands']);
    $this->assertEqualsCanonicalizing(
      ['help' => false], $res['commands']['foo']['flags']);

    $this->expectException(Unexpected::class);
    $this->expectExceptionMessage('unexpected argument "fooo"');
    $app->run(['fooo']);
  }

  function testSubCommandArg(): void {
    $app = Command::new('app')
      ->args(Arg::new('test'))
      ->subcommands(Command::new('foo')->args(Arg::new('test')));

    $res = $app->run(['hello', 'foo', 'world']);
    $this->assertEqualsCanonicalizing(['test' => 'hello'], $res['args']);
    $this->assertEqualsCanonicalizing(
      ['test' => 'world'], $res['commands']['foo']['args']);
  }

  function testSubCommandFlag(): void {
    $app = Command::new('app')
      ->flags(Flag::bool('test'))
      ->subcommands(Command::new('foo')->flags(Flag::new('test')));

    $res = $app->run(['--test', 'foo', '--test', 'world']);
    $this->assertTrue($res['flags']['test']);
    $this->assertEquals('world', $res['commands']['foo']['flags']['test']);
  }
}
?>
