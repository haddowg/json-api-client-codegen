# An action answering with a URL returns an `ActionLocation` and never follows it

An action that answers with a URL returns the runtime's `ActionLocation` (`url`, media `type`,
nullable `expiresAt`) read from the `200` location document the client always gets by sending the
JSON:API `Accept`. The URL is handed back, never fetched, because it typically points at storage
rather than the API and the caller decides what to do with it.

## Considered options

A generated value class per API would keep the result out of the runtime, but every client would
carry an identical class and a resource type named `locations` would collide with it. Reading the
`303` instead needs a PSR-18 client configured not to follow redirects, which the runtime cannot
enforce, and loses the media type and expiry.
