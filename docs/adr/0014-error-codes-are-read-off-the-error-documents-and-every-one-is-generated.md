# Error codes are read off the error documents, and every one is generated

The catalogue is found by walking the shapes the error documents offer, as the TypeScript codegen
does, rather than by matching component names, so a document cataloguing one component per code
fails on the pointer it cannot read instead of silently generating no codes. Every code found gets
a class, core's included, because the runtime no longer ships them (runtime ADR 0021): reading the
installed runtime to skip its codes tied what a client contains to the runtime version beside the
codegen, and a shipped class could not carry the typed `meta` a document declares.
