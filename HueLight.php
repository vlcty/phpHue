<?php
class HueLight
{
    const COLOR_GREEN = array('hue' => 182 * 140, 'sat' => 254, 'bri' => 254 );
    const COLOR_RED = array('hue' => 0, 'sat' => 254, 'bri' => 254 );
    const COLOR_BLUE = array('hue' => 182 * 250, 'sat' => 254, 'bri' => 254 );
    const COLOR_COOLWHITE = array('hue' => 150, 'sat' => 254, 'bri' => 254 );
    const COLOR_WARMWHITE = array('hue' => 500, 'sat' => 254, 'bri' => 254 );
    const COLOR_ORANGE = array('hue' => 182 * 25, 'sat' => 254, 'bri' => 254 );
    const COLOR_YELLOW = array('hue' => 182 * 85, 'sat' => 254, 'bri' => 254 );
    const COLOR_PINK = array('hue' => 182 * 300, 'sat' => 254, 'bri' => 254 );
    const COLOR_PURPLE = array('hue' => 182 * 270, 'sat' => 254, 'bri' => 254 );

    const SATISFACTION_LOWEST = 1;
    const SATISFACTION_MIDDLE = 128;
    const SATISFACTION_HIGHEST = 254;

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
    private $ct = 0; // 0 to 500
    private $alert = "none"; // "none", "select" or "lselect"
    private $effect = "none"; // "none" or "colorloop"
    private $colormode = "none"; // "hs", "xy" or "ct"

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
            # Field $data['state']['hue'] does not exist. Remove this?
            $this->setValueForMemberFromArray($this->hue, $data, 'hue');
            $this->setValueForMemberFromArray($this->satisfaction, $data, 'sat');
            $this->setValueForMemberFromArray($this->ct, $data, 'ct');
            $this->setValueForMemberFromArray($this->alert, $data, 'alert');
            $this->setValueForMemberFromArray($this->effect, $data, 'effect');
            $this->setValueForMemberFromArray($this->colormode, $data,
                'colormode');
        }
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

    public function turnOn() {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'on' => true
            )));

        $this->isOn = true;
    }

    public function turnOff() {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode(array(
                'on' => false
            )));

        $this->isOn = false;
    }

    public function toggleState() {
        if ( $this->isOn == true )
            $this->turnOff();
        else
            $this->turnOn();
    }

    public function setColor($newColor) {
        $pest = $this->parent->makePest();
        $pest->put(sprintf('lights/%d/state', $this->id),
            json_encode($newColor));
    }

    /**
     * Sets the color satisfaction.
     * Values can be betwenn 1 and 254 (included).
     *
     * 1   = The less satisfied
     * 254 = The most satisfied
     *
     * @
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

    public function getCt()
    {
        return $this->ct;
    }

    public function getAlert()
    {
        return $this->alert;
    }

    public function getEffect()
    {
        return $this->effect;
    }

    public function getColormode()
    {
        return $this->colormode;
    }

    // Sets the alert state. 'select' blinks once, 'lselect' blinks repeatedly, 'none' turns off blinking
    public function setAlert( $type = 'select' )
    {
        $data = json_encode( array( "alert" => $type ) );
        $pest = $this->parent->makePest();
        $pest->put( "lights/" .$this->id. "/state", $data );

        $this->parent->update( $this->id );
    }

    // Sets the effect state. 'colorloop' cycles through all hues using the current brightness and saturation settings, 'none' turns off the effect
    public function setEffect( $type = 'colorloop' )
    {
        $data = json_encode( array( "effect" => $type ) );
        $pest = $this->parent->makePest();
        $pest->put( "lights/" .$this->id. "/state", $data );

        $this->parent->update( $this->id );
    }

    // Sets the state property
    public function setLight( $input )
    {
        $data = json_encode( $input );
        $pest = $this->parent->makePest();
        $pest->put( "lights/" .$this->id. "/state", $data );

        $this->parent->update( $this->id );
    }

    // Sets a new name
    public function setName( $name )
    {
        $data = json_encode( array( "name" => $name ) );
        $pest = $this->parent->makePest();
        $pest->put( "lights/" .$this->id, $data );

        $this->parent->update( $this->id );
    }

    // Gin up a random color
    public function randomColor()
    {
        $return = array();

        $return['hue'] = rand( 0, 65535 );
        $return['sat'] = rand( 0, 254 );
        $return['bri'] = rand( 0, 254 );

        return $return;
    }

    // Gin up a random temp-based white setting
    public static function randomWhite()
    {
        $return = array();
        $return['ct'] = rand( 150, 500 );
        $return['bri'] = rand( 0, 255 );

        return $return;
    }
}
?>
