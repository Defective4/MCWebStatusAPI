<?php

namespace mcwsapi;

require_once 'mcwsapi/ChatColors.php';
class ChatParser {

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

