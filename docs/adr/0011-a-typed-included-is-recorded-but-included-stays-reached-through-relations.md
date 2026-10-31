# A typed included is recorded but included stays reached through relations

A document can type each operation's `included` as a `oneOf` over the resources its include paths reach, and the descriptor records those types on the operation's projection. The generated client emits nothing from them, because it never hands out `included` as a list: an included resource is reached through its relation's typed accessor, which already knows the related type and hydrates a polymorphic member by its `type`. A typed `included` array beside those accessors would be a second way to read the same resources with weaker types.

## Consequences

A `oneOf` linkage is read as a polymorphic set like an `anyOf` without a `null` branch, and a discriminator mapping that disagrees with its members' `type` is an error. An older document without either generates exactly as before.
