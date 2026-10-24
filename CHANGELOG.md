# Changelog

## 0.1.0 (2026-10-24)


### Features

* build the descriptor model from a typed spec reader ([#2](https://github.com/haddowg/json-api-client-codegen/issues/2)) ([685dafd](https://github.com/haddowg/json-api-client-codegen/commit/685dafd1e6929acf273f065a79956bb6b089f82d))
* carry attribute nullability and per-endpoint relation statuses ([#3](https://github.com/haddowg/json-api-client-codegen/issues/3)) ([dfc8461](https://github.com/haddowg/json-api-client-codegen/commit/dfc8461d72202d238c73b155a18497e7df32fa6e))
* detect fixed-page pagination and the page object parameter ([#4](https://github.com/haddowg/json-api-client-codegen/issues/4)) ([1487c55](https://github.com/haddowg/json-api-client-codegen/commit/1487c55e1c209273ed6e9e746a7829ae3209f445))
* emit a class for each error code the runtime does not ship ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* emit the root client with one accessor per resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate a client, starting with its enums ([#9](https://github.com/haddowg/json-api-client-codegen/issues/9)) ([cb004ba](https://github.com/haddowg/json-api-client-codegen/commit/cb004ba0d9197094c166fbfa69bc50dd52133d75))
* generate against the runtime's additions ([#12](https://github.com/haddowg/json-api-client-codegen/issues/12)) ([2ccd55b](https://github.com/haddowg/json-api-client-codegen/commit/2ccd55bb74b75d0ef7f945e9e06bc39602aa668a))
* generate create and update inputs ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate the operation surface for every resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate the read model for every resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* generate the read side of each resource type ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* model the create and update attribute sets separately ([#6](https://github.com/haddowg/json-api-client-codegen/issues/6)) ([f3e9317](https://github.com/haddowg/json-api-client-codegen/commit/f3e9317dd6db44454ccd7d967dcee5c8d93df23d))
* read generator contract 2 ([#8](https://github.com/haddowg/json-api-client-codegen/issues/8)) ([1eb1877](https://github.com/haddowg/json-api-client-codegen/commit/1eb1877e7bd54e5632c6f397e70d5b130c0c6a41))
* read generator contract 4 and describe error codes ([#14](https://github.com/haddowg/json-api-client-codegen/issues/14)) ([b08c995](https://github.com/haddowg/json-api-client-codegen/commit/b08c995ba57e52bd11a5f81944f3fd8cf4f51679))
* read generator contract 5 and narrow types from its constraints ([#15](https://github.com/haddowg/json-api-client-codegen/issues/15)) ([59458b1](https://github.com/haddowg/json-api-client-codegen/commit/59458b178b19dc8dc9dc52771ae0ed77b2abb3f9))
* read generator contract 8 ([#18](https://github.com/haddowg/json-api-client-codegen/issues/18)) ([9a6f1f4](https://github.com/haddowg/json-api-client-codegen/commit/9a6f1f44114bdad9bf561703168b0b7060917242))
* read generator contract 9 ([#19](https://github.com/haddowg/json-api-client-codegen/issues/19)) ([b4624af](https://github.com/haddowg/json-api-client-codegen/commit/b4624af1117bfb1d8359d20f6dc09a35bdd19f64))
* read generator contracts 6 and 7 ([#16](https://github.com/haddowg/json-api-client-codegen/issues/16)) ([e264a77](https://github.com/haddowg/json-api-client-codegen/commit/e264a77dbbcc39e4654334e1e43bf6512c512461))
* read pagination from x-paginator and generate a strategy picker ([#13](https://github.com/haddowg/json-api-client-codegen/issues/13)) ([aed659a](https://github.com/haddowg/json-api-client-codegen/commit/aed659a8dab10002bfb2efa99add1729d1eb46d1))


### Bug Fixes

* carry a client-generated id in the create input alone ([a52556f](https://github.com/haddowg/json-api-client-codegen/commit/a52556fbb220c48931171822160a74d0fd2e5deb))
* escape document strings and check names in generated code ([#20](https://github.com/haddowg/json-api-client-codegen/issues/20)) ([165463a](https://github.com/haddowg/json-api-client-codegen/commit/165463acf0ca79b6eff7d2e7898f21ea8d7e7376))
* regenerate the music-catalog fixture from json-api 1.1.0 ([#7](https://github.com/haddowg/json-api-client-codegen/issues/7)) ([bfdcae5](https://github.com/haddowg/json-api-client-codegen/commit/bfdcae52656f2a76cf7bc914b2381df753ff6209))
* regenerate the music-catalog fixture from the current server ([#5](https://github.com/haddowg/json-api-client-codegen/issues/5)) ([023e295](https://github.com/haddowg/json-api-client-codegen/commit/023e295ec117810601449cc23947846d34dd6d61))


### Miscellaneous Chores

* scaffold package ([1729892](https://github.com/haddowg/json-api-client-codegen/commit/17298920be602025643e2e048b55e1a5eb420774))
