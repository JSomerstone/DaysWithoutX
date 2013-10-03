<?php
namespace JSomerstone\DaysWithoutBundle\Model;

class CounterModel
{
    private $days;
    private $reseted;
    private $name;
    private $thing;

    public function __construct($thing, $reseted = null)
    {
        $this->thing = $thing;
        $this->reseted = is_null($reseted) ? date('Y-m-d') : $reseted;
        $this->name = self::getUrlSafe($thing);
    }

    public function persist($path)
    {
        $filename = "$path/$this->name.json";

        if ( ! file_put_contents($filename, $this->toJson()))
        {
            throw new \Exception("Unable to perist counter to '$filename'");
        }
    }

    public static function exists($path, $name)
    {
        $name = self::getUrlSafe($name);
        $filename = "$path/$name.json";
        return file_exists($filename);
    }

    public static function load($path, $name)
    {
        $name = self::getUrlSafe($name);
        $filename = "$path/$name.json";
        if (!file_exists($filename)) {
            throw new \Exception("Counter '$name' not found");
        }
        $arr = json_decode(file_get_contents($filename));
        return new static($arr->thing, $arr->reseted);

    }

    public function toArray()
    {
        return array(
            'name' => $this->name,
            'thing' => $this->thing,
            'reseted' => $this->reseted,
            'days' => $this->getDays()
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getThing()
    {
        return $this->thing;
    }

    public function getReseted()
    {
        return $this->reseted;
    }
    
    public function getDays()
    {
        $now = time();
        $reseted = strtotime($this->reseted);
        return floor(($now - $reseted)/(60*60*24));
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private static function getUrlSafe($unsafe)
    {
        $lower = strtolower($unsafe);
        $clean = preg_replace('/[^a-z0-9_\ \-]/', '', $lower);
        return preg_replace('/[\ ]/', '-', $clean);
    }
}
