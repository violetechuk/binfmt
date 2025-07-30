# Binary Formatter

This repository contains libraries that help with formatting binary responses from `fetch()` requests in JavaScript.

## Usage

> [!WARNING]  
> This library has little to no protection if the binary data given to it does not match the format you give it via `toJson()`.

The following example is JavaScript code to read a binary response from a `fetch()` call that has the following information:
- A 4-character header of `DATA`.
- A null byte.
- A string of `Example string!`.
- An unsigned integer of `61`.

```JavaScript
import BinaryFormatter, { BinaryFormat } from "./binfmt.js";

const bytes = await fetch( "example.php" );
const format = new BinaryFormat()
    .fixed( "header", 4 )
    .null()
    .string( "exampleStr" )
    .uint( "exampleUint" );

const bf = new BinaryFormatter( await bytes.arrayBuffer(), true );
const result = bf.toJson( format );
```

This will set `result` to the following JSON:

```JSON
{
    "header": "DATA",
    "exampleStr": "Example string!",
    "exampleUint": 61
}
```

You can also perform multiple `toJson()` calls, should you need to add conditionals before getting the next set of data from the same stream. For example, if `exampleUint` is `61`, it may be expected an unsigned short should come next (here with an example value of `2`):

```JavaScript
if( result.exampleUint == 61 )
{
    const format2 = new BinaryFormat().ushort( "additionalUshort" );
    const result2 = bf.toJson( format2 );

    console.log( result2 );
}
```

`result2` will now be set to:

```JSON
{
    "additionalUshort": 2
}
```