<?php

require 'vendor/autoload.php';

function assignRoom(Skier $first, Skier $second)
{
    $first->share($second, 'helo');
}

assignRoom(new Boy, new Boy);
assignRoom(new Boy, new Girl);
// BadMethodCallException: Cannot pass object of instance Girl to covariant method Boy::share.

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
