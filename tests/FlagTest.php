<?php
/**
 * Slap Flag UnitTests
 */
declare(strict_types=1);
namespace Itatem\Dataclass\Tests;

use PHPUnit\Framework\TestCase;

use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Errors\Invalid;
use Imgurbot12\Slap\Errors\Missing;
use Imgurbot12\Slap\Errors\Unexpected;

class FlagTest extends TestCase {

  function testSimple(): void {
    $app = Command::new('app')
      ->flags(Flag::new('test'));
    $result = $app->run([]);
    $this->assertEqualsCanonicalizing([], $result['args']);
    $this->assertEqualsCanonicalizing([], $result['commands']);
    $this->assertCount(2, $result['flags']);
    $this->assertArrayHasKey('test', $result['flags']);
    $this->assertNull($result['flags']['test']);
    $this->assertFalse($result['flags']['help']);

    $result = $app->run(['--test', 'helloworld']);
    $this->assertEqualsCanonicalizing([], $result['args']);
    $this->assertEqualsCanonicalizing([], $result['commands']);
    $this->assertCount(2, $result['flags']);
    $this->assertFalse($result['flags']['help']);
    $this->assertArrayHasKey('test', $result['flags']);
    $this->assertEquals('helloworld', $result['flags']['test']);

    $this->expectException(Unexpected::class);
    $this->expectExceptionMessage('unexpected argument "--test"');
    Command::new('app')->run(['--test', 'helloworld']);
  }

  function testShort(): void {
    $result = Command::new('app')
      ->flags(Flag::new('test')->short('s'))
      ->run(['-s', 'hello']);
    $this->assertEquals('hello', $result['flags']['test']);
    $this->expectException(Unexpected::class);
    $this->expectExceptionMessage('unexpected argument "-s"');
    Command::new('app')
      ->flags(Flag::new('test')->default('hello'))
      ->run(['-s', 'hello']);
  }

  function testRequired(): void {
    $app = Command::new('app')
      ->flags(Flag::new('test')->required(true));

    $result = $app->run(['--test', 'hello']);
    $this->assertEquals('hello', $result['flags']['test']);
    $this->expectException(Missing::class);
    $app->run([]);
  }

  function testDefault(): void {
    $app = Command::new('app')
      ->flags(Flag::new('test')->default('hello'));

    $result = $app->run([]);
    $this->assertEquals('hello', $result['flags']['test']);
    $result = $app->run(['--test', 'world']);
    $this->assertEquals('world', $result['flags']['test']);
  }

  function testBoolean(): void {
    $app = Command::new('app')
      ->flags(Flag::bool('test'));

    $result = $app->run([]);
    $this->assertFalse($result['flags']['test']);
    $result = $app->run(['--test']);
    $this->assertTrue($result['flags']['test']);
    $this->expectException(Invalid::class);
    $app->run(['--test', 'hello']);
  }

  function testInteger(): void {
    $app = Command::new('app')
      ->flags(Flag::int('test'));

    $result = $app->run([]);
    $this->assertNull($result['flags']['test']);
    $result = $app->run(['--test', '1234']);
    $this->assertSame(1234, $result['flags']['test']);
    $this->expectException(Invalid::class);
    $app->run(['--test', '123hello']);
  }

  function testRepeat(): void {
    $result = Command::new('app')
      ->flags(Flag::new('test')->repeat(true))
      ->run(['--test', 'hello', '--test', 'world']);
    $this->assertEqualsCanonicalizing(
      ['hello', 'world'], $result['flags']['test']);

    $this->expectException(Unexpected::class);
    Command::new('app')
      ->flags(Flag::new('test'))
      ->run(['--test', 'hello', '--test', 'world']);
  }

  function testCustomValidator(): void {
    $app = Command::new('app')
      ->flags(Flag::new('test')
        ->validate(fn ($v) => $v === 'hello', 'invalid hello'));

    $result = $app->run([]);
    $this->assertNull($result['flags']['test']);
    $result = $app->run(['--test', 'hello']);
    $this->assertEquals('hello', $result['flags']['test']);
    $this->expectException(Invalid::class);
    $this->expectExceptionMessage('test = "world" invalid hello');
    $app->run(['--test', 'world']);
  }
}
?>
