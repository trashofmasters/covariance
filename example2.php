<?php

require 'vendor/autoload.php';

class LivingBeing {}
class Animal extends LivingBeing {}
class Cat extends Animal {}
class Dog extends Animal {}

class Vet {
    public function help(Animal $animal) {
        return covariant($this, function (Animal $a) {
            echo "Default help for animal " . get_class($a) . PHP_EOL;
        });
    }
}

class AnyOtherVet extends Vet {}

class BetterVet extends Vet {
    public function helpCat(Cat $cat) {
        // Make $cat healthy again.
        echo "Your kitty is healthy again." . PHP_EOL;
    }
}

function visit(Vet $vet, Animal $pet) {
    return $vet->help($pet);
}

visit(new Vet, new Animal);
visit(new Vet, new Cat);
visit(new Vet, new Dog);

visit(new AnyOtherVet, new Animal); // calls Vet::help(Animal)
visit(new AnyOtherVet, new Cat); // calls Vet::help(Animal)
visit(new AnyOtherVet, new Dog); // calls Vet::help(Animal)

visit(new BetterVet, new Animal); // calls Vet::help(Animal)
// (does nothing when no base method is provided)
visit(new BetterVet, new Cat); // calls BetterVet::helpCat(Cat)
visit(new BetterVet, new Dog); // Invalid call: BetterVet only helps cats.

// visit(new BetterVet, new LivingBeing); contravariance isn't supported yet
