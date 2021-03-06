<?php

error_reporting(E_ALL);
require_once('config.php');
require_once('location.php');
require_once('travelMath.php');
require_once('travelView.php');

//
// This refactoring step:
// extracted calculateAngleAndDistance, pickBestOption, takeFastestVehicle
// tryToWalkThere and constructor methods.
// This refactoring lines up with Listing 13-22
//

class Travel
{

    private $distance = null;
    private $angle = 0.0;
    private $angle_in_radians = 0.0;
    private $time = 0.0;
    private $src = 0.0;
    private $dest = 0.0;

    public function __construct()
    {
        $this->distance = new Location(0, 0);
    }

    public function execute(Location $src, Location $dest)
    {
        $this->src = $src;
        $this->dest = $dest;

        $this->calculateAngleAndDistance();

        TravelView::displayOurIntendedPath($this->angle, $this->distance, $this->src, $this->dest);

        if ($this->doWeHaveOptions())
        {
            $this->pickBestOption();
        } else
        {
            $this->tryToWalkThere();
        }

        TravelView::displaySummary($this->time);
    }

    public function calculateAngleAndDistance()
    {
        $this->angle = TravelMath::calculateAngleInDegrees($this->src, $this->dest);
        $this->angle_in_radians = deg2rad($this->angle);
        $this->distance = TravelMath::calculateDistance($this->src, $this->dest);
    }

    public function tryToWalkThere()
    {
        if (STORMY_WEATHER)
        {
            TravelView::displayError("Storming");
        } else if ($this->distance < WALKING_MAX_DISTANCE)
        {
            $this->walk();
        } else
        {
            TravelView::displayError("Too far to walk");
        }
    }

    public function pickBestOption()
    {
        if (STORMY_WEATHER)
        {
            $this->takeFastestVehicle();
        } else
        {
            if ($this->distance < WALKING_MAX_DISTANCE && !IN_A_RUSH)
            {
                $this->walk();
            } else
            {
                $this->takeFastestVehicle();
            }
        }
    }

    private function takeFastestVehicle()
    {
        if (HAS_CAR)
        {
            $this->driveCar();
        } else if (HAS_MONEY && ON_BUS_ROUTE)
        {
            $this->rideBus();
        } else
        {
            $this->rideBike();
        }
    }

    private function doWeHaveOptions()
    {
        $has_options = false;
        if (HAS_CAR || (HAS_MONEY && ON_BUS_ROUTE) || HAS_BIKE)
        {
            $has_options = true;
        }
        return $has_options;
    }

    private function move($step, $message)
    {
        while (!TravelMath::isCloseToDest($this->src, $this->dest, $step))
        {
            $this->moveCloserToDestination($step, $message);
        }

        TravelView::displayArrived($message);
    }

    private function driveCar()
    {
        $this->time = CAR_DELAY;
        $this->move(CAR_STEP, "Driving a Car");
    }

    private function rideBus()
    {
        $this->time = BUS_DELAY;
        $this->move(BUS_STEP, "On the Bus");
    }

    private function rideBike()
    {
        $this->move(BIKE_STEP, "Biking");
    }

    private function walk()
    {
        $this->move(WALK_STEP, "Walking");
    }

    private function moveCloserToDestination($step, $method)
    {
        $this->src->x += ( $step * cos($this->angle_in_radians));
        $this->src->y += ( $step * sin($this->angle_in_radians));
        ++$this->time;
        TravelView::displayLocationStatusMessage($this->src->x, $this->src->y);
    }

}

//sample usage
$travel = new Travel();
$travel->execute(new Location(1, 3), new Location(4, 10));
//Sample Output:
//
//Trying to go from (1, 3) to (4, 10)
//In a rush
//Distance is 7.6157731058639 in the direction of 1.1659045405098 degrees
//Got to destination by driving a car
//Total time was: 00:20
?>