<?php

require 'vendor/autoload.php';

function assignRoom(Skier $first, Skier $second)
{
    $first->share($second, 'helo');
}

assignRoom(new Boy, new Boy);

// Catchable fatal error: argument passed to Boy::shareBoy() must be
// instace of Boy, instance of Girl given.
assignRoom(new Boy, new Girl);

class Skier
{
    protected $roommate;

    public function share(Skier $skier, $another = null)
    {
        // TODO: set base behavior for this method!
        return covariant($this);
    }

    public function roommate(Skier $skier)
    {
        return $this->roommate;
    }
}

class Boy extends Skier
{
    public function shareBoy(Boy $boy)
    {
        $this->roommate = $boy;
        return $this;
    }
}

class Girl extends Skier
{
    public function shareGirl(Girl $girl)
    {
        $this->roommate = $girl;
        return $this;
    }
}
