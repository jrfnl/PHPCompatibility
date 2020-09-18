<?php

/*
 * Valid namespaced names cross-version.
 */
namespace Foo\Bar;

use Bar\Foo;
use function Bar\Baz\foo_function;
use Const Bar\Foo\Foo\FOO_CONST;

use UnqualifiedClassName,
    Sublevel\PartiallyClassName,
    \Fully\Qualified\ClassName; // Leading backslash is unnecessary, but not invalid.

use some\namespaceA \{ClassA, ClassB, ClassC as C};
use Function some\namespaceB\ {fn_a, fn_b, fn_c};
use const some\namespaceC \ {ConstA, ConstB, ConstC};

use Bar\Foo\Baz\{
    Rab\ClassName,
    FUNCTION Oof\Rab\function_name as alias,
    const Oof\CONSTANT_NAME,
};

use \Bar\Foo; // Leading backslash is unnecessary, but not invalid.

class Valid extends namespace\FooClass implements \Vendor\Package\FooInterface, Package\BarInterface
{
    use Valid\FooTrait;

    use namespace\MyTrait,
        \Vendor\Package\AnotherTrait
    {
        \Vendor\Package\AnotherTrait::smallTalk insteadof MyTrait;
        MyTrait::bigTalk insteadof \Vendor\Package\AnotherTrait;
        \Vendor\Package\AnotherTrait::bigTalk as talk;
    }

    function withTypes( My\Named\Type $paramA, namespace\Name|\Fully\Qualified $paramB )
        : \Vendor\ReturnType|Partially\Qualified
    {
        echo unqualified();
        echo partially\qualified();
        echo \fully\qualified();
        echo namespace\relative();

        echo unqualified::CONSTANT;
        echo partially\qualified::$prop;
        echo \fully\qualified::static_function();
        array_map(\Callback::class, $array);

        if ($a instanceof Unqualified
            || $b instanceof Partially\Qualified
            || $c instanceof \Fully\Qualified
            || $d instanceof namespace\Relative
        ) {}

        $obj = new Unqualified();
        $obj = new Comment\Doc($docComment);
        $obj = new \Fully\Qualified;
        $obj = new namespace\Relative();

        try {
        } catch ( UnqualifiedException | PartiallyQualified\Exception $e) {
        } catch ( \Fully\Qualified\Exception | namespace\Exception $e) {
        }
    }

    function generator() {
        if ($a) {
            yield \Fully\Qualified::CONSTANT_ARRAY;
        } else {
            yield from \Fully\Qualified\function_call();
        }
    }

    function noFalsePositivesForNoSpaceBeforeFQN() {
        $a =\FQN;
        $a +=\FQN;
        $a **=\FQN+\FQN-\FQN*\FQN^\FQN|\FQN>>\FQN;
        $a = (int)\FQN;
        $a = (string)\FQN;
        $a = (object)\FQN;
        $a = (int)\FQN;
        $a = [\FQN];
        $a = ['key'=>\FQN];
        if (\FQN!==\FQN&&\FQN<>\FQN||\FQN<=>\FQN&&\FQN==\FQN) {}
    }
}

interface ValidInterface extends FooInterface, Package\BarInterface {}

class B implements/*comment*/\A {}


/*
 * PHP 8.0: whitespace and comments not allowed within names and names *must* be preceded by whitespace or comment.
 */

namespace MyNS \  WhiteSpace\
    NewLine\ NotOK;

use OtherVendor  \OtherPackage\   BazClass;

use \Bar \Foo; // Leading backslash is unnecessary, but not invalid.

use Sublevel \UnqualifiedClassName as Another,
    Sublevel\ PartiallyClassName,
    \Fully\ Qualified\ClassName; // Leading backslash is unnecessary, but not invalid.

use function My\Full/*comment*/\functionName as func;
use Const Bar\Foo \Foo\FOO_CONST;

use Function\My\Full\function_name; // Tricky one: interpreted differently in PHP 5/7 vs 8.
use const\My\Full\CONSTANT_NAME; // Tricky one: interpreted differently in PHP 5/7 vs 8.

use some \namespaceA \{
    ClassA,
    Part \ClassB,
    Sub\level \ClassC as C
};
use Function some\ namespaceB\ {
    Part\ fn_a,
    fn_b,
    Sub \level\fn_c
};
use const some \namespaceC \ {
    Sub\level\ ConstA,
    Part\ ConstB,
    ConstC
};

use Bar\ Foo\Baz\{
    Rab \ClassName,
    FUNCTION Oof\Rab\ function_name as alias,
    const Oof /*comment*/\CONSTANT_NAME,
};

class Invalid extends \ MyNamespace \ /* comment */ Something \    Other \
    Deeper \ ClassName implements \ MyNamespace \ /* comment */ Something \    Other \
    Deeper \ InterfaceName, Package \ BarInterface
{
    use InValid \FooTrait;

    use namespace\ MyTrait,
        \Vendor\Package\ AnotherTrait
    {
        \Vendor\ Package\AnotherTrait::smallTalk insteadof MyTrait;
        MyTrait::bigTalk insteadof\ Vendor\Package\AnotherTrait;
        \Vendor\Package \AnotherTrait::bigTalk as talk;
    }

    public function withTypes(
        My\ Named\Type $paramA,
        namespace \Name|\Fully\Qualified $paramB // Note: no error should be thrown for no space before name.
    ): \Vendor\/*comment*/ReturnType|Partially\ Qualified {
        $a = new \SomeNS \Sub\  ClassName();
        $b = \SomeNS \Sub\  ClassName :: $staticVar;
        $c = \SomeNS \Sub\  ClassName :: staticMethod();
        $d =\SomeNS \Sub\  ClassName :: CLASS_CONSTANT; // Note: no error should be thrown for no space before name.
        $e = SubNS \ DeeperSub \ NAMESPACED_CONSTANT;
        $f = SubNS \ DeeperSub \ namespaced_function();

        echo namespace \relative();
        echo namespace\ cname::method();
        $a = new namespace\sub \cname();
        $b = namespace\ CONSTANT_NAME;

        array_map(\ Callback::class, $array);

        if ($a instanceof Unqualified
            || $b instanceof Partially \Qualified
            || $c instanceof \Fully\ Qualified
            || $d instanceof namespace \Relative
        ) {}

        try {
        } catch ( UnqualifiedException $e) {
        } catch ( PartiallyQualified \Exception $e) {
        } catch (\Fully\ Qualified\Exception $e) { // Note: no error should be thrown for no space before name.
        } catch ( namespace \Exception $e) {
        }
    }

    public function get($classname)
    {
        $a = __NAMESPACE__ . ' \\ ' . $classname; // Ignored.
        return new $a();
    }

    function generator() {
        if ($a) {
            yield \ Fully\Qualified::CONSTANT_ARRAY;
        } else {
            yield from \Fully\ Qualified\function_call();
        }
    }
}

interface ValidInterface extends namespace \FooInterface, Package\ BarInterface {}

// Namespace operator, not declaration.
namespace \ Sublevel
          \ function_name();

$value = \Fully
    // phpcs:ignore Stnd.Cat.Sniff -- for reasons
    \Qualified
    /* comment */
    \Name
    // comment
    :: function_name();

// Double backslash. Intentional parse error. Ignore.
$obj = new \\SomeClass();


// No longer considered legal in PHP 8.
class B implements\A {} // **8
interface Foo extends\Bar {} // **8
$a = $c instanceof\Fully\Qualified;
function generator() {
    if ($a) {
        yield\Fully\Qualified::CONSTANT_ARRAY;
    } else {
        Yield From\Fully\Qualified\function_call(); // **8
    }
}

// Real life example from the RFC:
$escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($value) {
    throw new\ RuntimeException(sprintf($phrase, $parameter, $value, \gettype($resolved)));
}, $value);

// Some specific tests against false positives when reserved keywords are used in places
// where they are allowed in PHP 7 (class constant) or PHP 8 (namespace declaration).

namespace const\Bar;
namespace Foo\include\Bar;

class Foobar { const EXTENDS = 1; }

// Also check spacing before ns separator - if a keyword other than `namespace`, spacing should probably be added.
// Note: this will most likely need context as otherwise we'll run into problems with reserved keywords used in namespace
// names in combination with PHP < 8 + PHPCS < 3.5.7.
// For PHPCS 3.5.7 or higher, we should be ok (I think ?).
