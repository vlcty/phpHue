<?php
require( __DIR__ . '/pest-master/PestJSON.php' );
require(__DIR__ . '/HueError.php');

class HueBridge
{
    private $bridgeAddress;
    private $authKey;

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
    }

    public function makePest()
    {
        return new Pest( "http://" .$this->bridgeAddress. "/api/" .$this->authKey. "/" );
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

    public function getLights() {
        $lights = array();

        $pest = $this->makePest();
        $result = json_decode($pest->get('lights'), true);

        if ( is_null($result) ) {
            throw new HueError('Was not able to retrieve lights');
        }

        foreach ( array_keys($result) as $currentLight ) {
            $lights[] = new HueLight($this,
                (int) $currentLight,
                $result[$currentLight]);
        }

        return $lights;
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
}
?>
