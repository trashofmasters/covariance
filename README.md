# Covariance for PHP

The type system of PHP, in particular its object model, does not allow subclasses to _refine_ the type of type hinted arguments.

Therefore, when a parent class declares a method which type hints to some abstract data type, the subclasses cannot redefine this constraints to be more specific type.

The purpose of this library is to work within the boundary of what PHP allows to implement parameter and return type covariance, as well as contravariance.

For example, the following code produces a fatal error "_Declaration of AnyOtherVet::help should be compatible with Vet::help(Animal)._":

> ```php
> class Animal {}
>
> class Cat extends Animal {}
> class Dog extends Animal {}
>
> abstract class Vet {
>   public function help(Animal $a) {}
> }
>
> class AnyOtherVet extends Vet {
>   public function help(Animal $a) {}
> }
>
> class BetterVet extends Vet {
>   public function help(Cat $a) {}
> }
> ```
> <small>Example 1. **Invalid PHP code**: cannot redeclare the same method with a different signature.</small>

This concept is formally known as **covariance**. A method is covariant only if it accepts types that are subclasses of the type hinted in the parent definition – in the above example `BetterVet::help(Cat $c)` is covariant to `Vet::help(Animal $a)`.

Covariance enables us to write object-oriented that more closely model real world scenarios, as well as to reduce the boilerplate code required in all applications that do work around this limitation of the language in some alternative way.


In example 1, we've seen what a covariant method would look like if PHP supported covariance.

Unfortunately there's no simple way we can extend the language to support this syntax without an unnecessary compilation step, however, using a few conventions it should become possible to achieve a smiliar result.

# Covariance Conventions

With this library it becomes possible to achieve method covariance by declaring your base method as covariant:

> ```php
> class Vet {
>   public function help(Animal $animal) {
>     return covariant($this, function(Animal $animal) {
>       // Base method behaviour:
>       // Make $animal healthy again.
>       echo "Your pet is healthy again." . PHP_EOL;
>     });
>   }
> }
>
> class BetterVet extends Vet {
>   public function helpCat(Cat $cat) {
>     // Make $cat healthy again.
>     echo "Your kitty is healthy again." . PHP_EOL;
>   }
> }
> ```
> <small>Example 2. Covariant method declaration.</small>

> ```php
> function visit(Vet $vet, Animal $pet) {
>    return $vet->help($animal);
> }
>
> visit(new BetterVet, new Cat); // calls BetterVet::helpCat(Cat)
> visit(new BetterVet, new Dog); // Invalid call: BetterVet only helps cats.
>
> visit(new AnyOtherVet, new Cat); // calls Vet::help(Animal)
> visit(new AnyOtherVet, new Dog); // calls Vet::help(Animal)
> ```
> <small>Example 3. </small>

# TODO Enabling contravariance
