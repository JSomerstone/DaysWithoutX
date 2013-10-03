<?php
namespace JSomerstone\DaysWithoutBundle\Model;

class CounterModel
{
    private $days;
    private $reseted;
    private $name;
    private $thing;

    public function __construct($thing, $days = 0, $reseted = null)
    {
        $this->thing = $thing;
        $this->days = $days;
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

    public static function load($path, $name)
    {
        $filename = "$path/$name.json";
        if (!file_exists($filename)) {
            throw new \Exception("Counter '$name' not found");
        }
        $arr = json_decode(file_get_contents($filename));
        return new static($arr->thing, $arr->days, $arr->reseted);

    }

    public function toArray()
    {
        return array(
            'name' => $this->name,
            'thing' => $this->thing,
            'reseted' => $this->reseted,
            'days' => $this->days
        );
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private static function getUrlSafe($unsafe)
    {
        $lower = strtolower($unsafe);
        $clean = preg_replace('/[^a-z0-9_\ ]/', '', $lower);
        return preg_replace('/[\ ]/', '-', $clean);
    }
}
