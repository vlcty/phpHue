<?php
class HueLight
{
    private $parent;
    private $id = 0;
    private $name = "";
    private $type = "";
    private $modelid = "";
    private $swversion = "";
    private $state = false;
    private $reachable = false;
    private $bri = 0; // 0 to 255
    private $hue = 0; // 0 to 65535
    private $sat = 0; // 0 to 255
    private $ct = 0; // 0 to 500
    private $alert = "none"; // "none", "select" or "lselect"
    private $effect = "none"; // "none" or "colorloop"
    private $colormode = "none"; // "hs", "xy" or "ct"


    public function __construct( $parent, $lightid, $json )
    {
        $this->parent = $parent;
        $data = json_decode( $json, true );

        if ( isset( $data["state"] ) )
        {
            $this->id = $lightid;

            $this->setValueForMemberFromArray($this->name, $data, 'name');
            $this->setValueForMemberFromArray($this->type, $data, 'type');
            $this->setValueForMemberFromArray($this->modelid, $data,
                'modelid');
            $this->setValueForMemberFromArray($this->swversion, $data,
                'swversion');
            $this->setValueForMemberFromArray($this->state, $data, 'on');
            $this->setValueForMemberFromArray($this->reachable, $data,
                'reachable');
            $this->setValueForMemberFromArray($this->bri, $data, 'bri');
            # Field $data['state']['hue'] does not exist. Remove this?
            $this->setValueForMemberFromArray($this->hue, $data, 'hue');
            $this->setValueForMemberFromArray($this->sat, $data, 'sat');
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

    public function id()
    {
        return $this->id;
    }


    public function name()
    {
        return $this->name;
    }


    public function type()
    {
        return $this->type;
    }


    public function modelid()
    {
        return $this->modelid;
    }


    public function swversion()
    {
        return $this->swversion;
    }


    public function state()
    {
        return $this->state;
    }


    public function reachable()
    {
        return $this->reachable;
    }


    public function bri()
    {
        return $this->bri;
    }


    public function hue()
    {
        return $this->hue;
    }


    public function sat()
    {
        return $this->sat;
    }


    public function ct()
    {
        return $this->ct;
    }


    public function alert()
    {
        return $this->alert;
    }


    public function effect()
    {
        return $this->effect;
    }


    public function colormode()
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
}
?>
