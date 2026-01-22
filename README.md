## slap

A PHP CLI Library Inspired by Rust's CLAP

#### Installation

```bash
$ composer require imgurbot12/slap
```

#### Usage

Slap supports a standard CLI builder mode
which returns an untyped array as a result:

```php
use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

$app = Command::new('myapp')
  ->args(Arg::new('foo')->about('foo description'))
  ->flags(Flag::new('test')->short('t')->default('hello'))
  ->subcommands(
      Command::new('bar')->args(Arg::new('baz')->default('world'));

$result = $app->parse();
print_r($result);
```

It also includes a derivation mode similar to
[clap](https://github.com/clap-rs/clap):

```php
use Imgurbot12\Slap\Derive\Command;
use Imgurbot12\Slap\Derive\Flag;
use Imgurbot12\Slap\Derive\Parser;
use Imgurbot12\Slap\Derive\SubCommands;

#[Command(name: 'bar')]
class Bar {
  public string $baz = 'world';
}

class Commands extends SubCommands {
  public Bar $bar;
}

#[Command(name: 'myapp')]
class MyApp extends Parser {
  /** foo description */
  public string $foo;
  #[Flag(short: 't')]
  public string $test = 'hello';

  public Commands $command;
}

$result = MyApp::parse();
print_r($result);
```
