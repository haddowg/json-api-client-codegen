# Changelog

## 0.1.0 (2026-10-31)


### Features

* build the descriptor model from a typed spec reader ([#2](https://github.com/haddowg/json-api-client-codegen/issues/2)) ([685dafd](https://github.com/haddowg/json-api-client-codegen/commit/685dafd1e6929acf273f065a79956bb6b089f82d))
* carry attribute nullability and per-endpoint relation statuses ([#3](https://github.com/haddowg/json-api-client-codegen/issues/3)) ([dfc8461](https://github.com/haddowg/json-api-client-codegen/commit/dfc8461d72202d238c73b155a18497e7df32fa6e))
* detect fixed-page pagination and the page object parameter ([#4](https://github.com/haddowg/json-api-client-codegen/issues/4)) ([1487c55](https://github.com/haddowg/json-api-client-codegen/commit/1487c55e1c209273ed6e9e746a7829ae3209f445))
* emit a class for each error code the runtime does not ship ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* emit the root client with one accessor per resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* filter and sort a to-many's related read ([#40](https://github.com/haddowg/json-api-client-codegen/issues/40)) ([d795f60](https://github.com/haddowg/json-api-client-codegen/commit/d795f604d48e9866419cae8362e721a6c58b6dac))
* generate _handle() on every resource with an id-handle ([#38](https://github.com/haddowg/json-api-client-codegen/issues/38)) ([bba916b](https://github.com/haddowg/json-api-client-codegen/commit/bba916b01a98c3ac1df1b0996cd47f72a3f1f311))
* generate a client, starting with its enums ([#9](https://github.com/haddowg/json-api-client-codegen/issues/9)) ([cb004ba](https://github.com/haddowg/json-api-client-codegen/commit/cb004ba0d9197094c166fbfa69bc50dd52133d75))
* generate against the runtime's additions ([#12](https://github.com/haddowg/json-api-client-codegen/issues/12)) ([2ccd55b](https://github.com/haddowg/json-api-client-codegen/commit/2ccd55bb74b75d0ef7f945e9e06bc39602aa668a))
* generate atomic() when the server advertises atomic operations ([#37](https://github.com/haddowg/json-api-client-codegen/issues/37)) ([b62cf51](https://github.com/haddowg/json-api-client-codegen/commit/b62cf5101bf4ac24fcc2fa86eec290ac84689b4f))
* generate create and update inputs ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate Relationship Queries for reads that declare relatedQuery ([#39](https://github.com/haddowg/json-api-client-codegen/issues/39)) ([b842b45](https://github.com/haddowg/json-api-client-codegen/commit/b842b4537d24586aca2e5460a7d21a1a08247290))
* generate the operation surface for every resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate the read model for every resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate the read side of each resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* model the create and update attribute sets separately ([#6](https://github.com/haddowg/json-api-client-codegen/issues/6)) ([f3e9317](https://github.com/haddowg/json-api-client-codegen/commit/f3e9317dd6db44454ccd7d967dcee5c8d93df23d))
* narrow a form body to the fields its schema lists ([#24](https://github.com/haddowg/json-api-client-codegen/issues/24)) ([fac17b8](https://github.com/haddowg/json-api-client-codegen/commit/fac17b8c2691a21d7120486a2f4bf97dc6790d42))
* read action safety and per-method action ids from contract 15 ([#31](https://github.com/haddowg/json-api-client-codegen/issues/31)) ([35a76f9](https://github.com/haddowg/json-api-client-codegen/commit/35a76f98507a7b9bc619921840666b6dee4b8a72))
* read discriminated resource unions and a typed included ([#42](https://github.com/haddowg/json-api-client-codegen/issues/42)) ([ee55bc2](https://github.com/haddowg/json-api-client-codegen/commit/ee55bc22cae097be4145297bb04b251ce8050dce))
* read generator contract 10 ([#22](https://github.com/haddowg/json-api-client-codegen/issues/22)) ([612cbf3](https://github.com/haddowg/json-api-client-codegen/commit/612cbf326fb46ab067607436cfd42a413cb92e7b))
* read generator contract 11 ([#23](https://github.com/haddowg/json-api-client-codegen/issues/23)) ([810604d](https://github.com/haddowg/json-api-client-codegen/commit/810604dcad818cf7646e911c7dd585eb9e15e4a5))
* read generator contract 2 ([#8](https://github.com/haddowg/json-api-client-codegen/issues/8)) ([1eb1877](https://github.com/haddowg/json-api-client-codegen/commit/1eb1877e7bd54e5632c6f397e70d5b130c0c6a41))
* read generator contract 4 and describe error codes ([#14](https://github.com/haddowg/json-api-client-codegen/issues/14)) ([b08c995](https://github.com/haddowg/json-api-client-codegen/commit/b08c995ba57e52bd11a5f81944f3fd8cf4f51679))
* read generator contract 5 and narrow types from its constraints ([#15](https://github.com/haddowg/json-api-client-codegen/issues/15)) ([59458b1](https://github.com/haddowg/json-api-client-codegen/commit/59458b178b19dc8dc9dc52771ae0ed77b2abb3f9))
* read generator contract 8 ([#18](https://github.com/haddowg/json-api-client-codegen/issues/18)) ([9a6f1f4](https://github.com/haddowg/json-api-client-codegen/commit/9a6f1f44114bdad9bf561703168b0b7060917242))
* read generator contract 9 ([#19](https://github.com/haddowg/json-api-client-codegen/issues/19)) ([b4624af](https://github.com/haddowg/json-api-client-codegen/commit/b4624af1117bfb1d8359d20f6dc09a35bdd19f64))
* read generator contracts 6 and 7 ([#16](https://github.com/haddowg/json-api-client-codegen/issues/16)) ([e264a77](https://github.com/haddowg/json-api-client-codegen/commit/e264a77dbbcc39e4654334e1e43bf6512c512461))
* read operation examples from generator contract 12 ([#28](https://github.com/haddowg/json-api-client-codegen/issues/28)) ([dd2008e](https://github.com/haddowg/json-api-client-codegen/commit/dd2008e1a78a1d5e4534eb22f0158303296d41af))
* read operation scopes and single-type polymorphic unions from contract 16 ([#32](https://github.com/haddowg/json-api-client-codegen/issues/32)) ([5f5886f](https://github.com/haddowg/json-api-client-codegen/commit/5f5886fae13425ceeadbd2083fe68a3519f7e8e9))
* read pagination from x-paginator and generate a strategy picker ([#13](https://github.com/haddowg/json-api-client-codegen/issues/13)) ([aed659a](https://github.com/haddowg/json-api-client-codegen/commit/aed659a8dab10002bfb2efa99add1729d1eb46d1))
* read the shared link schemas of contract 18 ([#35](https://github.com/haddowg/json-api-client-codegen/issues/35)) ([f2e6233](https://github.com/haddowg/json-api-client-codegen/commit/f2e62332f7d8657336a7449a9e6e2d85567eaa43))
* return the URL an action answers with from contract 17 ([#34](https://github.com/haddowg/json-api-client-codegen/issues/34)) ([b62cb82](https://github.com/haddowg/json-api-client-codegen/commit/b62cb82fe91ecf9bc5decb33b475c36b68106703))
* type the links a resource declares from generator contract 14 ([#30](https://github.com/haddowg/json-api-client-codegen/issues/30)) ([16e0ce7](https://github.com/haddowg/json-api-client-codegen/commit/16e0ce74d65fb05949c771fc85c77bf7e04f3187))


### Bug Fixes

* carry a client-generated id in the create input alone ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* escape document strings and check names in generated code ([#20](https://github.com/haddowg/json-api-client-codegen/issues/20)) ([165463a](https://github.com/haddowg/json-api-client-codegen/commit/165463acf0ca79b6eff7d2e7898f21ea8d7e7376))
* generate a client from every shape core's contract witness projects ([#21](https://github.com/haddowg/json-api-client-codegen/issues/21)) ([2c63346](https://github.com/haddowg/json-api-client-codegen/commit/2c633462a5c6670221a32c9ee1868b379eb99dfc))
* page a polymorphic to-many read only when its endpoint does ([#33](https://github.com/haddowg/json-api-client-codegen/issues/33)) ([255e909](https://github.com/haddowg/json-api-client-codegen/commit/255e9098655df2528073e8822202fdb36514f0b1))
* read generator contract 13 ([#29](https://github.com/haddowg/json-api-client-codegen/issues/29)) ([c75e595](https://github.com/haddowg/json-api-client-codegen/commit/c75e595b8fa464ea15b0c9d197a3bce46f1029f1))
* regenerate the music-catalog fixture from json-api 1.1.0 ([#7](https://github.com/haddowg/json-api-client-codegen/issues/7)) ([bfdcae5](https://github.com/haddowg/json-api-client-codegen/commit/bfdcae52656f2a76cf7bc914b2381df753ff6209))
* regenerate the music-catalog fixture from the current server ([#5](https://github.com/haddowg/json-api-client-codegen/issues/5)) ([023e295](https://github.com/haddowg/json-api-client-codegen/commit/023e295ec117810601449cc23947846d34dd6d61))
* take a stream or a string for a text request body ([#26](https://github.com/haddowg/json-api-client-codegen/issues/26)) ([32a7395](https://github.com/haddowg/json-api-client-codegen/commit/32a73953bec470476f7a573d12334b41fe4ae55e))


### Miscellaneous Chores

* scaffold package ([1729892](https://github.com/haddowg/json-api-client-codegen/commit/17298920be602025643e2e048b55e1a5eb420774))
