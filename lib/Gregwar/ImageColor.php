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
        // Direct color representation (ex: 0xff0000)
        if (!is_string($color) && is_numeric($color))
            return $color;

        // Color name (ex: "red")
        if (isset(self::$colors[$color]))
            return self::$colors[$color];

        // Color string (ex: "ff0000", "#ff0000" or "0xfff")
        if (is_string($color)) {
            if (preg_match('/^(#|0x|)([0-9a-f]{3,6})/i', $color, $matches)) {
                $col = $matches[2];

                if (strlen($col) == 6)
                    return hexdec($col);

                if (strlen($col) == 3) {
                    $r = '';
                    for ($i=0; $i<3; $i++)
                        $r.= $col[$i].$col[$i];
                    return hexdec($r);
                }
            }
        }

        throw new \InvalidArgumentException('Invalid color: '.$color);
    }
}
