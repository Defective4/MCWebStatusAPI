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

require_once 'mcwsapi/IOException.php';
class MCStat {

    public static function stat(String $host, int $port) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!@socket_connect($socket, $host, $port)) {
            throw new IOException("Couldn't connect to the server", 0);
        }

        $hs = MCStat::createHandshake(762, $host, $port, 1);
        socket_write($socket, MCStat::createVarInt(strlen($hs)));
        socket_write($socket, $hs);
        socket_write($socket, pack("c", 1));
        socket_write($socket, pack("c", 0));

        if (MCStat::readVarInt($socket) <= 0)
            throw new IOException("Invalid received packet length", 1);
        if (MCStat::readVarInt($socket) != 0)
            throw new IOException("Invalid received packet ID", 2);

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

