---
status: accepted
---

# An action answering with a URL returns an `ActionLocation` and never follows it

From contract 17 an action that declares `SeeOther` with a media type answers a request preferring
JSON:API with a `200` document whose top-level `links.location` holds the URL, its media type and
its expiry, and answers everything else with the `303` (core ADR 0185). The generated client always
sends the JSON:API `Accept`, so it only ever gets the document. The descriptor reads the declared
media type from the `type` const the projector narrows `LocationDocument` with into the action's
`locationType`, so neither this codegen nor a tool reading the descriptor has to recognise the
schema again. The method returns the runtime's `ActionLocation`, a readonly value of `url`, `type`
and a nullable `expiresAt` as a `\DateTimeImmutable`, read by `Fetcher::location()`. The URL is
handed back, never fetched: a presigned download link points at storage, not the API, and the
caller decides what to do with it.

When the action also declares a JSON:API `200` body, the response is a `oneOf` of that document and
the location document. The method returns the union of the two results, and
`Fetcher::readOrLocation()` tells them apart by shape: a location document has a `location` link and
no `data`. A meta-only document and a collection get the same union.

## Considered options

**Generating a value class per API** would keep the result out of the runtime, but every client
would carry an identical class, and a resource type named `locations` would collide with it.

**Reading the `303` instead**, with redirects turned off, needs the PSR-18 client to be configured
not to follow them, which the runtime cannot enforce, and loses the media type and the expiry.

## Consequences

A `LocationDocument` that does not narrow `links.location.type` to a `const` is an error by pointer,
as any unreadable structure is (ADR 0001). A bare `SeeOther` documents only its `303`, so its method
is unchanged.
