<?php
/**
 * Binary Formatter Library
 * Copyright (c) Violetech. All rights reserved.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the “Software”), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
 * is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

define( "BF_BYTE",               0 );
define( "BF_SHORT",              1 );
define( "BF_USHORT",             2 );
define( "BF_INT",                3 );
define( "BF_UINT",               4 );
define( "BF_LONG",               5 );
define( "BF_ULONG",              6 );
define( "BF_FLOAT",              7 );
define( "BF_FIXED_LEN_CHARS",    8 );
define( "BF_STRING",             9 );
define( "BF_BINARY",             10 );

define( "BFSI_BYTE",             0 );
define( "BFSI_USHORT",           1 );
define( "BFSI_UINT",             2 );
define( "BFSI_ULONG",            3 );

class BinaryFormat {
    /**
     * @var array Format data.
     */
    private array $format = [];

    /**
     * Byte data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function byte( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_BYTE ];
        return $this;
    }
    
    /**
     * Short data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function short( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_SHORT ];
        return $this;
    }

    /**
     * Unsigned short data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function ushort( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_USHORT ];
        return $this;
    }

    /**
     * Int data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function int( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_INT ];
        return $this;
    }

    /**
     * Unsigned int data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function uint( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_UINT ];
        return $this;
    }

    /**
     * Long data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function long( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_LONG ];
        return $this;
    }

    /**
     * Unsigned long data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function ulong( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_ULONG ];
        return $this;
    }

    /**
     * Float data type.
     * 
     * @param string $n Name.
     * @return self
     */
    public function float( string $n ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_FLOAT ];
        return $this;
    }

    /**
     * Fixed length characters data type.
     * 
     * @param string $n Name.
     * @param int $s Size.
     * @return self
     */
    public function fixed( string $n, int $s ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_FIXED_LEN_CHARS, "size" => $s ];
        return $this;
    }

    /**
     * String data type.
     * 
     * @param string $n Name.
     * @param int $s Size.
     * @param ?int $si Size identifier.
     * @return self
     */
    public function string( string $n, ?int $si ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_STRING, "sizeIdentifier" => $si ];
        return $this;
    }

    /**
     * Binary data type.
     * 
     * @param string $n Name.
     * @param int $s Size.
     * @return self
     */
    public function binary( string $n, int $s ) : self {
        $this->format[] = [ "name" => $n, "type" => BF_BINARY, "size" => $s ];
        return $this;
    }

    /**
     * Get format array.
     * 
     * @return array Format array.
     */
    public function array() : array {
        return $this->format;
    }
}

class BinaryFormatter {
    /**
     * @var string Binary data.
     */
    private ?string $bin = null;

    /**
     * @var int Pointer.
     */
    private int $pointer = 0;

    /**
     * @var bool Use little endian.
     */
    private bool $useLittleEndian = false;

    /**
     * Constructor
     * 
     * @param string $bin Binary data.
     */
    public function __construct( string $bin ) {
        $this->bin = $bin;
    }

    /**
     * Convert binary data to array.
     * 
     * @param BinaryFormat|array $format Format.
     * @return string Array of extracted binary data.
     */
    public function toArray( BinaryFormat|array $format ) : array {
        if( $format instanceof BinaryFormat )
            $format = $format->array();

        $arr = [];

        foreach( $format as $f ) {
            $name = $f[ "name" ];
            $type = $f[ "type" ];
            $size = $f[ "size" ] ?? 0;

            switch( $type ) {
                case BF_BYTE:
                    $value = unpack( "C", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 1;
                    break;
                case BF_SHORT:
                    $value = unpack( "s", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 2;
                    break;
                case BF_USHORT:
                    $value = unpack( $this->useLittleEndian ? "v" : "n", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 2;
                    break;
                case BF_INT:
                    $value = unpack( "l", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 4;
                    break;
                case BF_UINT:
                    $value = unpack( $this->useLittleEndian ? "V" : "N", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 4;
                    break;
                case BF_LONG:
                    $value = unpack( "q", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 8;
                    break;
                case BF_ULONG:
                    $value = unpack( $this->useLittleEndian ? "P" : "J", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 8;
                    break;
                case BF_FLOAT:
                    $value = unpack( $this->useLittleEndian ? "e" : "E", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += 8;
                    break;
                case BF_FIXED_LEN_CHARS:
                    $value = unpack( "a{$size}", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += $size;
                    break;
                case BF_STRING:
                    $length = 0;
                    switch( $f[ "sizeIdentifier" ] ?? BFSI_ULONG ) {
                        case BFSI_BYTE:
                            $length = unpack( "C", $this->bin, $this->pointer )[ 1 ];
                            $this->pointer += 1;
                            break;
                        case BFSI_USHORT:
                            $length = unpack( $this->useLittleEndian ? "v" : "n", $this->bin, $this->pointer )[ 1 ];
                            $this->pointer += 2;
                            break;
                        case BFSI_UINT:
                            $length = unpack( $this->useLittleEndian ? "V" : "N", $this->bin, $this->pointer )[ 1 ];
                            $this->pointer += 4;
                            break;
                        case BFSI_ULONG:
                        default:
                            $length = unpack( $this->useLittleEndian ? "P" : "J", $this->bin, $this->pointer )[ 1 ];
                            $this->pointer += 8;
                            break;
                    }

                    $value = unpack( "a{$length}", $this->bin, $this->pointer )[ 1 ];
                    $arr[ $name ] = $value;
                    $this->pointer += $length;
                    break;
                case BF_BINARY:
                    $value = substr( $this->bin, $this->pointer, $size );
                    $arr[ $name ] = $value;
                    $this->pointer += $size;
                    break;
            }
        }

        return $arr;
    }

    /**
     * Convert array to binary data.
     * 
     * @param array $arr Array of data to convert.
     * @param BinaryFormat|array $format Format to use for conversion.
     * @return string Binary data.
     * @throws InvalidArgumentException If size is not specified for binary type or value length
     *                                  does not match specified size.
     */
    public function fromArray( array $arr, BinaryFormat|array $format ) : string {
        if( $format instanceof BinaryFormat )
            $format = $format->array();

        $bin = "";

        foreach( $format as $f ) {
            $name = $f[ "name" ];
            $type = $f[ "type" ];
            $size = $f[ "size" ] ?? 0;
            $value = $arr[ $name ] ?? null;

            switch( $type ) {
                case BF_BYTE:
                    $bin .= pack( "C", $value );
                    break;
                case BF_SHORT:
                    $bin .= pack( "s", $value );
                    break;
                case BF_USHORT:
                    $bin .= pack( $this->useLittleEndian ? "v" : "n", $value );
                    break;
                case BF_INT:
                    $bin .= pack( "l", $value );
                    break;
                case BF_UINT:
                    $bin .= pack( $this->useLittleEndian ? "V" : "N", $value );
                    break;
                case BF_LONG:
                    $bin .= pack( "q", $value );
                    break;
                case BF_ULONG:
                    $bin .= pack( $this->useLittleEndian ? "P" : "J", $value );
                    break;
                case BF_FLOAT:
                    $bin .= pack( $this->useLittleEndian ? "e" : "E", $value );
                    break;
                case BF_FIXED_LEN_CHARS:
                    $bin .= pack( "a{$size}", $value );
                    break;
                case BF_STRING:
                    $length = strlen( $value );

                    switch( $f[ "sizeIdentifier" ] ?? BFSI_ULONG ) {
                        case BFSI_BYTE:
                            $bin .= pack( "C", $length );
                            break;
                        case BFSI_USHORT:
                            $bin .= pack( $this->useLittleEndian ? "v" : "n", $length );
                            break;
                        case BFSI_UINT:
                            $bin .= pack( $this->useLittleEndian ? "V" : "N", $length );
                            break;
                        case BFSI_ULONG:
                        default:
                            $bin .= pack( $this->useLittleEndian ? "P" : "J", $length );
                            break;
                    }
                    
                    $bin .= pack( "a{$length}", $value );
                    break;
                case BF_BINARY:
                    if( $size <= 0 ) {
                        throw new InvalidArgumentException( "Size must be specified for binary type." );
                    }

                    if( strlen( $value ) !== $size ) {
                        throw new InvalidArgumentException( "Value length does not match specified size for binary type." );
                    }

                    $bin .= substr( $value, 0, $size );
                    break;
            }
        }

        return $bin;
    }
}