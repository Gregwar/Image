<?php

namespace Gregwar;

/**
 * Color manipulation class
 */
class ImageColor
{
    private static $colors = array(
        'black'     =>  0x000000,
        'silver'    =>  0xc0c0c0,
        'gray'      =>  0x808080,
        'teal'      =>  0x008080,
        'aqua'      =>  0x00ffff,
        'blue'      =>  0x0000ff,
        'navy'      =>  0x000080,
        'green'     =>  0x008000,
        'lime'      =>  0x00ff00,
        'white'     =>  0xffffff,
        'fuschia'   =>  0xff00ff,
        'purple'    =>  0x800080,
        'olive'     =>  0x808000,
        'yellow'    =>  0xffff00,
        'orange'    =>  0xffA500,
        'red'       =>  0xff0000,
        'maroon'    =>  0x800000,
        'transparent' => 0x7fffffff
    );

    public static function parse($color)
    {
        if (is_numeric($color))
            return $color;

        if (isset(self::$colors[$color]))
            return self::$colors[$color];

        throw new \InvalidArgumentException('Invalid color: '.$color);
    }
}
