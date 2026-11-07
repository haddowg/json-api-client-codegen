# Server documents are pinned packages and the generated output is reviewed as a CI diff

The music catalog and core's contract witness are read from `@haddowg/json-api-symfony-specs` and
`@haddowg/json-api-witness` at exact versions in a private, devDependency-only `package.json`, and
the generated output is no longer committed as golden files: CI generates it on a pull request's
base and head, posts the diff as one comment, and fails until the `generated-output` label
acknowledges it, as core does for its witness. Committed copies drifted from what the servers
publish and every refresh buried an emitter's diff under megabytes of regenerated documents and
output; the label keeps the review a golden file gave, while the suite still proves generation is
deterministic, the output passes PHPStan level 9, and the behaviour tests run it fresh.

## Consequences

- A pull request may pin a server pull request's `0.0.0-pr.<n>.<sha>` to test against it, with a
  warning; `check-pins` fails a prerelease on `main` and on a release pull request, since the
  prerelease is deleted when its pull request closes.
- There is no lockfile: each pin is exact, the packages have no dependencies, and a published
  GitHub Packages version cannot be replaced.
- An unintended output change is caught at review, on the pull request, rather than by a local
  `composer test`. Run `composer generate-output` before and after a change to see it locally.
