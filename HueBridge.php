<?php
require( __DIR__ . '/pest-master/PestJSON.php' );
require(__DIR__ . '/HueError.php');

class HueBridge
{
    private $bridgeAddress;
    private $authKey;
    private $lights;

    /**
     * Construct a new HueBridge connection
     *
     * @param $bridgeAddress string The IP or FQDN to a hue bridge
     * @param $authKey string The 32 characters long auth key
     * @throws InvalidArgumentException If one of the parameters is invalid
     **/
    public function __construct($bridgeAddress, $authKey)
    {
        if ( strlen($bridgeAddress) == 0 )
            throw new InvalidArgumentException(
                'Parameter $bridgeAddress is empty');

        if ( strlen($authKey) != 32 )
            throw new InvalidArgumentException(
                'Parameter $authKey is invalid. Length has to be 32');

        $this->bridgeAddress = $bridgeAddress;
        $this->authKey = $authKey;

        $this->update();
    }

    public function makePest()
    {
        return new Pest( "http://" .$this->bridgeAddress. "/api/" .$this->authKey. "/" );
    }

    private function makeLightArray( $lightid = false )
    {
        $targets = array();

        if ( $lightid === false )
        {
            $targets = $this->lightIds();
        }
        else
        {
            if ( !is_array( $lightid ) )
            {
                $targets[] = $lightid;
            }
            else
            {
                $targets = $lightid;
            }
        }

        return $targets;
    }

    /**
     * Gets a new API key from the bridge
     * Hint: You have to press the button on the bridge first and then
     * call this function
     *
     * @param $bridgeAddress string The IP or FQDN to the hue bridge
     * @return string with the API key
     * @throws HueError If no connection to the bridge could be established
     *          or the bridge refused to give one
     * @throws InvalidArgumentException If the bridge address is not valid
     **/
    public static function fetchAPIAccessKey($bridgeAddress)
    {
        if ( strlen($bridgeAddress) == 0 )
            throw new InvalidArgumentException(
                'Parameter $bridgeAddress is empty');

        $pest = new Pest('http://' . $bridgeAddress . '/api');
        $data = json_encode(array(
                'devicetype' => 'phpHue'
            ));;
        $result = json_decode($pest->post('', $data), true);

        if ( is_null($result) ) {
            throw new HueError('No connection to the Hue bridge');
        }
        else if ( array_key_exists('error', $result[0]) ) {
            throw new HueError($result[0]['error']['description']);
        }
        else if ( array_key_exists('success', $result[0]) ) {
            return $result[0]['success']['username'];
        }
        else {
            throw new HueError('Something went terribly bad. Should not happen');
        }
    }

    public function update( $lightid = false )
    {
        $lights = $this->makeLightArray( $lightid );
        foreach ( $lights as $id )
        {
            $pest = $this->makePest();
            $data = $pest->get( "lights/$id" );

            $this->lights[ $id ] = new HueLight( $this, $id, $data );
        }
    }

    public function lights()
    {
        return $this->lights;
    }

    // Returns an array of the light numbers in the system
    public function lightIds()
    {
        $pest = $this->makePest();
        $result = json_decode( $pest->get( 'lights' ), true );
        $targets = array_keys( $result );

        return $targets;
    }

    // Gets the full state of the bridge
    public function state()
    {
        $pest = $this->makePest();
        return $pest->get( "" );
    }

    // Gets an array of currently configured schedules
    public function schedules()
    {
        $pest = $this->makePest();
        $result = json_decode( $pest->get( "schedules" ), true );

        return $result;
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
    public function randomWhite()
    {
        $return = array();
        $return['ct'] = rand( 150, 500 );
        $return['bri'] = rand( 0, 255 );

        return $return;
    }

    // Build a few color commands based on color names
    public function predefinedColors( $colorname )
    {
        $command = array();
        switch ( $colorname )
        {
            case "green":
                $command['hue'] = 182 * 140;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "red":
                $command['hue'] = 0;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "blue":
                $command['hue'] = 182 * 250;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "coolwhite":
                $command['ct']  = 150;
                $command['bri'] = 254;
                break;

            case "warmwhite":
                $command['ct']  = 500;
                $command['bri'] = 254;
                break;

            case "orange":
                $command['hue'] = 182 * 25;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "yellow":
                $command['hue'] = 182 * 85;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "pink":
                $command['hue'] = 182 * 300;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;

            case "purple":
                $command['hue'] = 182 * 270;
                $command['sat'] = 254;
                $command['bri'] = 254;
                break;
        }

        return $command;
    }
}
?>
