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

export const BINARY_FORMATTER_DATA_TYPES = Object.freeze({
    Byte:                                       0,
    Short:                                      1,
    UShort:                                     2,
    Int:                                        3,
    UInt:                                       4,
    Long:                                       5,
    ULong:                                      6,
    Float:                                      7,
    FixedLengthCharacters:                      8,
    String:                                     9,
    Binary:                                     10
});

export const BINARY_FORMATTER_SIZE_IDENTIFIERS = Object.freeze({
    Byte:                                       0,
    UShort:                                     1,
    UInt:                                       2,
    ULong:                                      3
});

export default class BinaryFormatter
{
    /**
     * @type {DataView} Binary data view.
     */
    #data;

    /**
     * @type {number} Pointer offset.
     */
    #pointer = 0;

    /**
     * @type {boolean} Use little endian.
     */
    #useLittleEndian = false;

    /**
     * Constructor.
     * @param {ArrayBuffer} buffer ArrayBuffer of the data.
     */
    constructor( buffer, useLittleEndian )
    {
        this.#data = new DataView( buffer );
        this.#useLittleEndian = useLittleEndian;
    }

    /**
     * Convert the binary data to a JSON format.
     * @param {BinaryFormat|object[]} format Format to use.
     * @returns {object} JSON data.
     */
    toJson( format )
    {
        const newObject = {};

        if( format instanceof BinaryFormat )
            format = format.array();

        for( const valueSpec of format )
        {
            const k = valueSpec.name;
            if( k === null )
            {
                //
                // NUL byte
                //
                this.#pointer++;
                continue;
            }

            const t = valueSpec.type;
            newObject[ k ] = null;

            switch( t )
            {
                case BINARY_FORMATTER_DATA_TYPES.Byte:
                    newObject[ k ] = this.#data.getUint8( this.#pointer );
                    this.#pointer += 1;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.Short:
                    newObject[ k ] = this.#data.getInt16( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 2;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.UShort:
                    newObject[ k ] = this.#data.getUint16( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 2;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.Int:
                    newObject[ k ] = this.#data.getInt32( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 4;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.UInt:
                    newObject[ k ] = this.#data.getUint32( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 4;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.Long:
                    newObject[ k ] = this.#data.getBigInt64( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 8;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.ULong:
                    newObject[ k ] = this.#data.getBigUint64( this.#pointer, this.#useLittleEndian );
                    this.#pointer += 8;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.Float:
                    newObject[ k ] = parseFloat( this.#data.getFloat64( this.#pointer, this.#useLittleEndian ) );
                    this.#pointer += 8;
                    break;
                case BINARY_FORMATTER_DATA_TYPES.FixedLengthCharacters:
                    {
                        let size = valueSpec.size;
                        let str = "";

                        while( size-- > 0 )
                            str += String.fromCharCode( this.#data.getUint8( this.#pointer++ ) );

                        newObject[ k ] = str;
                    }
                    break;
                case BINARY_FORMATTER_DATA_TYPES.String:
                    {
                        let size = 0;
                        switch( valueSpec.sizeIdentifier ) {
                            case BINARY_FORMATTER_SIZE_IDENTIFIERS.Byte:
                                size = this.#data.getUint8( this.#pointer++ );
                                this.#pointer += 1;
                                break;
                            case BINARY_FORMATTER_SIZE_IDENTIFIERS.UShort:
                                size = this.#data.getUint16( this.#pointer, this.#useLittleEndian );
                                this.#pointer += 2;
                                break;
                            case BINARY_FORMATTER_SIZE_IDENTIFIERS.UInt:
                                size = this.#data.getUint32( this.#pointer, this.#useLittleEndian );
                                this.#pointer += 4;
                                break;
                            case BINARY_FORMATTER_SIZE_IDENTIFIERS.ULong:
                            default:
                                size = this.#data.getBigUint64( this.#pointer, this.#useLittleEndian );
                                this.#pointer += 8;
                                break;
                        }

                        let str = "";

                        while( size-- > 0 )
                            str += String.fromCharCode( this.#data.getUint8( this.#pointer++ ) );

                        newObject[ k ] = str;
                    }
                    break;
                case BINARY_FORMATTER_DATA_TYPES.Binary:
                    {
                        let size = valueSpec.size;
                        const arr = [];

                        while( size-- > 0 )
                            arr.push( this.#data.getUint8( this.#pointer++ ) );

                        newObject[ k ] = Uint8Array.from( arr );
                    }
                    break;
            }
        }

        return newObject;
    }
}

export class BinaryFormat
{
    /**
     * @type {array} Format array.
     */
    #format;

    constructor()
    {
        this.#format = [];
    }

    /**
     * Byte data type. Set n to null for NULL byte.
     * @param {string|null} n Name.
     * @returns {this}
     */
    byte( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Byte
            }
        );
        return this;
    }

    /**
     * NULL byte shortcut.
     * @returns {this}
     */
    null()
    {
        return this.byte( null );
    }

    /**
     * Short data type.
     * @param {string} n Name.
     * @returns {this}
     */
    short( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Short
            }
        );
        return this;
    }

    /**
     * Unsigned short data type.
     * @param {string} n Name.
     * @returns {this}
     */
    ushort( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.UShort
            }
        );
        return this;
    }

    /**
     * Integer data type.
     * @param {string} n Name.
     * @returns {this}
     */
    int( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Int
            }
        );
        return this;
    }

    /**
     * Unsigned integer data type.
     * @param {string} n Name.
     * @returns {this}
     */
    uint( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.UInt
            }
        );
        return this;
    }

    /**
     * Long data type.
     * @param {string} n Name.
     * @returns {this}
     */
    long( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Long
            }
        );
        return this;
    }

    /**
     * Unsigned long data type.
     * @param {string} n Name.
     * @returns {this}
     */
    ulong( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.ULong
            }
        );
        return this;
    }

    /**
     * Float data type.
     * @param {string} n Name.
     * @returns {this}
     */
    float( n )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Float
            }
        );
        return this;
    }

    /**
     * Fixed length of characters data type.
     * @param {string} n Name.
     * @param {BigInt} s Size.
     * @returns {this}
     */
    fixed( n, s )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.FixedLengthCharacters,
                size: s
            }
        );
        return this;
    }
    
    /**
     * String data type.
     * @param {string} n Name.
     * @param {number} [si] Size identifier (optional).
     * @returns {this}
     */
    string( n, si )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.String,
                sizeIdentifier: si
            }
        );
        return this;
    }
    
    /**
     * Binary data type.
     * @param {string} n Name.
     * @param {BigInt} s Size.
     * @returns {this}
     */
    binary( n, s )
    {
        this.#format.push(
            {
                name: n,
                type: BINARY_FORMATTER_DATA_TYPES.Binary,
                size: s
            }
        );
        return this;
    }

    /**
     * Get the format array.
     * @returns {object[]} Format array.
     */
    array()
    {
        return this.#format;
    }
}