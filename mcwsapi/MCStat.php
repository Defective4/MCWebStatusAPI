<?php

namespace mcwsapi;

class MCStat {

    public static function stat(String $host, int $port) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, $host, $port);

        $hs = MCStat::createHandshake(762, $host, $port, 1);
        socket_write($socket, MCStat::createVarInt(strlen($hs)));
        socket_write($socket, $hs);
        socket_write($socket, pack("c", 1));
        socket_write($socket, pack("c", 0));

        MCStat::readVarInt($socket);
        MCStat::readVarInt($socket); // TODO Error handling
        
        $raw = MCStat::readString($socket);
        return json_decode($raw, true, 999, JSON_THROW_ON_ERROR);
    }

    public static function createHandshake(int $protocol, String $host, int $port, int $state): String {
        $data = pack("c", 0);
        $data .= MCStat::createVarInt($protocol);
        $data .= MCStat::createVarInt(strlen($host));
        $data .= $host;
        $data .= pack("n", $port);
        $data .= pack("c", $state);
        return $data;
    }

    public static function readString($socket): String {
        $len = MCStat::readVarInt($socket);
        $buffer = "";
        socket_recv($socket, $buffer, $len, MSG_WAITALL);
        return $buffer;
    }

    public static function readVarInt($socket): int {
        $numRead = 0;
        $result = 0;
        do {
            $buffer = "";
            socket_recv($socket, $buffer, 1, MSG_WAITALL);
            $read = unpack("c", $buffer)[1];
            $value = ($read & 0b01111111);
            $result |= ($value << (7 * $numRead));
            $numRead++;
            if ($numRead > 5) {
                throw new \RuntimeException("VarInt is too big");
            }
        } while(($read & 0b10000000) != 0);
        return $result;
    }

    public static function createVarInt(int $value): String {
        $data = "";
        do {
            $temp = ($value & 0b01111111);
            $value >>= 7;
            if ($value != 0) {
                $temp |= 0b10000000;
            }
            $temp = (($temp + 128) % 256) - 128;
            $data .= pack("c", $temp);
        } while($value != 0);
        return $data;
    }
}

