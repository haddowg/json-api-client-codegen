"""MkDocs build hooks for the published documentation site.

The source Markdown links into this repository with relative paths (``../tests/…``,
``../src/…``) so it reads correctly on GitHub. Those targets are not part of the site, so at build
time they are rewritten to GitHub ``blob`` URLs. The source files are left untouched.
"""

import re

_PACKAGE_BLOB = "https://github.com/haddowg/json-api-client-codegen/blob/main/"

# ](../…/<repo path outside docs/>)  ->  package GitHub blob URL
_REPO_LINK = re.compile(
    r"\]\((?:\.\./)+((?:tests|src|bin|composer|phpunit|LICENSE|CONTRIBUTING)[^)]*)\)"
)


def on_page_markdown(markdown, **kwargs):
    return _REPO_LINK.sub(rf"]({_PACKAGE_BLOB}\1)", markdown)
