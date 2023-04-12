<?php

// MIT License

// Copyright (c) 2023 Defective4

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
namespace mcwsapi;

class ChatColors {
    private static $colors = array (
            "black" => "000000",
            "dark_blue" => "0000aa",
            "dark_green" => "00aa00",
            "dark_aqua" => "00aaaa",
            "dark_red" => "aa0000",
            "dark_purple" => "aa00aa",
            "gold" => "ffaa00",
            "gray" => "aaaaaa",
            "dark_gray" => "555555",
            "blue" => "5555ff",
            "green" => "55ff55",
            "cyan" => "55ffff",
            "red" => "ff5555",
            "pink" => "ff55ff",
            "yellow" => "ffff55",
            "white" => "ffffff"
    );
    private static $formats = array (
            "o",
            "p",
            "r",
            "t",
            "u",
            "l",
            "m",
            "n",
            "k"
    );

    public static function legacyToHex(String $parsed) {
        $colors = ChatColors::$colors;
        $keys = array_keys($colors);
        $hexed = "\u{00a7}7".$parsed;
        for($x = 0; $x < count($colors); $x++) {
            $hex = $colors[$keys[$x]];
            $hexX = dechex($x);
            $hexed = str_replace("\u{00a7}$hexX", "\u{00a7}#$hex", $hexed);
        }
        foreach(ChatColors::$formats as $format)
            $hexed = str_replace("\u{00a7}$format", "", $hexed);
        return $hexed;
    }

    public static function parse(String $cName) {
        $colors = ChatColors::$colors;
        if (array_key_exists($cName, $colors)) {
            return dechex(array_search($cName, array_keys($colors)));
        } else {
            return "#".$colors["white"];
        }
    }
}

