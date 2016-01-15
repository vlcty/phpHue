<?php
class HueLight
{
    const COLOR_GREEN = 182 * 140;
    const COLOR_RED = 0;
    const COLOR_BLUE = 182 * 250;
    const COLOR_COOLWHITE = 150;
    const COLOR_WARMWHITE = 500;
    const COLOR_ORANGE = 182 * 25;
    const COLOR_YELLOW = 182 * 85;
    const COLOR_PINK = 182 * 300;
    const COLOR_PURPLE = 182 * 270;

    const SATISFACTION_LOWEST = 1;
    const SATISFACTION_MIDDLE = 128;
    const SATISFACTION_HIGHEST = 254;

    const EFFECT_NONE = 'none';
    const EFFECT_COLORLOOP = 'colorloop';

    const ALERT_NONE = 'none';
    const ALERT_SELECT = 'select';
    const ALERT_LSELECT = 'lselect';

    private $parent;
    private $id = 0;
    private $name = "";
    private $type = "";
    private $modelid = "";
    private $swversion = "";
    private $isOn = false;
    private $reachable = false;
    private $brightness = 0; // 0 to 254
    private $hue = 0; // 0 to 65535
    private $satiscation = 0; // 0 to 255
    private $alert = "none"; // "none", "select" or "lselect"
    private $effect = 'none';

    /**
     * Constructs a new light
     *
     * @param $parent HueBridge Reference to the HueBridge object
     * @param $lightid int The light id
     * @param $data array The decoded json from the API
     **/
    public function __construct(&$parent, $lightid, $data ) {
        $this->parent = $parent;
        $this->id = $lightid;
        $this->extractObjectInfoFromArray($data);
    }

    /**
     * Extract the object info from an array and stores them in the right
     * member variables
     *
     * @param $data array The array containing the data
     * @return void
     **/
    private function extractObjectInfoFromArray($data) {
        if ( array_key_exists('state', $data) ) {
            $this->setValueForMemberFromArray($this->name, $data, 'name');
            $this->setValueForMemberFromArray($this->type, $data, 'type');
            $this->setValueForMemberFromArray($this->modelid, $data,
                'modelid');
            $this->setValueForMemberFromArray($this->swversion, $data,
                'swversion');
            $this->setValueForMemberFromArray($this->isOn, $data, 'on');
            $this->setValueForMemberFromArray($this->reachable, $data,
                'reachable');
            $this->setValueForMemberFromArray($this->brightness, $data, 'bri');
            $this->setValueForMemberFromArray($this->hue, $data, 'hue');
            $this->setValueForMemberFromArray($this->satisfaction, $data, 'sat');
            $this->setValueForMemberFromArray($this->alert, $data, 'alert');
            $this->setValueForMemberFromArray($this->effect, $data, 'effect');
        }
    }

    /**
     * Refreshs the lighs object information
     *
     * @return void
     **/
    public function refresh() {
        $pest = $this->parent->makePest();
        $this->extractObjectInfoFromArray(
            json_decode($pest->get('lights/' . $this->id), true));
    }

    /**
     * This function searches the value of a key from an array.
     * If no value is found on the first try the subarray 'state' is searched
     * if present.
     *
     * @param $member ref The member function to be set when a value is found
     * @param $array array The array to be searched
     * @param $key string The key which should be searched in the array
     * @return bool true if value was found, false if not
     **/
    private function setValueForMemberFromArray(&$member, &$array, $key) {
        if ( array_key_exists($key, $array) ) {
            $member = $array[$key];
        }
        else if ( array_key_exists('state', $array) &&
            array_key_exists($key, $array['state']) ) {
            $member = $array['state'][$key];
        }
        else {
            return false;
        }

        return true;
    }

    /**
     * Turns on the light. If the light is already on nothing will happen.
     *
     * @return void
     **/
    public function turnOn() {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'on' => true
            )));

        $this->isOn = true;
    }

    /**
     * Turns off the light. If the light is already off nothing will happen.
     *
     * @return void
     **/
    public function turnOff() {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'on' => false
            )));

        $this->isOn = false;
    }

    /**
     * Toogles the state of the light.
     * If the light is on it will turn off and vice versa.
     *
     * @return void
     **/
    public function toggleState() {
        if ( $this->isOn == true )
            $this->turnOff();
        else
            $this->turnOn();
    }

    /**
     * Sets the new color
     *
     * @param $newColor array The new color
     * @return void
     **/
    public function setColor($newColor) {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'hue' => $newColor
            )));
    }

    /**
     * Sets the color satisfaction.
     * Values can be betwenn 1 and 254 (included).
     *
     * 1   = The less satisfied
     * 254 = The most satisfied
     *
     * @param $newSatisfaction int The new satisfaction
     * @throws InvalidArgumentException If the satisfaction is out of bounds
     **/
    public function setSatisfaction($newSatisfaction) {
        if ( $newSatisfaction < HueLight::SATISFACTION_LOWEST ||
            $newSatisfaction > HueLight::SATISFACTION_HIGHEST ) {
            throw new InvalidArgumentException(
                sprintf('Values must be between %d and %d',
                    HueLight::SATISFACTION_LOWEST,
                    HueLight::SATISFACTION_HIGHEST));
        }

        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'sat' => $newSatisfaction
            )));

        $this->satisfaction = $newSatisfaction;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getModelId()
    {
        return $this->modelid;
    }

    public function getSoftwareVersion()
    {
        return $this->swversion;
    }

    public function isOn() {
        return $this->isOn;
    }

    public function isReachable()
    {
        return $this->reachable;
    }

    /**
     * Returns the brightness
     *
     * @return int The value between 0 and 255
     **/
    public function getBrightness()
    {
        return $this->brightness;
    }

    public function getHue()
    {
        return $this->hue;
    }

    public function getSatisfaction()
    {
        return $this->satisfaction;
    }

    public function getAlert()
    {
        return $this->alert;
    }

    public function getEffect()
    {
        return $this->effect;
    }

    /**
     * Sets the alert. From the official API docs:
     * The alert effect, is a temporary change to the bulb’s state.
     *
     * Have a look at HueLight::ALERT_*
     *
     * @param $newAlert string The new alert
     * @return void
     **/
    public function setAlert($newAlert) {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'alert' => $newEffect
            )));
    }

    /**
     * Sets the new effect.
     * Possible values are stored in constants. Look at HueLight::EFFECT_*
     *
     * @param $newEffect string Name of the new effect.
     * @return void
     **/
    public function setEffect($newEffect) {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'effect' => $newEffect
            )));
    }

    /**
     * Sets the lights name
     *
     * @param $newName string The new name
     **/
    public function setName($newName)
    {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d', $this->id),
            json_encode(array(
                'name' => $newName
            )));
    }
}
?>
