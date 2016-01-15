<?php
class HueScene {
    private $bridge = null;
    private $id = '';
    private $name = '';
    private $lights = array();
    private $owner = '';
    private $recycle = false;
    private $locked = false;
    private $lastupdated = '';
    private $version = 1;

    /**
     * Creates a new Hue scene
     *
     * @param $bridge HueBridge The hue bridge reference
     * @param $id string The UID of the scene
     * @param $data array The array containing the info
     * @throws InvalidArgumentException If the bridge object reference is null
     *          or the id is empty
     **/
    public function __construct(&$bridge, $id, $data) {
        if ( is_null($bridge) ||
            is_a($bridge, 'HueBridge') == false ) {
            throw new InvalidArgumentException('$bridge is not a bridge object');
        }

        if ( strlen($id) == 0 ) {
            throw new InvalidArgumentException('$id is invalid');
        }

        $this->bridge = $bridge;
        $this->id = $id;

        $this->extractObjectInfoFromArray($data);
    }

    /**
     * It does stuff.
     **/
    private function setValueForMemberFromArray(&$member, $key, $data) {
        if ( array_key_exists($key, $data) ) {
            $member = $data[$key];
        }
    }

    /**
     * It also does stuff :-)
     **/
    private function extractObjectInfoFromArray($data) {
        $this->setValueForMemberFromArray($this->name, 'name', $data);
        $this->setValueForMemberFromArray($this->owner, 'owner', $data);
        $this->setValueForMemberFromArray($this->recycle, 'recycle', $data);
        $this->setValueForMemberFromArray($this->locked, 'locked', $data);
        $this->setValueForMemberFromArray($this->lastupdated, 'lastupdated', $data);

        // Get all the lights
        // TODO: Implement this function after function in HueLight is added
    }

    /**
     * Updates the scene
     *
     * @return void
     **/
    public function update() {
        $this->lights = array();

        $pest = $this->bridge->makePest();
        $response = json_decode($pest->get('scenes/'. $this->id), true);

        $this->extractObjectInfoFromArray($response);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getLights() {
        return $this->lights;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function getRecycle() {
        return $this->recycle;
    }

    public function getLocked() {
        return $this->locked;
    }

    public function getLastUpdated() {
        return $this->lastupdated;
    }

    public function getVersion() {
        return $this->version;
    }
}
?>
