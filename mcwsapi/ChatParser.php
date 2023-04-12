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

require_once 'mcwsapi/ChatColors.php';
class ChatParser {

    public static function stripColors($hexed) {
        return preg_replace("{\u{00a7}#.{6}}", "", $hexed);
    }

    public static function toHTML($hexed) {
        $html = "";
        foreach(explode("\u{00a7}#", $hexed) as $val) {
            if (strlen($val) >= 6) {
                $color = "#".substr($val, 0, 6);
                $str = substr($val, 6);
                $append = "<span style=\"color: $color\">$str</span>";
            } else {
                $append = "<span>$val</span>";
            }
            $html .= $append;
        }
        return str_replace("\n", "<br/>", str_replace("\r", "<br/>", $html));
    }

    public static function parse($json) {
        $text = "";
        if (is_array($json)) {
            if (array_key_exists("text", $json)) {
                if (array_key_exists("color", $json)) {
                    $color = $json["color"];
                    if (strpos($color, "#") === 0) {
                        $text .= "\u{00a7}".$color;
                    } else {
                        $text .= "\u{00a7}".ChatColors::parse($color);
                    }
                }
                $text .= $json["text"];
            }
            if (array_key_exists("extra", $json))
                foreach($json["extra"] as $element)
                    $text .= ChatParser::parse($element);
        } else
            $text = $json;

        return $text;
    }
}

