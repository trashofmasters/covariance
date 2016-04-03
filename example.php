<?php

require 'vendor/autoload.php';

function assignRoom(Skier $first, Skier $second)
{
    $first->share($second);
}

assignRoom(new Boy, new Boy);
// Makes the two boys room mates.

assignRoom(new RankedBoy, new Boy);

assignRoom(new Boy, new Girl);
// PHP Fatal error:  Cannot pass object of instance Boy to covariant method Girl::share.

class Skier
{
    protected $roommate;

    public function share(Skier $skier)
    {
        // TODO: set base behavior for this method!
        return covariant($this, $skier);
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

class RankedBoy extends Boy
{
    public function shareBoy(Boy $boy)
    {
        echo 'ranked + boy' . PHP_EOL;
    }

    public function shareRankedBoy(RankedBoy $boy)
    {
        echo 'ranked + ranked' . PHP_EOL;
    }
}
