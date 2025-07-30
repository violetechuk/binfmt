# Binary Formatter

This repository contains libraries that help with formatting binary responses from `fetch()` requests in JavaScript.

## Usage
The following example is JavaScript code to read a binary response from a `fetch()` call that has the following information:
- A 4-character header of "DATA".
- A null byte.
- A string of "Example string!"
- An unsigned integer of 61.

```JavaScript
import BinaryFormatter, { BinaryFormat } from "./binfmt.js";

const bytes = await fetch( "example.php" );
const format = new BinaryFormat()
    .fixed( "header", 4 )
    .null()
    .string( "exampleStr" )
    .uint( "exampleUint" );

const bf = new BinaryFormatter( await bytes.arrayBuffer(), true );
console.log( bf.toJson(format) );
```

This will output the following in the browser console:

```JSON
{
    header: "DATA",
    exampleStr: "Example string!",
    exampleUint: 61
}
```