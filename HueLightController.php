<?php
class HueLightController {
    private $lights = null;

    /**
     * Constructs a new Hue Lightcontroller. The parameter takes an array
     * where HueLight objects should be stored. They get check and NULL values
     * or objects of false types are filtered out
     *
     * @param $lights array The reference to an array containing HueLight
     *      objects
     * @throws InvalidArgumentException If the parameter is not an array
     *      or empty
     **/
    public function __construct(&$lights) {
        if ( is_array($lights) == false )
            throw new InvalidArgumentException('Parameter has to be an array');

        if ( sizeof($lights) == 0 )
            throw new InvalidArgumentException('No lights given');

        $this->lights = $lights;

        $this->filterOutNullOrFalseHueLightObjects();
        $this->filterOutNotReachableLights();
    }

    /**
     * Filters out every item which is NULL or not a HueLight object
     *
     * @return void
     **/
    private function filterOutNullOrFalseHueLightObjects() {
        $newArray = array();

        while ( sizeof($this->lights) != 0 ) {
            $currentObject = array_shift($this->lights);

            if ( is_null($currentObject) == false &&
                is_a($currentObject, 'HueLight') ) {
                    $newArray[] = $currentObject;
            }
        }

        $this->lights = &$newArray;
    }

    /**
     * Filters out not reachable lights
     *
     * @return void
     **/
    private function filterOutNotReachableLights() {
        $newArray = array();

        while ( sizeof($this->lights) != 0 ) {
            $currentLight = array_shift($this->lights);

            if ( $currentLight->isReachable() )
                $newArray[] = $currentLight;
        }

        $this->lights = &$newArray;
    }

    /**
     * Turns on and setups the attributs of all lights according to your needs.
     * If an attribute is NULL it will be skipped and left unchanged.
     *
     * @param $effect string The effect to set
     * @param $satiscation int The satiscation to set
     * @param $brightness int The brightness to set
     * @param $color int The color to set
     * @return void
     **/
    private function setupLights($effect = null, $satisfaction = null,
        $brightness = null, $color = null) {

        $this->turnOnAllLights();

        foreach ( $this->lights as &$currentLight ) {
            if ( ! is_null($effect) )
                $currentLight->setEffect($effect);

            if ( ! is_null($satisfaction) )
                $currentLight->setSatisfaction($satisfaction);

            if ( ! is_null($brightness) )
                $currentLight->setBrightness($brightness);

            if ( ! is_null($color) )
                $currentLight->setColor($color);
        }
    }

    /**
     * Filters out lights which have the given name
     *
     * @param $name string The name of the unwanted lights
     * @throws InvalidArgumentException If the name is not a string or empty
     * @return int The amount of removed lights
     **/
    public function filterOutUnwantedLightsByName($name) {
        if ( is_string($name) == false || strlen($name) == 0 )
            throw new InvalidArgumentException('Name not a string or empty');

        $amountRemoved = 0;
        $newArray = array();

        while ( sizeof($this->lights) ) {
            $currentLight = array_shift($this->lights);

            if ( preg_match("/$name/", $currentLight->getName()) == false ) {
                $newArray[] = $currentLight;
            }
            else {
                $amountRemoved++;
            }
        }

        $this->lights = &$newArray;

        return $amountRemoved;
    }

    /**
     * Turns on all lights
     *
     * @return void
     **/
    public function turnOnAllLights() {
        foreach ( $this->lights as &$currentLight )
            $currentLight->turnOn();
    }

    /**
     * Turns off all lights
     *
     * @return void
     **/
    public function turnOffAllLights() {
        foreach ( $this->lights as &$currentLight )
            $currentLight->turnOff();
    }

    /**
     * Torrgle the lights. Lights which are off are turned on and vice versa.
     *
     * @return void
     **/
    public function toggleAllLights() {
        foreach ( $this->lights as &$currentLight )
            $currentLight->toggleState();
    }

    /**
     * Returns the array containing the lights after all the built in filtering.
     * PLEASE: Don't use this function to manipulate the array and append shit!
     *
     * @return array The array of HueLight objects
     **/
    public function getLights() {
        return $this->lights;
    }

    /**
     * Nice orange and red tones which change slowly the color
     *
     * @return void
     **/
    public function warmPlace() {
        $colorMax = 8000;

        $this->setupLights(HueLight::EFFECT_NONE,
            HueLight::SATISFACTION_HIGHEST,
            HueLight::BRIGHTNESS_HIGHEST,
            null
        );

        foreach ( $this->lights as &$currentLight ) {
            $currentLight->setColor(rand(1, $colorMax));
            sleep(1);
        }

        while ( 1 ) {
            foreach ( $this->lights as &$currentLight ) {
                printf("Light: %s Old-Color: %d ",
                    $currentLight->getName(),
                    $currentLight->getColor());

                $newColor = $currentLight->getColor() + 100;

                printf("New Color: %d\n",
                    $newColor);

                if ( $newColor >= $colorMax)
                    $newColor = 1;

                $currentLight->setColor($newColor);
                sleep(4);
            }

            print("-------------\n");
        }
    }

    /**
     * White sprobe
     *
     * @return void
     **/
    public function whiteStrobe() {
        $this->setupLights(
            HueLight::EFFECT_NONE,
            HueLight::SATISFACTION_LOWEST,
            HueLight::BRIGHTNESS_HIGHEST,
            HueLight::COLOR_COOLWHITE
        );

        // Run
        while ( true ) {
            shuffle($this->lights);

            foreach ( $this->lights as &$currentLight ) {
                $currentLight->turnOn();
                $currentLight->turnOff();
            }

            sleep(1);
        }
    }
}
?>
